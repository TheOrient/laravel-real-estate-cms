<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Listing;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;
use App\Mail\ListingApprovedMail;
use App\Mail\ListingRejectedMail;
use App\Mail\NewListingMail;
use App\Mail\ListingExpiredMail;
use App\Mail\ListingSubmittedForApprovalMail;
use App\Mail\ListingResubmittedForApprovalMail;

class NotificationService implements ServiceInterface
{
    /**
     * Create a new notification
     */
    public function create(array $data): Notification
    {
        // Set default values
        $data['is_read'] = $data['is_read'] ?? false;
        $data['is_important'] = $data['is_important'] ?? false;
        $data['target'] = $data['target'] ?? Notification::TARGET_USER;
        $data['type'] = $data['type'] ?? Notification::TYPE_CUSTOM;
        $data['created_by'] = $data['created_by'] ?? auth()->id();

        return Notification::create($data);
    }

    /**
     * Send listing approved notification to user (site + email)
     */
    public function notifyListingApproved(Listing $listing): Notification
    {
        $actionLinks = [
            [
                'title' => 'İlanı Görüntüle',
                'url' => route('listings.show', $listing->slug),
                'type' => 'primary'
            ],
            [
                'title' => 'İlanlarım',
                'url' => route('user.listings.my'),
                'type' => 'secondary'
            ]
        ];

        // Create site notification
        $notification = $this->create([
            'title' => 'İlanınız Onaylandı!',
            'message' => '"' . $listing->title . '" başlıklı ilanınız onaylandı ve yayınlandı.',
            'type' => Notification::TYPE_LISTING_APPROVED,
            'target' => Notification::TARGET_USER,
            'user_id' => $listing->user_id,
            'related_type' => Listing::class,
            'related_id' => $listing->id,
            'action_links' => $actionLinks,
            'is_important' => true,
        ]);

        // Send email notification (queued)
        try {
            Mail::to($listing->user->email)->queue(new ListingApprovedMail($listing));
        } catch (\Exception $e) {
            \Log::error('Failed to send listing approved email', [
                'listing_id' => $listing->id,
                'error' => $e->getMessage()
            ]);
        }

        return $notification;
    }

    /**
     * Send listing rejected notification to user (site + email)
     */
    public function notifyListingRejected(Listing $listing, ?string $reason = null): Notification
    {
        $message = '"' . $listing->title . '" başlıklı ilanınız onaylanmadı.';
        if ($reason) {
            $message .= ' Sebep: ' . $reason;
        }

        $actionLinks = [
            [
                'title' => 'İlanı Düzenle',
                'url' => route('user.listings.edit', $listing->id),
                'type' => 'primary'
            ],
            [
                'title' => 'İlanlarım',
                'url' => route('user.listings.my'),
                'type' => 'secondary'
            ]
        ];

        // Create site notification
        $notification = $this->create([
            'title' => 'İlan Onaylanmadı',
            'message' => $message,
            'type' => Notification::TYPE_LISTING_REJECTED,
            'target' => Notification::TARGET_USER,
            'user_id' => $listing->user_id,
            'related_type' => Listing::class,
            'related_id' => $listing->id,
            'action_links' => $actionLinks,
            'is_important' => true,
        ]);

        // Send email notification (queued)
        try {
            Mail::to($listing->user->email)->queue(new ListingRejectedMail($listing, $reason));
        } catch (\Exception $e) {
            \Log::error('Failed to send listing rejected email', [
                'listing_id' => $listing->id,
                'error' => $e->getMessage()
            ]);
        }

        return $notification;
    }

