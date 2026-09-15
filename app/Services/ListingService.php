<?php

namespace App\Services;

use App\Constants\ListingAttributeDisplayTypeConstant;
use App\Models\ListingAttributeCustomValue;
use App\Models\Listing;
use App\Models\Notification;
use App\Repositories\ListingRepository;
use App\Repositories\CategoryRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ListingService implements ServiceInterface
{
    protected $listingRepository;
    protected $categoryRepository;
    protected $notificationService;

    public function __construct(
        ListingRepository $listingRepository,
        CategoryRepository $categoryRepository,
        NotificationService $notificationService
    ) {
        $this->listingRepository = $listingRepository;
        $this->categoryRepository = $categoryRepository;
        $this->notificationService = $notificationService;
    }

    public function getListing(string $slug,$language_id = null)
    {
        // İlk önce ilanı bul (onay durumuna bakma)
        $listing = $this->listingRepository->getActiveBySlug($slug,$language_id);

        // Onay ve aktiflik kontrol et
        if (!$listing->is_approved || !$listing->is_active) {
            // Eğer onaylanmamış veya aktif değilse sadece admin veya ilan sahibi görebilir
            if (!Auth::check()) {
                abort(404);
            }

            $user = Auth::user();
            $isAdmin = $user->user_role === 'admin';
            $isOwner = $user->id === $listing->user_id;

            if (!$isAdmin && !$isOwner) {
                abort(404);
            }
        }

        $listing->increment('view_count');

        // Cache similar listings for better performance
        $similarListingsCacheKey = "similar_listings_{$listing->category_id}_{$listing->id}";

        // Try to use cache tags if supported, otherwise use regular caching
        try {
            $similarListings = Cache::tags(['similar_listings', "category_{$listing->category_id}"])
                ->remember($similarListingsCacheKey, now()->addMinutes(30), function () use ($listing) {
                    return $this->listingRepository->getSimilarListings(
                        $listing->category_id,
                        $listing->id
                    );
                });
        } catch (\Exception $e) {
            // Fallback for file-based cache
            $similarListings = Cache::remember($similarListingsCacheKey, now()->addMinutes(30), function () use ($listing) {
                return $this->listingRepository->getSimilarListings(
                    $listing->category_id,
                    $listing->id
                );
            });

            // Store cache key for later invalidation
            $this->storeCacheKeyForCategory($listing->category_id, $similarListingsCacheKey);
        }

        $categoryAttributes = $listing->category->getAllAttributes();

        // Group attributes by position
        $topAttributes = $categoryAttributes->filter(function ($attribute) {
            return $attribute->pivot->property_detail_position === 'top';
        });

        $bottomAttributes = $categoryAttributes->filter(function ($attribute) {
            return $attribute->pivot->property_detail_position === 'bottom';
        });

        $groupedAttributes = $listing->attributeValues
            ->filter(function ($attributeValue) {
                return $attributeValue->attribute->show_in_listing;
            })
            ->groupBy(function ($attributeValue) {
                return $attributeValue->attribute->id;
            });

        $customAttributes = $listing->customAttributeValues
            ->filter(function ($customValue) {
                return $customValue->attribute->show_in_listing;
            })
            ->groupBy(function ($customValue) {
                return $customValue->attribute_id;
            });

        return [
            'listing' => $listing,
            'similarListings' => $similarListings,
            'categoryAttributes' => $categoryAttributes,
            'topAttributes' => $topAttributes,
            'bottomAttributes' => $bottomAttributes,
            'groupedAttributes' => $groupedAttributes,
            'customAttributes' => $customAttributes
        ];
    }

    public function createListing(array $data, array $attributeData)
    {
        DB::beginTransaction();

        try {
            $listing = $this->listingRepository->create($data);

            $category = $this->categoryRepository->find($data['category_id']);
            $categoryAttributes = $category->getAllAttributes();

            foreach ($categoryAttributes as $attribute) {
                $attributeKey = 'attribute_' . $attribute->id;

                if (isset($attributeData[$attributeKey])) {
                    $attributeValue = $attributeData[$attributeKey];

                    if (in_array($attribute->display_type_search, ListingAttributeDisplayTypeConstant::getSingularPredefinedValueTypes())) {
                        $listing->attributeValues()->attach($attributeValue);
                    } elseif ($attribute->display_type_search === 'checkbox') {
                        if (is_array($attributeValue)) {
                            foreach ($attributeValue as $valueId) {
                                $listing->attributeValues()->attach($valueId);
                            }
                        }
                    } else {
                        ListingAttributeCustomValue::create([
                            'listing_id' => $listing->id,
                            'attribute_id' => $attribute->id,
                            'value' => (string) $attributeValue
                        ]);
                    }
                }
            }

            // Ön yüz / tek form oluşturma: admin onay kuyruğu yok; doğrudan yayında (UserListingController ile aynı mantık)
            $listing->update([
                'is_approved' => true,
                'is_active' => true,
                'is_sent_to_approver' => false,
                'sent_to_approver_at' => null,
                'approved_at' => now(),
                'expires_at' => now()->addDays(30),
            ]);

            // Clear similar listings cache for this category when new listing is created
            $this->clearSimilarListingsCacheForCategory($data['category_id']);

            DB::commit();
            return $listing;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getAdminListings(array $filters = [], $showTrashed = false)
    {
        return $this->listingRepository->getFilteredListingsForAdmin($filters, $showTrashed);
    }

    public function approveListing($id)
    {
        $listing = $this->listingRepository->find($id);
        $wasApproved = $listing->is_approved;

        // Check if listing has expired (based on listing's expires_at, not package)
        $listingWasExpired = $listing->expires_at && $listing->expires_at->isPast();

        // Determine if we should decrement user's package quota
        // 1. New listing (never approved before)
        // 2. Expired listing being re-approved (re-publishing)
        $shouldDecrementQuota = !$wasApproved || $listingWasExpired;

        $result = $this->listingRepository->updateApprovalStatus($id, true);

        // Clear similar listings cache when listing is approved
        if ($result) {
            $this->clearSimilarListingsCacheForCategory($result->category_id);

            // Set listing expiration date to 30 days from now
            $result->update(['expires_at' => now()->addDays(30)]);

            // Package system removed - no quota decrement needed

            // Send notification to user if listing was just approved
            if (!$wasApproved && $result->is_approved) {
                try {
                    $this->notificationService->notifyListingApproved($result);
                } catch (\Exception $e) {
                    // Log error but don't break the flow
                    Log::error('Failed to send listing approved notification: ' . $e->getMessage());
                }
            }
        }

        return $result;
    }

    public function rejectListing($id, $reason)
    {
        $listing = $this->listingRepository->find($id);

        // Reset approval tracking fields when rejecting
        $listing->is_approved = false;
        $listing->is_sent_to_approver = false;
        $listing->sent_to_approver_at = null;
        $listing->save();

        // Clear similar listings cache
        $this->clearSimilarListingsCacheForCategory($listing->category_id);

        // Send rejection notification with reason
        try {
            $this->notificationService->notifyListingRejected($listing, $reason);
        } catch (\Exception $e) {
            Log::error('Failed to send listing rejection notification: ' . $e->getMessage());
        }

        return $listing;
    }

    public function activateListing($id)
    {
        $result = $this->listingRepository->updateActiveStatus($id, true);

        // Clear similar listings cache when listing is activated
        if ($result) {
            $this->clearSimilarListingsCacheForCategory($result->category_id);
        }

        return $result;
    }

    public function deactivateListing($id)
    {
        $result = $this->listingRepository->updateActiveStatus($id, false);

        // Clear similar listings cache when listing is deactivated
        if ($result) {
            $this->clearSimilarListingsCacheForCategory($result->category_id);
        }

        return $result;
    }

    /**
     * Soft delete listing (for normal users and admin panel)
     */
    public function deleteListing($id)
    {
        $listing = $this->listingRepository->find($id);
        if (!$listing) {
            return false;
        }

        $categoryId = $listing->category_id;
        $this->clearSimilarListingsCacheForCategory($categoryId);

        // Soft delete the listing
        return $listing->delete();
    }

    /**
     * Permanent delete listing (admin only)
     * This will also delete all images and related data
     */
    public function forceDeleteListing($id)
    {
        $listing = $this->listingRepository->findTrashed($id);
        if (!$listing) {
            return false;
        }

        $categoryId = $listing->category_id;
        $images = $listing->images()->get();

        $this->clearSimilarListingsCacheForCategory($categoryId);

        // Delete physical image files
        $images->each(function ($image) {
            $imagePath = public_path('uploads/listings/' . $image->image);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            $image->delete();
        });

        // Permanently delete the listing
        return $listing->forceDelete();
    }

    /**
     * Restore soft deleted listing (admin only)
     */
    public function restoreListing($id)
    {
        $listing = \App\Models\Listing::withTrashed()->find($id);
        if (!$listing) {
            return false;
        }

        $this->clearSimilarListingsCacheForCategory($listing->category_id);
        return $listing->restore();
    }

    /**
     * Renew listing by extending expiration date and using user quota
     */
    public function renewListing(Listing $listing): bool
    {
        try {
            DB::beginTransaction();

            $user = $listing->user;

            // Check package quota only if package system is enabled
            // Package system removed - just extend the listing without quota check
            $listing->update([
                'expires_at' => Carbon::now()->addDays(30),
            ]);

            Log::info('Listing auto-renewed', [
                'listing_id' => $listing->id,
                'user_id' => $user->id,
                'new_expires_at' => $listing->expires_at,
            ]);

            // Send site notification
            $notificationData = [
                'title' => 'İlanınız Otomatik Yenilendi',
                'type' => 'listing_renewed',
                'target' => Notification::TARGET_USER,
                'user_id' => $user->id,
                'related_type' => Listing::class,
                'related_id' => $listing->id,
            ];

            $notificationData['message'] = "'{$listing->title}' başlıklı ilanınızın 30 günlük yayın süresi dolmuştu ve otomatik olarak 30 gün daha uzatıldı. Yeni bitiş tarihi: " . $listing->expires_at->format('d.m.Y');
            $notificationData['action_links'] = [
                ['title' => 'İlanı Görüntüle', 'url' => route('listings.show', $listing->slug), 'type' => 'primary']
            ];

            Notification::create($notificationData);

            // Send email notification
            try {
                Mail::to($user->email)->queue(new \App\Mail\ListingRenewedMail($listing, $user));
            } catch (\Exception $e) {
                Log::error('Failed to send listing renewed email', [
                    'user_id' => $user->id,
                    'listing_id' => $listing->id,
                    'error' => $e->getMessage()
                ]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Listing renewal failed', [
                'listing_id' => $listing->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Deactivate expired listing when user has no quota (sends notifications)
     */
    public function deactivateExpiredListing(Listing $listing): bool
    {
        try {
            DB::beginTransaction();

            $user = $listing->user;

            // Deactivate the listing
            $listing->update([
                'is_active' => false,
                'status' => 'expired',
            ]);

            Log::info('Listing deactivated (no quota)', [
                'listing_id' => $listing->id,
                'user_id' => $user->id,
            ]);

            // Send site notification
            $notificationData = [
                'title' => 'İlanınızın Süresi Doldu ve Pasife Alındı',
                'type' => 'listing_expired',
                'target' => Notification::TARGET_USER,
                'user_id' => $user->id,
                'related_type' => Listing::class,
                'related_id' => $listing->id,
                'is_important' => true,
            ];

            $notificationData['message'] = "'{$listing->title}' başlıklı ilanınızın 30 günlük yayın süresi doldu ve yayından kaldırıldı. İlanınızı tekrar yayınlamak için düzenleme yapabilirsiniz. Her ilan admin onayından sonra 30 gün yayında kalır.";
            $notificationData['action_links'] = [
                ['title' => 'İlanı Görüntüle', 'url' => route('listings.show', $listing->slug), 'type' => 'primary']
            ];

            Notification::create($notificationData);

            // Send email notification
            try {
                Mail::to($user->email)->queue(new \App\Mail\ListingExpiredMail($listing, $user));
            } catch (\Exception $e) {
                Log::error('Failed to send listing expired email', [
                    'user_id' => $user->id,
                    'listing_id' => $listing->id,
                    'error' => $e->getMessage()
                ]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Listing deactivation failed', [
                'listing_id' => $listing->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Clear similar listings cache for a specific category
     */
    public function clearSimilarListingsCacheForCategory($categoryId)
    {
        // Try to use cache tags if supported (Redis/Memcached)
        try {
            Cache::tags(['similar_listings', "category_{$categoryId}"])->flush();
        } catch (\Exception $e) {
            // Fallback for file-based cache - store and clear individual keys
            $cacheKeysKey = "similar_listings_keys_category_{$categoryId}";
            $cacheKeys = Cache::get($cacheKeysKey, []);

            // Clear all stored cache keys for this category
            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }

            // Clear the keys registry
            Cache::forget($cacheKeysKey);
        }
    }

    /**
     * Store cache key for later invalidation
     */
    private function storeCacheKeyForCategory($categoryId, $cacheKey)
    {
        $cacheKeysKey = "similar_listings_keys_category_{$categoryId}";
        $cacheKeys = Cache::get($cacheKeysKey, []);
        $cacheKeys[] = $cacheKey;

        // Store keys registry for 24 hours (longer than individual cache)
        Cache::put($cacheKeysKey, array_unique($cacheKeys), now()->addHours(24));
    }
}
