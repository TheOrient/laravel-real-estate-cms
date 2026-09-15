<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Exception;

class FileUploadService implements ServiceInterface
{
    /**
     * File type configurations
     */
    const FILE_TYPES = [
        'image' => [
            'extensions' => ['jpeg', 'jpg', 'png', 'gif', 'webp'],
            'mime_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            'max_size' => 2048, // 2MB in KB
            'directory' => 'images'
        ],
        'document' => [
            'extensions' => ['pdf', 'doc', 'docx', 'txt'],
            'mime_types' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'],
            'max_size' => 5120, // 5MB in KB
            'directory' => 'documents'
        ],
        'video' => [
            'extensions' => ['mp4', 'avi', 'mov', 'wmv'],
            'mime_types' => ['video/mp4', 'video/avi', 'video/quicktime', 'video/x-ms-wmv'],
            'max_size' => 51200, // 50MB in KB
            'directory' => 'videos'
        ]
    ];

    /**
     * Upload a single file
     *
     * @param UploadedFile $file
     * @param string $type
     * @param string $subfolder
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function uploadFile(UploadedFile $file, string $type = 'image', string $subfolder = '', array $options = []): array
    {
        // Validate file type
        if (!isset(self::FILE_TYPES[$type])) {
            throw new Exception("Unsupported file type: {$type}");
        }

        $config = self::FILE_TYPES[$type];

        // Validate file
        $this->validateFile($file, $config);

        // Generate unique filename
        $filename = $this->generateFileName($file, $options);

        // Determine upload path
        $uploadPath = $this->getUploadPath($type, $subfolder);

        // Ensure directory exists
        $this->ensureDirectoryExists($uploadPath);

        // Get file info before moving (important for temp files)
        $originalName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();
        $extension = $file->getClientOriginalExtension();

        // Move file to destination
        $destinationPath = $uploadPath . '/' . $filename;

        try {
            $file->move(public_path($uploadPath), $filename);

            return [
                'success' => true,
                'filename' => $filename,
                'original_name' => $originalName,
                'path' => $uploadPath . '/' . $filename,
                'url' => asset($uploadPath . '/' . $filename),
                'size' => $fileSize,
                'mime_type' => $mimeType,
                'extension' => $extension
            ];
        } catch (Exception $e) {
            throw new Exception("Failed to upload file: " . $e->getMessage());
        }
    }

    /**
     * Upload multiple files
     *
     * @param array $files
     * @param string $type
     * @param string $subfolder
     * @param array $options
     * @return array
     */
    public function uploadMultipleFiles(array $files, string $type = 'image', string $subfolder = '', array $options = []): array
    {
        $results = [];
        $errors = [];

        foreach ($files as $index => $file) {
            try {
                $result = $this->uploadFile($file, $type, $subfolder, $options);
                $results[] = $result;
            } catch (Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'filename' => $file->getClientOriginalName(),
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'success' => count($results) > 0,
            'uploaded' => $results,
            'errors' => $errors,
            'total_uploaded' => count($results),
            'total_errors' => count($errors)
        ];
    }

    /**
     * Delete a file
     *
     * @param string $filePath
     * @return bool
     */
    public function deleteFile(string $filePath): bool
    {
        try {
            $fullPath = public_path($filePath);

            if (File::exists($fullPath)) {
                File::delete($fullPath);
                return true;
            }

            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Delete multiple files
     *
     * @param array $filePaths
     * @return array
     */
    public function deleteMultipleFiles(array $filePaths): array
    {
        $deleted = [];
        $failed = [];

        foreach ($filePaths as $path) {
            if ($this->deleteFile($path)) {
                $deleted[] = $path;
            } else {
                $failed[] = $path;
            }
        }

        return [
            'deleted' => $deleted,
            'failed' => $failed,
            'total_deleted' => count($deleted),
            'total_failed' => count($failed)
        ];
    }

    /**
     * Validate uploaded file
     *
     * @param UploadedFile $file
     * @param array $config
     * @return void
     * @throws Exception
     */
    private function validateFile(UploadedFile $file, array $config): void
    {
        // Check if file is valid
        if (!$file->isValid()) {
            throw new Exception('Invalid file upload');
        }

        // Check file size
        $sizeInKB = $file->getSize() / 1024;
        if ($sizeInKB > $config['max_size']) {
            throw new Exception("File size ({$sizeInKB}KB) exceeds maximum allowed size ({$config['max_size']}KB)");
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $config['extensions'])) {
            throw new Exception("File extension '{$extension}' is not allowed. Allowed: " . implode(', ', $config['extensions']));
        }

        // Check MIME type
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, $config['mime_types'])) {
            throw new Exception("File type '{$mimeType}' is not allowed");
        }
    }