    /**
     * Send new listing notification to admin (site + email)
     */
    public function notifyAdminNewListing(Listing $listing): Notification
    {
        $actionLinks = [
            [
                'title' => 'İlanı İncele',
                'url' => route('admin.listings.show', $listing->id),
                'type' => 'primary'
            ],
            [
                'title' => 'İlanları Yönet',
                'url' => route('admin.listings.index', ['approval_status' => 'pending']),
                'type' => 'secondary'
            ]
        ];

        // Create site notification
        $notification = $this->create([
            'title' => 'Yeni İlan Bekliyor',
            'message' => $listing->user->name . ' tarafından "' . $listing->title . '" başlıklı yeni ilan eklendi. Onayınızı bekliyor.',
            'type' => Notification::TYPE_NEW_LISTING,
            'target' => Notification::TARGET_ADMIN,
            'related_type' => Listing::class,
            'related_id' => $listing->id,
            'action_links' => $actionLinks,
            'created_by' => $listing->user_id,
        ]);

        // Send email notification to admins (queued)
        try {
            // Bildirim adresi: önce notification_email, sonra contact_email.
            $adminEmail = trim((string) Setting::get('notification_email'))
                ?: (trim((string) Setting::get('contact_email'))
                    ?: config('mail.admin_email', config('mail.from.address')));
            Mail::to($adminEmail)->queue(new NewListingMail($listing));
        } catch (\Exception $e) {
            \Log::error('Failed to send new listing email to admin', [
                'listing_id' => $listing->id,
                'error' => $e->getMessage()
            ]);
        }

        return $notification;
    }

    /**
     * Send listing expired notification (site + email)
     */
    public function notifyListingExpired(Listing $listing): Notification
    {
        $actionLinks = [
            [
                'title' => 'İlanı Yenile',
                'url' => route('user.listings.edit', $listing->id),
                'type' => 'primary'
            ],
            [
                'title' => 'İlanlarım',
                'url' => route('user.listings.my'),
                'type' => 'secondary'
            ]
        ];

        // Create site notification
        $notification = $this->create([
            'title' => 'İlan Süresi Doldu',
            'message' => '"' . $listing->title . '" başlıklı ilanınızın süresi doldu. Yenilemek için düzenleyebilirsiniz.',
            'type' => Notification::TYPE_LISTING_EXPIRED,
            'target' => Notification::TARGET_USER,
            'user_id' => $listing->user_id,
            'related_type' => Listing::class,
            'related_id' => $listing->id,
            'action_links' => $actionLinks,
        ]);

        // Send email notification (queued)
        try {
            Mail::to($listing->user->email)->queue(new ListingExpiredMail($listing));
        } catch (\Exception $e) {
            \Log::error('Failed to send listing expired email', [
                'listing_id' => $listing->id,
                'error' => $e->getMessage()
            ]);
        }

        return $notification;
    }

    /**
     * Notify user that listing has been submitted for approval (site + email)
     * Used when user creates a new listing
     */
    public function notifyUserListingSubmitted(Listing $listing): Notification
    {
        $actionLinks = [
            [
                'title' => 'İlanlarım',
                'url' => route('user.listings.my'),
                'type' => 'primary'
            ]
        ];

        // Create site notification
        $notification = $this->create([
            'title' => __('notifications.listing_submitted_title'),
            'message' => __('notifications.listing_submitted_message', ['title' => $listing->title]),
            'type' => Notification::TYPE_NEW_LISTING,
            'target' => Notification::TARGET_USER,
            'user_id' => $listing->user_id,
            'related_type' => Listing::class,
            'related_id' => $listing->id,
            'action_links' => $actionLinks,
            'is_important' => true,
        ]);

        // Send email notification (queued)
        try {
            Mail::to($listing->user->email)->queue(new ListingSubmittedForApprovalMail($listing));
        } catch (\Exception $e) {
            \Log::error('Failed to send listing submitted email to user', [
                'listing_id' => $listing->id,
                'user_id' => $listing->user_id,
                'error' => $e->getMessage()
            ]);
        }

        return $notification;
    }

