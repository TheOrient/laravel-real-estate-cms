<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\Category;
use App\Models\Blog;
use App\Models\ContactMessage;
use App\Models\Page;
use App\Models\User;
use App\Constants\UserRolesConstant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService implements ServiceInterface
{
    protected $userService;
    protected $listingService;
    protected $categoryService;

    public function __construct(
        UserService $userService,
        ListingService $listingService,
        CategoryService $categoryService
    ) {
        $this->userService = $userService;
        $this->listingService = $listingService;
        $this->categoryService = $categoryService;
    }

    public function getDashboardStats(): array
    {
        $stats = [
            'totalUsers' => User::where('user_role', UserRolesConstant::AGENT)->count(),
            'totalListings' => Listing::count(),
            'totalCategories' => Category::count(),
            'newUsers' => User::where('user_role', UserRolesConstant::AGENT)
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
            'newListings' => Listing::where('created_at', '>=', now()->subDays(7))->count(),
            'approvedListings' => Listing::where('is_approved', true)->count(),
            'featuredListings' => Listing::where('is_featured', true)->count(),
            'totalBlogs' => Blog::where('is_active', true)->count(),
            'totalPages' => Page::where('is_active', true)->count(),
            'unreadMessages' => ContactMessage::whereNull('read_at')->count(),
            'listingsByStatus' => $this->getListingsByStatus(),
            'topCategories' => $this->getTopCategories(),
            'recentListings' => $this->getRecentListings(),
            'recentMessages' => $this->getRecentMessages(),
        ];

        return $stats;
    }

    protected function getListingsByStatus(): array
    {
        return Listing::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
    }

    protected function getTopCategories(): Collection
    {
        return Category::orderBy('listings_count', 'desc')
            ->take(5)
            ->get();
    }

    protected function getRecentListings(): Collection
    {
        return Listing::with(['user', 'category'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
    }

    protected function getRecentMessages(): Collection
    {
        return ContactMessage::query()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    }
}
