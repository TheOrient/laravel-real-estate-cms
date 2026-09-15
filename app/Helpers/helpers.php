<?php

/**
 * function to generate image url for listings
 */
function listing_image_url($image, $size = 'medium', $fit = 'contain'){
    // Check if image is null or empty
    if (empty($image)) {
        // Return default placeholder image
        return asset('images/placeholder.jpg');
    }

    // External URLs (Unsplash CDN used by sample seeder, etc.) bypass
    // Glide — Glide can only resize images on the local "uploads" disk.
    if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
        return $image;
    }

    // If image already contains the full path, use it directly
    if (str_contains($image, 'uploads/')) {
        $path = $image;
    } else {
        // For listing images: uploads/listings/YYYY/MM/filename
        $path = 'uploads/listings/' . $image;
    }

    // Use the image resize route with the given parameters
    return route('image.resize', [
        'size' => $size,
        'fit' => $fit,
        'path' => $path
    ]);
}

/**
 * function to generate image url for users (legacy - simple asset path)
 */
function user_image_url($image){
    return asset('uploads/users/'.$image);
}

/**
 * Generate resized avatar URL using Glide (similar to listings)
 */
function user_avatar_url($image, $size = 'avatar_medium', $fit = 'crop')
{
    if (empty($image)) {
        return asset('images/placeholder.jpg');
    }

    // If image already contains the full uploads path, use it directly
    if (str_contains($image, 'uploads/')) {
        $path = $image;
    } else {
        // Default user uploads directory
        $path = 'uploads/user/' . ltrim($image, '/');
    }

    return route('image.resize', [
        'size' => $size,
        'fit' => $fit,
        'path' => $path,
    ]);
}

/**
 * Get FileUploadService instance
 */
function fileUpload(): \App\Services\FileUploadService {
    return app(\App\Services\FileUploadService::class);
}

/**
 * Quick file upload helper
 */
function uploadFile($file, $type = 'image', $subfolder = '', $options = []) {
    return fileUpload()->uploadFile($file, $type, $subfolder, $options);
}

/**
 * Quick multiple files upload helper
 */
function uploadMultipleFiles($files, $type = 'image', $subfolder = '', $options = []) {
    return fileUpload()->uploadMultipleFiles($files, $type, $subfolder, $options);
}

/**
 * Check if package system is enabled
 * NOTE: Package system is permanently disabled
 */
function is_package_system_enabled(): bool {
    return false;
}

/**
 * Read a setting value with a sensible fallback.
 *
 * The optional $default lets call sites stay self-contained — new
 * features don't have to remember to seed a row in `settings` before
 * they ship; the fallback kicks in transparently.
 */
function get_setting($key, $default = null){
    $value = \App\Models\Setting::get($key);
    return ($value === null || $value === '') ? $default : $value;
}

/**
 * Set a setting
 */
function set_setting($key, $value){
    return \App\Models\Setting::set($key, $value);
}

/**
 * Get language configuration from config/app.php by code
 */
function language_config(string $code): ?array
{
    $languages = config('app.languages', []);

    return $languages[$code] ?? null;
}

/**
 * Resolve language icon URL by merging DB (Language model) and config/app.php.
 *
 * - Önce parametre Language modeli ise code alanını kullanır.
 * - Sonra config('app.languages.{code}.icon') değerini okuyup asset() ile URL üretir.
 * - Icon tanımlı değilse null döner.
 *
 * @param \App\Models\Language|string $languageOrCode
 */
function language_icon_url($languageOrCode): ?string
{
    if ($languageOrCode instanceof \App\Models\Language) {
        $code = $languageOrCode->code;
    } else {
        $code = (string) $languageOrCode;
    }

    if (!$code) {
        return null;
    }

    $config = language_config($code);

    if (!$config || empty($config['icon'])) {
        return null;
    }

    return asset($config['icon']);
}

/**
 * Get page slug for current language
 *
 * @param string $identifier Page identifier (about, contact, etc.)
 * @return string The slug for the current language
 */
function getPageSlug($identifier)
{
    // Public URLs deliberately keep stable Turkish slugs in every locale.
    // Avoid repeated page/language queries from header and footer rendering.
    $slugMap = [
        'about' => 'hakkimizda',
        'contact' => 'iletisim',
    ];

    return $slugMap[$identifier] ?? $identifier;
}