    /**
     * Notify user that listing has been resubmitted for approval after edit (site + email)
     */
    public function notifyUserListingResubmitted(Listing $listing): Notification
    {
        $actionLinks = [
            [
                'title' => 'İlanlarım',
                'url' => route('user.listings.my'),
                'type' => 'primary'
            ]
        ];

        // Create site notification
        $notification = $this->create([
            'title' => __('notifications.listing_resubmitted_title'),
            'message' => __('notifications.listing_resubmitted_message', ['title' => $listing->title]),
            'type' => Notification::TYPE_NEW_LISTING,
            'target' => Notification::TARGET_USER,
            'user_id' => $listing->user_id,
            'related_type' => Listing::class,
            'related_id' => $listing->id,
            'action_links' => $actionLinks,
            'is_important' => true,
        ]);

        // Send email notification (queued)
        try {
            Mail::to($listing->user->email)->queue(new ListingResubmittedForApprovalMail($listing));
        } catch (\Exception $e) {
            \Log::error('Failed to send listing resubmitted email to user', [
                'listing_id' => $listing->id,
                'user_id' => $listing->user_id,
                'error' => $e->getMessage()
            ]);
        }

        return $notification;
    }

    /**
     * Notify admin that listing has been edited and resubmitted (site + email)
     */
    public function notifyAdminListingResubmitted(Listing $listing): Notification
    {
        $actionLinks = [
            [
                'title' => 'İlanı İncele',
                'url' => route('admin.listings.show', $listing->id),
                'type' => 'primary'
            ],
            [
                'title' => 'İlanları Yönet',
                'url' => route('admin.listings.index', ['approval_status' => 'pending']),
                'type' => 'secondary'
            ]
        ];

        // Create site notification
        $notification = $this->create([
            'title' => __('notifications.listing_resubmitted_admin_title'),
            'message' => __('notifications.listing_resubmitted_admin_message', [
                'user' => $listing->user->name,
                'title' => $listing->title
            ]),
            'type' => Notification::TYPE_NEW_LISTING,
            'target' => Notification::TARGET_ADMIN,
            'related_type' => Listing::class,
            'related_id' => $listing->id,
            'action_links' => $actionLinks,
            'created_by' => $listing->user_id,
        ]);

        // Send email notification to admins (queued)
        try {
            // Bildirim adresi: önce notification_email, sonra contact_email.
            $adminEmail = trim((string) Setting::get('notification_email'))
                ?: (trim((string) Setting::get('contact_email'))
                    ?: config('mail.admin_email', config('mail.from.address')));
            Mail::to($adminEmail)->queue(new NewListingMail($listing));
        } catch (\Exception $e) {
            \Log::error('Failed to send listing resubmitted email to admin', [
                'listing_id' => $listing->id,
                'error' => $e->getMessage()
            ]);
        }

        return $notification;
    }

    /**
     * Send custom notification
     */
    public function notifyCustom(array $data): Notification
    {
        return $this->create(array_merge([
            'type' => Notification::TYPE_CUSTOM,
        ], $data));
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications($userId, $limit = 10)
    {
        return Notification::forUser($userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get admin notifications
     */
    public function getAdminNotifications($limit = 10)
    {
        return Notification::forAdmin()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get unread count for user
     */
    public function getUserUnreadCount($userId): int
    {
        return Notification::forUser($userId)->unread()->count();
    }

    /**
     * Get unread count for admin
     */
    public function getAdminUnreadCount(): int
    {
        return Notification::forAdmin()->unread()->count();
    }

    /**
     * Mark all notifications as read for user
     */
    public function markAllAsReadForUser($userId): void
    {
        Notification::forUser($userId)->unread()->update(['is_read' => true]);
    }

    /**
     * Mark all notifications as read for admin
     */
    public function markAllAsReadForAdmin(): void
    {
        Notification::forAdmin()->unread()->update(['is_read' => true]);
    }

    /**
     * Delete notification
     */
    public function delete($notificationId): bool
    {
        $notification = Notification::find($notificationId);
        if ($notification) {
            return $notification->delete();
        }
        return false;
    }

    /**
     * Bulk delete notifications
     */
    public function bulkDelete(array $notificationIds): int
    {
        return Notification::whereIn('id', $notificationIds)->delete();
    }
}
