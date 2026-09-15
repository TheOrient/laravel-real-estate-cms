<?php

namespace App\Services;

use App\Models\Listing;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SocialMediaPublisherService
 *
 * Posts a new listing to Facebook (Page feed) and Instagram Business.
 *
 * Configuration lives in the settings table — that way the operator
 * can rotate tokens or flip the feature on/off without redeploying:
 *   - meta_page_id              Facebook Page ID
 *   - meta_page_token           Page Access Token (long-lived)
 *   - meta_ig_user_id           Instagram Business User ID
 *   - social_autopost_enabled   '1' / '0' kill switch
 *
 * Either platform can be skipped by leaving its fields blank. All
 * errors are logged but never thrown — calling code (the publish
 * event listener) must not break listing creation if FB is down.
 */
class SocialMediaPublisherService
{
    /**
     * Publish a listing to all configured platforms. Returns an array
     * keyed by platform with either a post URL string or an error.
     */
    public function publishListing(Listing $listing): array
    {
        if (! $this->isAutoPostEnabled()) {
            return ['skipped' => 'social_autopost_enabled is off'];
        }

        $result = [];

        // ---- Facebook Page ----
        $pageId    = $this->setting('meta_page_id');
        $pageToken = $this->setting('meta_page_token');
        if ($pageId && $pageToken) {
            try {
                $result['facebook'] = $this->postToFacebook($listing, $pageId, $pageToken);
            } catch (\Throwable $e) {
                Log::error('Facebook auto-post failed', ['listing_id' => $listing->id, 'err' => $e->getMessage()]);
                $result['facebook'] = ['error' => $e->getMessage()];
            }
        }

        // ---- Instagram Business ----
        $igUserId = $this->setting('meta_ig_user_id');
        if ($igUserId && $pageToken) {
            try {
                $result['instagram'] = $this->postToInstagram($listing, $igUserId, $pageToken);
            } catch (\Throwable $e) {
                Log::error('Instagram auto-post failed', ['listing_id' => $listing->id, 'err' => $e->getMessage()]);
                $result['instagram'] = ['error' => $e->getMessage()];
            }
        }

        return $result;
    }

    public function isAutoPostEnabled(): bool
    {
        return (string) get_setting('social_autopost_enabled', '0') === '1';
    }

    /* ------------------------------- Composition ------------------------------- */

    /**
     * Format the listing into a single social-friendly message. Kept
     * intentionally short — most platforms truncate around 2200 chars
     * and the price + location + link is what readers care about.
     */
    protected function composeMessage(Listing $listing): string
    {
        $lines = [];

        $title = trim((string) $listing->title);
        if ($title !== '') $lines[] = $title;

        if ($listing->price) {
            $lines[] = number_format($listing->price, 0, ',', '.') . ' ₺';
        }

        $location = [];
        if ($listing->city)     $location[] = $listing->city->name;
        if ($listing->district) $location[] = $listing->district->name;
        if (!empty($location)) $lines[] = '📍 ' . implode(', ', $location);

        $desc = trim(strip_tags((string) ($listing->description ?? '')));
        if ($desc !== '') $lines[] = mb_substr($desc, 0, 280);

        $lines[] = route('listings.show', $listing->slug);

        return implode("\n\n", array_filter($lines, fn ($l) => $l !== ''));
    }

    /**
     * First image URL for Instagram (which requires a publicly fetchable image).
     */
    protected function firstImageUrl(Listing $listing): ?string
    {
        $img = $listing->images?->first()?->image ?? $listing->image;
        if (! $img) return null;
        // Render at large size via Glide so Meta gets a proper file
        // (not the original blob it might 404 on).
        return route('image.resize', ['size' => 'large', 'fit' => 'cover', 'path' => $img]);
    }

    /* ---------------------------------- HTTP --------------------------------- */

    protected function postToFacebook(Listing $listing, string $pageId, string $pageToken): array
    {
        $v = config('services.meta.graph_version', 'v19.0');
        $message = $this->composeMessage($listing);
        $image = $this->firstImageUrl($listing);

        // Prefer /photos with caption when an image is available —
        // produces a much richer Page post than a text-only /feed entry.
        if ($image) {
            $resp = Http::asForm()->timeout(30)->post("https://graph.facebook.com/{$v}/{$pageId}/photos", [
                'url'          => $image,
                'caption'      => $message,
                'access_token' => $pageToken,
            ]);
        } else {
            $resp = Http::asForm()->timeout(30)->post("https://graph.facebook.com/{$v}/{$pageId}/feed", [
                'message'      => $message,
                'link'         => route('listings.show', $listing->slug),
                'access_token' => $pageToken,
            ]);
        }

        if (! $resp->ok()) {
            throw new \RuntimeException('Facebook ' . $resp->status() . ': ' . $resp->body());
        }

        $postId = $resp->json('post_id') ?? $resp->json('id');
        return [
            'post_id' => $postId,
            'url'     => $postId ? "https://www.facebook.com/{$postId}" : null,
        ];
    }

    /**
     * Instagram Business posting is a 2-step container flow:
     *   1) Create a media container with the image URL + caption.
     *   2) Publish the container to the feed.
     */
    protected function postToInstagram(Listing $listing, string $igUserId, string $pageToken): array
    {
        $image = $this->firstImageUrl($listing);
        if (! $image) {
            throw new \RuntimeException('Instagram requires a public image URL.');
        }

        $v = config('services.meta.graph_version', 'v19.0');
        $caption = $this->composeMessage($listing);

        $container = Http::asForm()->timeout(30)->post("https://graph.facebook.com/{$v}/{$igUserId}/media", [
            'image_url'    => $image,
            'caption'      => $caption,
            'access_token' => $pageToken,
        ]);
        if (! $container->ok()) {
            throw new \RuntimeException('IG container ' . $container->status() . ': ' . $container->body());
        }
        $containerId = $container->json('id');

        $publish = Http::asForm()->timeout(30)->post("https://graph.facebook.com/{$v}/{$igUserId}/media_publish", [
            'creation_id'  => $containerId,
            'access_token' => $pageToken,
        ]);
        if (! $publish->ok()) {
            throw new \RuntimeException('IG publish ' . $publish->status() . ': ' . $publish->body());
        }

        return ['media_id' => $publish->json('id')];
    }

    protected function setting(string $key): ?string
    {
        $value = trim((string) get_setting($key, ''));
        if ($value !== '') return $value;

        // Fall back to .env via config/services.meta.* for the common keys.
        return match ($key) {
            'meta_page_id'    => config('services.meta.page_id'),
            'meta_page_token' => config('services.meta.page_token'),
            'meta_ig_user_id' => config('services.meta.ig_user_id'),
            default           => null,
        };
    }
}
