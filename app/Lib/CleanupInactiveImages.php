<?php

namespace App\Lib;

use App\Models\ListingImage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CleanupInactiveImages
{
    /**
     * Execute the cleanup process
     *
     * @param int $days Number of days after which inactive images will be deleted
     * @return array Result statistics
     */
    public function handle($days = 1)
    {
        $cutoffDate = Carbon::now()->subDays($days);

        // Find inactive images older than specified days
        $inactiveImages = ListingImage::where('is_active', 0)
            ->where('created_at', '<', $cutoffDate)
            ->get();

        if ($inactiveImages->isEmpty()) {
            return [
                'deleted_count' => 0,
                'failed_count' => 0,
                'total_found' => 0
            ];
        }

        $deletedCount = 0;
        $failedCount = 0;

        foreach ($inactiveImages as $image) {
            try {
                // Delete physical file
                $imagePath = public_path('uploads/listings/' . $image->image);
                if (file_exists($imagePath)) {
                    @unlink($imagePath);
                }

                // Delete database record
                $image->delete();
                $deletedCount++;
            } catch (\Exception $e) {
                $failedCount++;
                Log::error('Failed to delete inactive image', [
                    'image_id' => $image->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return [
            'deleted_count' => $deletedCount,
            'failed_count' => $failedCount,
            'total_found' => $inactiveImages->count()
        ];
    }
}
