<?php

namespace App\Constants;

class ImageSize
{

    // Available image size presets
    const SIZES = [
        'thumbnail_small' => '100x100',
        'gallery_thumbnail' => '550x400',
        'medium' => '300x300',
        'large' => '800x600',
        'blog_cover' => '800x500',   // 16:10 — blog kapak/kart görselleri
        'full' => '1200x1200',

        // Avatar / profile image sizes
        'avatar_small' => '64x64',      // liste kartları, küçük rozetler
        'avatar_medium' => '96x96',     // sidebar kartları
        'avatar_large' => '160x160',    // profil sayfası, büyük avatar

        // Home hero image sizes
        // Desktop: wide hero banner
        'home_hero_desktop' => '1920x1080',
        // Mobile: smaller width but same aspect ratio for performance
        'home_hero_mobile' => '768x432',
    ];

    // Available fit options
    const FIT = [
        'contain' => 'contain',     // Default. Resizes to fit within dimensions without cropping/distortion
        'max' => 'max',             // Like contain but won't upscale small images
        'fill' => 'fill',           // Resizes without cropping/distortion, fills remaining space with bg color
        'fill-max' => 'fill-max',   // Like fill but will upscale small images
        'stretch' => 'stretch',     // Stretches to fit dimensions exactly
        'crop' => 'crop',           // Resizes to fill dimensions and crops excess
        'crop-top' => 'cover-top',  // Crop from top
        'crop-bottom' => 'cover-bottom', // Crop from bottom
        'crop-center' => 'cover-center', // Crop from center (default for crop)
    ];

    // Available crop positions when using fit=crop
    const CROP_POSITIONS = [
        'top-left' => 'cover-top-left',
        'top' => 'cover-top',
        'top-right' => 'cover-top-right',
        'left' => 'cover-left',
        'center' => 'cover-center',
        'right' => 'cover-right',
        'bottom-left' => 'cover-bottom-left',
        'bottom' => 'cover-bottom',
        'bottom-right' => 'cover-bottom-right',
    ];

    // Image quality options (1-100)
    const QUALITY = [
        'low' => 60,
        'medium' => 80,
        'high' => 90,
    ];
}