<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Services\SocialMediaPublisherService;
use Illuminate\Http\RedirectResponse;

/**
 * Manual "share now" trigger from the admin listing page.
 *
 * The same SocialMediaPublisherService backs the automatic post-on-
 * publish flow; this just lets an operator re-fire it (e.g. after
 * fixing a missing image or expired token).
 */
class SocialShareController extends Controller
{
    public function __construct(protected SocialMediaPublisherService $publisher) {}

    public function share(Listing $listing): RedirectResponse
    {
        $result = $this->publisher->publishListing($listing);

        if (isset($result['skipped'])) {
            return back()->with('warning', __('admin/social.autopost_disabled'));
        }

        $errors = [];
        $okPlatforms = [];
        foreach ($result as $platform => $payload) {
            if (is_array($payload) && isset($payload['error'])) {
                $errors[] = "{$platform}: {$payload['error']}";
            } else {
                $okPlatforms[] = $platform;
            }
        }

        if (!empty($errors) && empty($okPlatforms)) {
            return back()->with('error', __('admin/social.share_failed') . ' ' . implode(' | ', $errors));
        }

        $message = __('admin/social.shared_to', ['platforms' => implode(', ', $okPlatforms)]);
        if (!empty($errors)) {
            $message .= ' — ' . __('admin/social.partial') . ': ' . implode(' | ', $errors);
        }

        return back()->with('success', $message);
    }
}