    /**
     * Generate unique filename
     *
     * @param UploadedFile $file
     * @param array $options
     * @return string
     */
    private function generateFileName(UploadedFile $file, array $options = []): string
    {
        $extension = $file->getClientOriginalExtension();

        if (isset($options['keep_original_name']) && $options['keep_original_name']) {
            $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $name = Str::slug($name);
            return $name . '_' . Str::random(10) . '_' . time() . '.' . $extension;
        }

        if (isset($options['custom_name'])) {
            return Str::slug($options['custom_name']) . '_' . Str::random(10) . '_' . time() . '.' . $extension;
        }

        return time() . '_' . Str::random(10) . '.' . $extension;
    }

    /**
     * Get upload path
     *
     * @param string $type
     * @param string $subfolder
     * @return string
     */
    private function getUploadPath(string $type, string $subfolder = ''): string
    {
        $config = self::FILE_TYPES[$type];

        // Special handling for listings to match helper function expectations
        if ($subfolder === 'listings' && $type === 'image') {
            $basePath = 'uploads/listings';
        } else {
            $basePath = 'uploads/' . $config['directory'];

            if ($subfolder) {
                $basePath .= '/' . trim($subfolder, '/');
            }
        }

        // Add date-based organization
        $datePath = date('Y/m');
        $basePath .= '/' . $datePath;

        return $basePath;
    }

    /**
     * Ensure directory exists
     *
     * @param string $path
     * @return void
     */
    private function ensureDirectoryExists(string $path): void
    {
        $fullPath = public_path($path);

        if (!File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
        }
    }

    /**
     * Get file info
     *
     * @param string $filePath
     * @return array|null
     */
    public function getFileInfo(string $filePath): ?array
    {
        $fullPath = public_path($filePath);

        if (!File::exists($fullPath)) {
            return null;
        }

        return [
            'path' => $filePath,
            'url' => asset($filePath),
            'size' => File::size($fullPath),
            'mime_type' => File::mimeType($fullPath),
            'last_modified' => File::lastModified($fullPath),
            'exists' => true
        ];
    }

    /**
     * Resize image after upload (works with ImageService)
     *
     * @param string $filePath
     * @param array $sizes
     * @return array
     */
    public function createImageVariants(string $filePath, array $sizes = ['thumbnail_small', 'medium', 'large']): array
    {
        $variants = [];

        foreach ($sizes as $size) {
            try {
                // This would work with ImageService to create different sizes
                $variants[$size] = [
                    'url' => route('image.resize', [
                        'size' => $size,
                        'fit' => 'contain',
                        'path' => $filePath
                    ]),
                    'size' => $size
                ];
            } catch (Exception $e) {
                // Skip if size creation fails
                continue;
            }
        }

        return $variants;
    }

    /**
     * Clean old files (for maintenance)
     *
     * @param int $daysOld
     * @param string $directory
     * @return array
     */
    public function cleanOldFiles(int $daysOld = 30, string $directory = 'uploads/temp'): array
    {
        $cleaned = [];
        $errors = [];
        $cutoffTime = time() - ($daysOld * 24 * 60 * 60);

        try {
            $path = public_path($directory);

            if (!File::exists($path)) {
                return ['cleaned' => [], 'errors' => ['Directory does not exist']];
            }

            $files = File::allFiles($path);

            foreach ($files as $file) {
                if ($file->getMTime() < $cutoffTime) {
                    try {
                        File::delete($file->getPathname());
                        $cleaned[] = $file->getRelativePathname();
                    } catch (Exception $e) {
                        $errors[] = $file->getRelativePathname() . ': ' . $e->getMessage();
                    }
                }
            }
        } catch (Exception $e) {
            $errors[] = 'General error: ' . $e->getMessage();
        }

        return [
            'cleaned' => $cleaned,
            'errors' => $errors,
            'total_cleaned' => count($cleaned),
            'total_errors' => count($errors)
        ];
    }
}
