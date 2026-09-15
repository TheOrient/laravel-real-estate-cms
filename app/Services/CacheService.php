<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Clear all application caches
     *
     * @return array Returns status of each cache clearing operation
     */
    public function clearAll(): array
    {
        $results = [
            'cache' => false,
            'config' => false,
            'routes' => false,
            'views' => false,
        ];

        try {
            // Clear application cache
            Cache::flush();
            $results['cache'] = true;
        } catch (\Exception $e) {
            \Log::error('Failed to clear application cache: ' . $e->getMessage());
        }

        try {
            // Clear config cache
            $results['config'] = $this->clearConfigCache();
        } catch (\Exception $e) {
            \Log::error('Failed to clear config cache: ' . $e->getMessage());
        }

        try {
            // Clear route cache
            $results['routes'] = $this->clearRouteCache();
        } catch (\Exception $e) {
            \Log::error('Failed to clear route cache: ' . $e->getMessage());
        }

        try {
            // Clear compiled views
            $results['views'] = $this->clearViewCache();
        } catch (\Exception $e) {
            \Log::error('Failed to clear view cache: ' . $e->getMessage());
        }

        return $results;
    }

    /**
     * Clear application cache only
     */
    public function clearCache(): bool
    {
        try {
            Cache::flush();
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to clear cache: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear config cache file
     */
    public function clearConfigCache(): bool
    {
        $configCache = base_path('bootstrap/cache/config.php');

        if (file_exists($configCache)) {
            return @unlink($configCache);
        }

        return true; // Already cleared
    }

    /**
     * Clear route cache file
     */
    public function clearRouteCache(): bool
    {
        $routeCache = base_path('bootstrap/cache/routes-v7.php');

        if (file_exists($routeCache)) {
            return @unlink($routeCache);
        }

        return true; // Already cleared
    }

    /**
     * Clear compiled view files
     */
    public function clearViewCache(): bool
    {
        $viewPath = storage_path('framework/views');

        if (!is_dir($viewPath)) {
            return true; // Directory doesn't exist, nothing to clear
        }

        $files = glob($viewPath . '/*');
        $success = true;

        foreach ($files as $file) {
            if (is_file($file)) {
                if (!@unlink($file)) {
                    $success = false;
                }
            }
        }

        return $success;
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        return [
            'config_cached' => file_exists(base_path('bootstrap/cache/config.php')),
            'routes_cached' => file_exists(base_path('bootstrap/cache/routes-v7.php')),
            'views_count' => count(glob(storage_path('framework/views/*'))),
        ];
    }
}
