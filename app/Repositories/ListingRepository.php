<?php

namespace App\Repositories;

use App\Models\Listing;
use Illuminate\Pagination\LengthAwarePaginator;

class ListingRepository extends BaseRepository
{
    public function model(): string
    {
        return Listing::class;
    }

    public function getActiveBySlug(string $slug, $language_id = null)
    {
        // Look up a listing whose `listing_descriptions` row has this slug
        // in ANY language — falls back to TR when the visitor is on EN
        // but only a TR translation exists. Prevents the classic
        // /listing/{tr-slug}-404 when locale switches mid-flow.
        $relations = [
            'descriptions',
            'category',
            'user',
            'city',
            'district',
            'neighborhood',
            'images' => function ($query) {
                $query->orderBy('is_primary', 'desc')
                    ->orderBy('sort_order', 'asc');
            },
            'attributeValues.attribute',
            'customAttributeValues.attribute',
        ];

        $query = $this->model->where('is_active', true)
            ->whereHas('descriptions', function ($q) use ($slug, $language_id) {
                $q->where('slug', $slug);
                if ($language_id) {
                    $q->where('language_id', $language_id);
                }
            })
            ->with($relations);

        $listing = $query->first();

        // Slug not found in the requested locale — retry without the
        // language constraint so visitors on EN can still open TR-only
        // listings (and vice-versa).
        if (! $listing && $language_id) {
            $listing = $this->model->where('is_active', true)
                ->whereHas('descriptions', function ($q) use ($slug) {
                    $q->where('slug', $slug);
                })
                ->with($relations)
                ->first();
        }

        if (! $listing) {
            abort(404);
        }

        return $listing;
    }

    public function getApprovedBySlug(string $slug)
    {
        return $this->model->where('slug', $slug)
            ->where('is_active', true)
            ->where('is_approved', true)
            ->with([
                'category',
                'user',
                'city',
                'district',
                'neighborhood',
                'images' => function ($query) {
                    $query->orderBy('is_primary', 'desc')
                        ->orderBy('sort_order', 'asc');
                },
                'attributeValues.attribute',
                'customAttributeValues.attribute'
            ])
            ->firstOrFail();
    }

    public function getSimilarListings($categoryId, $excludeId, $limit = 4)
    {
        return $this->model->where('category_id', $categoryId)
            ->where('id', '!=', $excludeId)
            ->where('is_active', true)
            ->where('is_approved', true)
            ->with(['district', 'city', 'neighborhood'])
            ->inRandomOrder()
            ->take($limit)
            ->get();
    }

    public function getFilteredListingsForAdmin(array $filters = [], $showTrashed = false): LengthAwarePaginator
    {
        $query = $this->model->with(['user', 'category', 'city']);

        // Handle trashed listings
        if ($showTrashed) {
            $query->onlyTrashed();
        }

        if (isset($filters['approval_status'])) {
            if ($filters['approval_status'] == 'pending') {
                $query->where('is_sent_to_approver', true)
                    ->where('is_approved', false);
            } elseif ($filters['approval_status'] == 'approved') {
                $query->where('is_approved', true);
            } elseif ($filters['approval_status'] == 'not_approved') {
                $query->where('is_approved', false);
            }
        }

        if (isset($filters['active_status'])) {
            $query->where('is_active', $filters['active_status'] == 'active');
        }

        // Search functionality - search by listing title, ID, or user name
        if (isset($filters['search']) && !empty($filters['search'])) {
            $searchTerm = $filters['search'];

            $query->where(function ($q) use ($searchTerm) {
                // Search by ID (exact match)
                if (is_numeric($searchTerm)) {
                    $q->where('id', $searchTerm);
                }

                // Search by title (partial match)
                $q->orWhereHas('descriptions', function ($descriptionQuery) use ($searchTerm) {
                    $descriptionQuery->where('title', 'LIKE', '%' . $searchTerm . '%');
                })
                    // Search by user name
                    ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                        $userQuery->where(function ($nameQuery) use ($searchTerm) {
                            $nameQuery->where('first_name', 'LIKE', '%' . $searchTerm . '%')
                                ->orWhere('last_name', 'LIKE', '%' . $searchTerm . '%');
                        });
                    });
            });
        }

        // Filter by category
        if (isset($filters['category_id']) && !empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }


        return $query->latest()->paginate(15);
    }

    public function updateApprovalStatus($id, bool $isApproved)
    {
        $listing = $this->find($id);
        $listing->is_approved = $isApproved;

        // If approving, set approved_at and reset approval tracking fields
        if ($isApproved) {
            $listing->approved_at = now();
            $listing->is_sent_to_approver = false;
            $listing->sent_to_approver_at = null;
        }

        $listing->save();
        return $listing;
    }

    public function updateActiveStatus($id, bool $isActive)
    {
        $listing = $this->find($id);
        $listing->is_active = $isActive;
        $listing->save();
        return $listing;
    }

    public function getAllActiveListings(int $perPage = 10, $languageId = null)
    {
        return $this->model->where('is_active', true)
            ->where('is_approved', true)
            ->with(['descriptions', 'images', 'category.descriptions', 'city', 'district', 'neighborhood'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getLatestActiveListings(int $limit = 8)
    {
        return $this->model->where('is_active', true)
            ->where('is_approved', true)
            ->with([
                'descriptions',
                'user',
                'images',
                'category.descriptions',
                'city',
                'district',
                'attributeValues.attribute',
                'customAttributeValues.attribute',
            ])
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get featured listings for home page
     */
    public function getFeaturedListings(int $limit = 8)
    {
        return $this->model->where('is_active', true)
            ->where('is_approved', true)
            ->where('is_featured', true)
            ->with([
                'descriptions',
                'user',
                'images',
                'category.descriptions',
                'city',
                'district',
                'attributeValues.attribute',
                'customAttributeValues.attribute',
            ])
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }
}
