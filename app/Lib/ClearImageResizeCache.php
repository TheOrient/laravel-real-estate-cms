<?php

namespace App\Lib;

class ClearImageResizeCache
{
    /**
     * Execute the cache cleanup
     *
     * @return array Result statistics
     */
    public function handle()
    {
        // Get all files recursively in cache directory
        $files = $this->globRecursive(storage_path('app/cache/.cache/uploads'), '*');

        $deletedFiles = 0;
        $deletedDirs = 0;

        // First remove files older than 30 days
        foreach ($files as $file) {
            // If it's a file and older than 30 days
            if (is_file($file) && filemtime($file) < time() - 30 * 24 * 60 * 60) {
                if (@unlink($file)) {
                    $deletedFiles++;
                }
            }
        }

        // Then remove empty directories (starting from deepest level)
        $dirs = array_filter($this->globRecursive(storage_path('app/cache/.cache/uploads'), '*'), 'is_dir');
        // Sort by deepest path first (most slashes)
        usort($dirs, function ($a, $b) {
            return substr_count($b, DIRECTORY_SEPARATOR) - substr_count($a, DIRECTORY_SEPARATOR);
        });

        foreach ($dirs as $dir) {
            // If it's a directory and empty
            if (count(glob($dir . '/*')) === 0) {
                if (@rmdir($dir)) {
                    $deletedDirs++;
                }
            }
        }

        return [
            'deleted_files' => $deletedFiles,
            'deleted_dirs' => $deletedDirs
        ];
    }

    /**
     * Recursive glob function to get all files in a directory and subdirectories
     *
     * @param string $base Directory to search
     * @param string $pattern Glob pattern to match files
     * @param int $flags Glob flags
     * @return array Array of files matching the pattern
     */
    private function globRecursive($base, $pattern, $flags = 0)
    {
        if (substr($base, -1) !== DIRECTORY_SEPARATOR) {
            $base .= DIRECTORY_SEPARATOR;
        }

        $files = glob($base . $pattern, $flags);
        if (!is_array($files)) {
            $files = [];
        }

        $dirs = glob($base . '*', GLOB_ONLYDIR | GLOB_NOSORT);
        if (!is_array($dirs)) {
            return $files;
        }

        foreach ($dirs as $dir) {
            $dirFiles = $this->globRecursive($dir, $pattern, $flags);
            $files = array_merge($files, $dirFiles);
        }

        return $files;
    }
}
