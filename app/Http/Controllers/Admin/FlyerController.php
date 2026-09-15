<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Services\PDFGeneratorService;
use Illuminate\Http\Request;

/**
 * Generates a printable / downloadable flyer for a single listing.
 *
 * Query string options:
 *   ?theme=orange|blue|green|red|purple|gold
 *   ?orientation=portrait|landscape
 *
 * Missing or invalid values fall back to the operator's defaults
 * (settings.flyer_default_theme, settings.flyer_default_orientation).
 */
class FlyerController extends Controller
{
    public function __construct(protected PDFGeneratorService $generator) {}

    public function download(Request $request, Listing $listing)
    {
        $theme = $request->query('theme');
        $orientation = $request->query('orientation');

        return $this->generator->generateListingFlyer($listing, $theme, $orientation);
    }
}
