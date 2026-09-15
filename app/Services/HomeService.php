<?php

namespace App\Services;

use App\Models\Blog;
use App\Repositories\CategoryRepository;
use App\Repositories\ListingRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class HomeService implements ServiceInterface
{
    protected $categoryRepository;
    protected $listingRepository;

    public function __construct(
        CategoryRepository $categoryRepository,
        ListingRepository $listingRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->listingRepository = $listingRepository;
    }

    public function getHomePageData(): array
    {
        return [
            'categories'       => $this->getMainCategories(),
            'featuredListings' => $this->getFeaturedListings(),
            'latestListings'   => $this->getLatestListings(),
            'latestBlogPosts'  => $this->getLatestBlogPosts(),
        ];
    }

    /**
     * Latest published blog posts for the home page "blog" strip.
     *
     * Defensive about the `blogs` table existence so a clean install
     * that hasn't migrated yet doesn't blow up the home page.
     */
    protected function getLatestBlogPosts(int $limit = 3): Collection
    {
        if (! Schema::hasTable('blogs')) {
            return collect();
        }

        return Blog::latestPublished()
            ->with(['descriptions', 'author:id,first_name,last_name'])
            ->limit($limit)
            ->get();
    }

    protected function getMainCategories(): Collection
    {
        return $this->categoryRepository->getActiveWithChildren();
    }

    /**
     * Get featured listings for home page
     */
    protected function getFeaturedListings(): Collection
    {
        return $this->listingRepository->getFeaturedListings(4);
    }

    protected function getLatestListings(): Collection
    {
        return $this->listingRepository->getLatestActiveListings();
    }

}
