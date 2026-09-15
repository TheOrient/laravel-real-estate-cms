<?php

namespace App\Lib;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class ClearSimilarListingsCache
{
    /**
     * Execute the cache cleanup
     *
     * @param int|null $categoryId Category ID to clear cache for
     * @param bool $all Whether to clear all cache
     * @return array Result statistics
     */
    public function handle($categoryId = null, $all = false)
    {
        if ($all) {
            return $this->clearAllSimilarListingsCache();
        } elseif ($categoryId) {
            return $this->clearCategorySimilarListingsCache($categoryId);
        } else {
            return [
                'success' => false,
                'message' => 'Please specify either all=true or categoryId'
            ];
        }
    }

    /**
     * Clear all similar listings cache
     */
    private function clearAllSimilarListingsCache()
    {
        try {
            // Clear cache tags if supported
            Cache::tags(['similar_listings'])->flush();
            return [
                'success' => true,
                'message' => 'Cache cleared using tags.'
            ];
        } catch (\Exception $e) {
            // Fallback for file-based cache
            $categories = Category::pluck('id');
            $clearedCount = 0;

            foreach ($categories as $categoryId) {
                $cacheKeysKey = "similar_listings_keys_category_{$categoryId}";
                $cacheKeys = Cache::get($cacheKeysKey, []);

                foreach ($cacheKeys as $key) {
                    Cache::forget($key);
                    $clearedCount++;
                }

                Cache::forget($cacheKeysKey);
            }

            return [
                'success' => true,
                'message' => "Cleared {$clearedCount} individual cache entries."
            ];
        }
    }

    /**
     * Clear similar listings cache for specific category
     */
    private function clearCategorySimilarListingsCache($categoryId)
    {
        $category = Category::find($categoryId);

        if (!$category) {
            return [
                'success' => false,
                'message' => "Category with ID {$categoryId} not found."
            ];
        }

        try {
            // Clear cache tags if supported
            Cache::tags(['similar_listings', "category_{$categoryId}"])->flush();
            return [
                'success' => true,
                'message' => 'Cache cleared using tags.'
            ];
        } catch (\Exception $e) {
            // Fallback for file-based cache
            $cacheKeysKey = "similar_listings_keys_category_{$categoryId}";
            $cacheKeys = Cache::get($cacheKeysKey, []);
            $clearedCount = 0;

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
                $clearedCount++;
            }

            Cache::forget($cacheKeysKey);
            return [
                'success' => true,
                'message' => "Cleared {$clearedCount} individual cache entries."
            ];
        }
    }
}
