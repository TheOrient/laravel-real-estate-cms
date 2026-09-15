<?php

namespace App\Http\Controllers;

use App\Services\HomeService;
use Illuminate\View\View;

class HomeController extends Controller
{
    protected $homeService;

    public function __construct(HomeService $homeService)
    {
        $this->homeService = $homeService;
    }

    /**
     * Show the application home page.
     */
    public function index(): View
    {
        $data = $this->homeService->getHomePageData();
        return view('home', $data);
    }
}
