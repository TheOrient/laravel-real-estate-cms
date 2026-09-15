<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'read_at',
        'admin_notes',
        'ip_address'
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Scope for new messages only
     */
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    /**
     * Scope for read messages
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope for unread messages
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Mark message as read
     */
    public function markAsRead()
    {
        $this->update([
            'read_at' => now(),
            'status' => $this->status === 'new' ? 'read' : $this->status
        ]);
    }

    /**
     * Get status badge class for admin
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'new' => 'badge-primary',
            'read' => 'badge-info',
            'replied' => 'badge-success',
            'archived' => 'badge-secondary',
            default => 'badge-secondary'
        };
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute()
    {
        return match($this->status) {
            'new' => 'Yeni',
            'read' => 'Okundu',
            'replied' => 'Yanıtlandı',
            'archived' => 'Arşivlendi',
            default => 'Bilinmiyor'
        };
    }

    /**
     * Check if message is new
     */
    public function isNew()
    {
        return $this->status === 'new';
    }
}
