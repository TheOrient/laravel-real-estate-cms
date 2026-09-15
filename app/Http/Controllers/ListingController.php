<?php

namespace App\Http\Controllers;

use App\Services\ListingService;
use Illuminate\View\View;

class ListingController extends Controller
{
    protected $listingService;

    public function __construct(ListingService $listingService)
    {
        $this->listingService = $listingService;
    }

    /**
     * Display the listing details.
     */
    public function show(string $slug): View
    {
        return view('listings.show', $this->listingService->getListing($slug));
    }

}
