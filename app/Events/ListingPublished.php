<?php

namespace App\Events;

use App\Models\Listing;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired the first time a listing transitions to a state visible to
 * the public. Listeners include the social media publisher and any
 * future analytics or notification pipelines.
 */
class ListingPublished
{
    use Dispatchable, SerializesModels;

    public function __construct(public Listing $listing) {}
}
