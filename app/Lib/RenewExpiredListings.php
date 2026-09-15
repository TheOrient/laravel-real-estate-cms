<?php

namespace App\Lib;

use App\Models\Listing;
use App\Services\ListingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RenewExpiredListings
{
    protected $listingService;

    public function __construct(ListingService $listingService)
    {
        $this->listingService = $listingService;
    }

    /**
     * Execute the renewal process
     *
     * @return array Result statistics
     */
    public function handle()
    {
        // Get all active and approved listings that have expired
        $expiredListings = Listing::where('is_active', true)
            ->where('is_approved', true)
            ->where('expires_at', '<=', Carbon::now())
            ->with('user', 'category')
            ->get();

        $renewedCount = 0;
        $deactivatedCount = 0;
        $errors = 0;
        $results = [];

        foreach ($expiredListings as $listing) {
            try {
                $user = $listing->user;

                // Try to renew the listing using ListingService
                $renewed = $this->listingService->renewListing($listing);

                if ($renewed) {
                    // User has quota - listing was renewed
                    $renewedCount++;
                    $results[] = "✓ Renewed listing #{$listing->id}: {$listing->title} (User: {$user->full_name})";
                } else {
                    // User has no quota - deactivate the listing
                    $this->listingService->deactivateExpiredListing($listing);
                    $deactivatedCount++;
                    $results[] = "⊗ Deactivated listing #{$listing->id}: {$listing->title} (User: {$user->full_name} - No quota)";
                }
            } catch (\Exception $e) {
                $errors++;
                $results[] = "✗ Error processing listing #{$listing->id}: " . $e->getMessage();
                Log::error('Listing renewal error', [
                    'listing_id' => $listing->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return [
            'processed' => $expiredListings->count(),
            'renewed' => $renewedCount,
            'deactivated' => $deactivatedCount,
            'errors' => $errors,
            'messages' => $results
        ];
    }
}
