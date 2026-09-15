<?php

namespace App\Models;

use App\Constants\UserRolesConstant;
use App\Helpers\PhoneHelper;
use App\Notifications\CustomResetPassword;
use App\Notifications\CustomVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'email_verified_at',
        'password',
        'phone',
        'enable_whatsapp',
        'avatar',
        'bio',
        'user_role',
        'is_active',
        'version',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Model booted hook.
     *
     * Kullanıcı üzerinde kritik bir değişiklik (şifre, email, rol, aktiflik vb.)
     * olduğunda version alanını 1 artırır. Böylece VersionCheck middleware
     * aktif oturumları zorunlu olarak sonlandırabilir.
     */
    protected static function booted(): void
    {
        static::updating(function (User $user) {
            // Sadece kritik alanlar değiştiğinde versiyon artır
            if ($user->isDirty(['password', 'email', 'user_role', 'is_active'])) {
                $user->version = ($user->version ?? 1) + 1;
            }
        });
    }

    /**
     * Set phone attribute - automatically sanitize
     */
    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = PhoneHelper::sanitize($value);
    }

    /**
     * Get formatted phone number for display
     */
    public function getFormattedPhoneAttribute(): ?string
    {
        return PhoneHelper::format($this->phone);
    }

    /**
     * Get user's full name
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    /**
     * Get user's name (alias for full_name for backwards compatibility)
     */
    public function getNameAttribute(): string
    {
        return $this->getFullNameAttribute();
    }

    /**
     * Get user's initials
     */
    public function getInitialsAttribute(): string
    {
        $firstInitial = mb_substr($this->first_name, 0, 1);
        $lastInitial = mb_substr($this->last_name, 0, 1);

        return mb_strtoupper($firstInitial.$lastInitial);
    }

    /**
     * Base avatar path (relative to public/uploads)
     */
    protected function getBaseAvatarPath(): string
    {
        if (! empty($this->avatar)) {
            if (str_starts_with($this->avatar, 'uploads/')) {
                return $this->avatar;
            }

            return 'uploads/user/'.ltrim($this->avatar, '/');
        }

        return 'images/demo-avatar.svg';
    }

    /**
     * Get user's avatar URL (medium, default)
     */
    public function getAvatarUrlAttribute(): string
    {
        return user_avatar_url($this->getBaseAvatarPath(), 'avatar_medium', 'crop');
    }

    /**
     * Small avatar URL (list cards)
     */
    public function getAvatarSmallUrlAttribute(): string
    {
        return user_avatar_url($this->getBaseAvatarPath(), 'avatar_small', 'crop');
    }

    /**
     * Large avatar URL (profile pages, big circles)
     */
    public function getAvatarLargeUrlAttribute(): string
    {
        return user_avatar_url($this->getBaseAvatarPath(), 'avatar_large', 'crop');
    }

    /**
     * User's listings
     */
    public function listings()
    {
        return $this->hasMany(Listing::class);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->user_role === UserRolesConstant::ADMIN;
    }

    /**
     * Internal portfolio consultant. Agent accounts are created only by an
     * administrator; there is no public registration or marketplace flow.
     */
    public function isAgent(): bool
    {
        return $this->user_role === UserRolesConstant::AGENT;
    }

    /**
     * Check if user is active
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * Increment version when password changes
     */
    public function incrementVersion()
    {
        $this->version++;
        $this->save();
    }

    /**
     * Send custom email verification notification
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail);
    }

    /**
     * Send custom password reset notification
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPassword($token));
    }
}
