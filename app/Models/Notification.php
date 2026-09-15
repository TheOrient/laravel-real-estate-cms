<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    protected $fillable = [
        'title',
        'message',
        'type',
        'target',
        'is_read',
        'is_important',
        'icon',
        'action_links',
        'related_type',
        'related_id',
        'user_id',
        'created_by',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_important' => 'boolean',
        'action_links' => 'array',
    ];

    // Notification types constants
    const TYPE_LISTING_APPROVED = 'listing_approved';
    const TYPE_LISTING_REJECTED = 'listing_rejected';
    const TYPE_LISTING_EXPIRED = 'listing_expired';
    const TYPE_LISTING_RENEWED = 'listing_renewed';
    const TYPE_NEW_LISTING = 'new_listing';
    const TYPE_NEW_REPORT = 'new_report';
    const TYPE_NEW_MESSAGE = 'new_message';
    const TYPE_ORDER_CREATED = 'order_created';
    const TYPE_ORDER_CONFIRMED = 'order_confirmed';
    const TYPE_ORDER_REJECTED = 'order_rejected';
    const TYPE_NEW_ORDER = 'new_order';
    const TYPE_CUSTOM = 'custom';

    // Target types
    const TARGET_USER = 'user';
    const TARGET_ADMIN = 'admin';

    /**
     * Get the user who will receive this notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who created this notification
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the related model (polymorphic relation)
     */
    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(): void
    {
        $this->update(['is_read' => false]);
    }

    /**
     * Scope to get unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope to get read notifications
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope to get user notifications
     */
    public function scopeForUser($query, $userId = null)
    {
        $userId = $userId ?? auth()->id();
        return $query->where('target', self::TARGET_USER)
                     ->where('user_id', $userId);
    }

    /**
     * Scope to get admin notifications
     */
    public function scopeForAdmin($query)
    {
        return $query->where('target', self::TARGET_ADMIN);
    }

    /**
     * Scope to get notifications by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get formatted time ago
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get default icon based on notification type
     */
    public function getDefaultIcon()
    {
        $icons = [
            self::TYPE_LISTING_APPROVED => 'ri-check-circle-line',
            self::TYPE_LISTING_REJECTED => 'ri-close-circle-line',
            self::TYPE_LISTING_EXPIRED => 'ri-time-line',
            self::TYPE_LISTING_RENEWED => 'ri-refresh-line',
            self::TYPE_NEW_LISTING => 'ri-add-circle-line',
            self::TYPE_NEW_REPORT => 'ri-alert-line',
            self::TYPE_NEW_MESSAGE => 'ri-message-3-line',
            self::TYPE_ORDER_CREATED => 'ri-shopping-cart-line',
            self::TYPE_ORDER_CONFIRMED => 'ri-checkbox-circle-line',
            self::TYPE_ORDER_REJECTED => 'ri-close-circle-line',
            self::TYPE_NEW_ORDER => 'ri-shopping-bag-line',
            self::TYPE_CUSTOM => 'ri-notification-3-line',
        ];

        return $this->icon ?? ($icons[$this->type] ?? 'ri-notification-3-line');
    }
}
