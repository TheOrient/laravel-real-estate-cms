<?php

namespace App\Services;

use App\Constants\ImageSize;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use League\Glide\ServerFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;
class ImageService implements ServiceInterface
{
    public function detectMime($path)
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'application/octet-stream',
        };
    }

    public function resizeImage($size, $fit, $path)
    {
        // Validate size parameter
        if (!array_key_exists($size, ImageSize::SIZES)) {
            throw new \Exception('Invalid size parameter');
        }

        // Validate fit parameter
        if (!array_key_exists($fit, ImageSize::FIT)) {
            throw new \Exception('Invalid fit parameter');
        }

        // Validate path
        if (preg_match('/[^a-zA-Z0-9\/\-\_\.]/', $path)) {
            throw new \Exception('Invalid path parameter');
        }

        // Resolve path. Kapak/görsel yolları bazen 'uploads/' öneki OLMADAN
        // kaydedilebiliyor; 'uploads' diski public/ köküne bağlı olduğundan
        // önce olduğu gibi, bulunamazsa 'uploads/' önekiyle tekrar dene.
        if (!Storage::disk('uploads')->exists($path)) {
            if (!str_starts_with($path, 'uploads/') && Storage::disk('uploads')->exists('uploads/' . $path)) {
                $path = 'uploads/' . $path;
            } else {
                throw new \Exception('Image not found');
            }
        }

        // Parse size into width and height
        [$width, $height] = explode('x', ImageSize::SIZES[$size]);

        $cachePath = storage_path('app/cache');
        if (!File::exists($cachePath)) {
            File::makeDirectory($cachePath, 0755, true);
        }

        $server = ServerFactory::create([
            'source' => Storage::disk('uploads')->getDriver(),
            'cache' => $cachePath,
            'base_url' => 'img',
            'cache_path_prefix' => '.cache',
            'driver' => 'gd',
        ]);

        $options = [
            'w' => $width,
            'h' => $height,
            'fit' => ImageSize::FIT[$fit],
        ];

        // 🔥 getImageResponse OLMADAN en stabil çözüm ↓↓↓
        return new StreamedResponse(function () use ($server, $path, $options) {
            $server->outputImage($path, $options);
        }, 200, [
            'Content-Type' => $this->detectMime($path),
            'Cache-Control' => 'public, max-age=31536000',
            'X-Accel-Buffering' => 'no', // Large image fix (özellikle Apache/Nginx)
        ]);
    }

    /**
     * Clear all cached versions of a specific image
     */
    public function clearImageCache($imagePath)
    {
        $cachePath = storage_path('app/cache/.cache');
        $imageCachePath = $cachePath . DIRECTORY_SEPARATOR . $imagePath;

        // Remove the specific image cache
        if (File::exists($imageCachePath)) {
            File::delete($imageCachePath);
        }
        // Also remove any cached versions with different parameters
        $imageDirPath = dirname($imageCachePath);
        $imageFileName = basename($imageCachePath);
        $imageNameWithoutExt = pathinfo($imageFileName, PATHINFO_FILENAME);
        $imageExt = pathinfo($imageFileName, PATHINFO_EXTENSION);

        // check if folder exists, clear all files in the folder
        if (File::isDirectory($imageDirPath)) {
            File::cleanDirectory($imageDirPath);
        }
    }

    /**
     * Delete image file and its cache
     */
    public function deleteImage($imagePath)
    {
        // Clear cache first
        $this->clearImageCache($imagePath);

        // Delete the original image
        if (Storage::disk('uploads')->exists($imagePath)) {
            Storage::disk('uploads')->delete($imagePath);
        }
    }
}
