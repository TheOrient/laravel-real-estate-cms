<?php

namespace App\Services;

/**
 * VideoEmbedService
 *
 * Recognises common video URLs (YouTube, Vimeo) and converts them
 * into safe iframe embeds. Falls back to a plain HTML5 <video> tag
 * for self-hosted files. Centralised so the listing detail page,
 * blog detail page and admin preview can all render the same way.
 */
class VideoEmbedService
{
    public const PROVIDER_YOUTUBE = 'youtube';
    public const PROVIDER_VIMEO   = 'vimeo';
    public const PROVIDER_FILE    = 'file';
    public const PROVIDER_UNKNOWN = 'unknown';

    /**
     * Inspect a URL and return [provider, videoId]. Returns
     * [PROVIDER_UNKNOWN, null] when nothing recognisable matches.
     */
    public static function detect(?string $url): array
    {
        if (! $url) return [self::PROVIDER_UNKNOWN, null];

        // youtu.be/<id>
        if (preg_match('~youtu\.be/([A-Za-z0-9_-]{6,})~', $url, $m)) {
            return [self::PROVIDER_YOUTUBE, $m[1]];
        }
        // youtube.com/watch?v=<id>
        if (preg_match('~youtube\.com/watch\?(?:.*&)?v=([A-Za-z0-9_-]{6,})~', $url, $m)) {
            return [self::PROVIDER_YOUTUBE, $m[1]];
        }
        // youtube.com/embed/<id> / shorts/<id>
        if (preg_match('~youtube\.com/(?:embed|shorts)/([A-Za-z0-9_-]{6,})~', $url, $m)) {
            return [self::PROVIDER_YOUTUBE, $m[1]];
        }
        // vimeo.com/<id>
        if (preg_match('~vimeo\.com/(?:video/)?([0-9]{6,})~', $url, $m)) {
            return [self::PROVIDER_VIMEO, $m[1]];
        }

        return [self::PROVIDER_UNKNOWN, null];
    }

    public static function embedUrl(?string $url): ?string
    {
        [$provider, $id] = self::detect($url);

        return match ($provider) {
            self::PROVIDER_YOUTUBE => "https://www.youtube.com/embed/{$id}",
            self::PROVIDER_VIMEO   => "https://player.vimeo.com/video/{$id}",
            default                => null,
        };
    }

    /**
     * Light-weight URL validator used by form requests. Either accepts
     * a recognised host or rejects the string. Self-hosted files are
     * validated separately as uploads.
     */
    public static function isValidExternalUrl(?string $url): bool
    {
        if (! $url) return false;
        if (! filter_var($url, FILTER_VALIDATE_URL)) return false;
        [$provider] = self::detect($url);
        return $provider !== self::PROVIDER_UNKNOWN;
    }

    /**
     * Allowed mime types for self-hosted uploads. Kept conservative —
     * extra formats can be enabled per-install if needed.
     */
    public static function allowedFileMimes(): array
    {
        return ['video/mp4', 'video/webm', 'video/ogg'];
    }

    public static function maxFileBytes(): int
    {
        // 50 MB default. Overridable via settings table if desired.
        $mb = (int) get_setting('video_max_mb', 50);
        if ($mb < 1) $mb = 50;
        return $mb * 1024 * 1024;
    }
}
