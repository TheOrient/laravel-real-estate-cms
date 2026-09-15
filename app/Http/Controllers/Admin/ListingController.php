<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Listing\UpdateAdminListingRequest;
use App\Models\Listing;
use App\Services\ListingService;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    protected $listingService;

    public function __construct(ListingService $listingService)
    {
        $this->listingService = $listingService;
    }

    /**
     * Display a listing of listings.
     */
    public function index(Request $request)
    {
        $filters = [];
        $showTrashed = $request->get('show_trashed', false);

        if ($request->has('approval_status')) {
            $filters['approval_status'] = $request->approval_status;
        }

        if ($request->has('active_status')) {
            $filters['active_status'] = $request->active_status;
        }

        // Add search functionality
        if ($request->has('search') && !empty($request->search)) {
            $filters['search'] = $request->search;
        }

        if ($request->has('category_id') && !empty($request->category_id)) {
            $filters['category_id'] = $request->category_id;
        }

        $listings = $this->listingService->getAdminListings($filters, $showTrashed);

        // Get categories for filter dropdown
        $defaultLanguageId = \App\Models\Language::where('is_default', true)->value('id');
        $categories = \App\Models\Category::leftJoin('category_descriptions', function($join) use ($defaultLanguageId) {
                $join->on('categories.id', '=', 'category_descriptions.category_id')
                     ->where('category_descriptions.language_id', '=', $defaultLanguageId);
            })
            ->select('categories.*')
            ->orderBy('category_descriptions.name')
            ->get();

        return view('admin.listings.index', compact('listings', 'categories', 'showTrashed'));
    }

    /**
     * View the listing in the frontend.
     */
    public function show($id)
    {
        $listing = Listing::findOrFail($id);
        return redirect()->route('listings.show', $listing->slug);
    }

    /**
     * Approve the listing.
     */
    public function approve($id)
    {
        $this->listingService->approveListing($id);

        return redirect()->route('admin.listings.index')
            ->with('success', __('admin/listings.listing_approved_successfully'));
    }

    /**
     * Reject the listing with reason.
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $this->listingService->rejectListing($id, $request->rejection_reason);

        return redirect()->route('admin.listings.index')
            ->with('success', __('admin/listings.listing_rejected_successfully'));
    }

    /**
     * Activate the listing.
     */
    public function activate($id)
    {
        $this->listingService->activateListing($id);

        return redirect()->route('admin.listings.index')
            ->with('success', __('admin/listings.listing_activated_successfully'));
    }

    /**
     * Deactivate the listing.
     */
    public function deactivate($id)
    {
        $this->listingService->deactivateListing($id);

        return redirect()->route('admin.listings.index')
            ->with('success', __('admin/listings.listing_deactivated_successfully'));
    }

    /**
     * Toggle featured status of the listing.
     */
    public function toggleFeatured($id)
    {
        $listing = Listing::findOrFail($id);
        $listing->update(['is_featured' => !$listing->is_featured]);

        $message = $listing->is_featured
            ? __('admin/listings.listing_featured_successfully')
            : __('admin/listings.listing_unfeatured_successfully');

        return redirect()->route('admin.listings.index')->with('success', $message);
    }

    /**
     * Remove the listing.
     */
    public function destroy($id)
    {
        $this->listingService->deleteListing($id);

        return redirect()->route('admin.listings.index')
            ->with('success', __('admin/listings.listing_moved_to_trash'));
    }

    /**
     * Permanently delete listing (admin only)
     */
    public function forceDestroy($id)
    {
        $this->listingService->forceDeleteListing($id);

        return redirect()->route('admin.listings.index',['show_trashed' => true])
            ->with('success', __('admin/listings.listing_deleted_permanently'));
    }

    /**
     * Restore soft deleted listing (admin only)
     */
    public function restore($id)
    {
        $this->listingService->restoreListing($id);

        return redirect()->route('admin.listings.index')
            ->with('success', __('admin/listings.listing_restored'));
    }
}
