<?php

namespace App\Listeners;

use App\Events\ListingPublished;
use App\Services\SocialMediaPublisherService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Pushes a newly-published listing onto the configured social media
 * platforms. Runs on the queue so a slow Meta endpoint can't delay
 * the user's "publish" click.
 */
class PublishListingToSocialMedia implements ShouldQueue
{
    use InteractsWithQueue;

    /** Use the default queue connection (database in this project). */
    public $queue = 'default';

    /** Reasonable retry policy — Meta is occasionally flaky. */
    public $tries   = 3;
    public $backoff = 30; // seconds between retries

    public function __construct(protected SocialMediaPublisherService $publisher) {}

    public function handle(ListingPublished $event): void
    {
        if (! $this->publisher->isAutoPostEnabled()) {
            return;
        }

        $this->publisher->publishListing($event->listing);
    }
}
