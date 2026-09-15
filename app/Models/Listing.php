<?php

namespace App\Models;

use App\Models\ListingDescription;
use App\Models\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Listing extends Model
{
    use SoftDeletes;

    protected bool $currentDescriptionResolved = false;
    protected ?ListingDescription $currentDescriptionCache = null;

    protected $fillable = [
        'price',
        'category_id',
        'user_id',
        'city_id',
        'district_id',
        'neighborhood_id',
        'latitude',
        'longitude',
        'status',
        'is_featured',
        'expires_at',
        'view_count',
        'image',
        'is_active',
        'is_approved',
        'is_sent_to_approver',
        'sent_to_approver_at',
        'approved_at',
        'allow_whatsapp',
        'video_url',
        'video_file',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_featured' => 'boolean',
        'expires_at' => 'datetime',
        'view_count' => 'integer',
        'is_active' => 'boolean',
        'is_approved' => 'boolean',
        'is_sent_to_approver' => 'boolean',
        'sent_to_approver_at' => 'datetime',
        'approved_at' => 'datetime',
        'allow_whatsapp' => 'boolean',
    ];

    /**
     * Set latitude attribute - convert empty strings to null
     */
    public function setLatitudeAttribute($value)
    {
        $this->attributes['latitude'] = ($value === '' || $value === null) ? null : $value;
    }

    /**
     * Set longitude attribute - convert empty strings to null
     */
    public function setLongitudeAttribute($value)
    {
        $this->attributes['longitude'] = ($value === '' || $value === null) ? null : $value;
    }

    protected static function boot()
    {
        parent::boot();
    }

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function neighborhood(): BelongsTo
    {
        return $this->belongsTo(Neighborhood::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ListingImage::class)->orderBy('sort_order');
    }

    public function activeImages(): HasMany
    {
        return $this->hasMany(ListingImage::class)
            ->where('is_active', 1)
            ->orderBy('sort_order');
    }

    /**
     * Translation relationship (all languages)
     */
    public function descriptions(): HasMany
    {
        return $this->hasMany(ListingDescription::class);
    }

    /**
     * Translation for current session language (fallback default)
     */
    public function description(): HasOne
    {
        $currentLanguage = session('current_language');
        $languageId = $currentLanguage?->id;

        if (!$languageId) {
            $languageId = Language::cachedByCode(app()->getLocale())?->id
                ?? Language::defaultCached()?->id;
        }

        return $this->hasOne(ListingDescription::class)
            ->when($languageId, function ($query) use ($languageId) {
                $query->where('language_id', $languageId);
            });
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'attribute_value_listing')
                    ->withTimestamps();
    }

    public function customAttributeValues(): HasMany
    {
        return $this->hasMany(ListingAttributeCustomValue::class);
    }

    /**
     * Get description for a specific language (falls back to default)
     */
    public function getDescriptionForLanguage(?int $languageId = null)
    {
        if ($languageId === null) {
            $languageId = Language::defaultCached()?->id;
        }

        if (! $this->relationLoaded('descriptions')) {
            $this->setRelation('descriptions', $this->descriptions()->get());
        }

        return $this->descriptions->firstWhere('language_id', $languageId);
    }

    /**
     * Get description for current locale with fallback to default.
     *
     * Resolution order:
     *   1. Description in the active locale (e.g. EN if visitor switched)
     *   2. Description in the DB-default language (TR)
     *   3. ANY description row — last-resort so a listing always
     *      has a title/slug even when only one translation exists.
     */
    public function getCurrentDescription()
    {
        if ($this->currentDescriptionResolved) {
            return $this->currentDescriptionCache;
        }

        $language = Language::cachedByCode(app()->getLocale());

        if ($language) {
            $desc = $this->getDescriptionForLanguage($language->id);
            if ($desc) return $this->rememberCurrentDescription($desc);
        }

        // Fall back to the configured default language.
        $default = Language::defaultCached();
        if ($default) {
            $desc = $this->getDescriptionForLanguage($default->id);
            if ($desc) return $this->rememberCurrentDescription($desc);
        }

        // Last resort: return any row that exists for this listing.
        return $this->rememberCurrentDescription($this->descriptions->first());
    }

    protected function rememberCurrentDescription(?ListingDescription $description): ?ListingDescription
    {
        $this->currentDescriptionResolved = true;
        return $this->currentDescriptionCache = $description;
    }

    public function getTitleAttribute()
    {
        $desc = $this->getCurrentDescription();
        $title = $desc?->title ?? $this->attributes['title'] ?? null;
        // Auto-translate when the description is from the source
        // language but the visitor is on EN. Cached for 24h.
        return $this->maybeAutoTranslate($title, 'plain');
    }

    public function getSlugAttribute()
    {
        // Slugs MUST stay deterministic — never translate.
        $desc = $this->getDescriptionForLanguage(Language::defaultCached()?->id)
            ?? $this->getCurrentDescription();
        return $desc?->slug ?? $this->attributes['slug'] ?? ('listing-' . $this->id);
    }

    public function getDescriptionAttribute()
    {
        $desc = $this->getCurrentDescription();
        $body = $desc?->description ?? $this->attributes['description'] ?? null;
        return $this->maybeAutoTranslate($body, 'plain');
    }

    /**
     * Pass the string through AutoTranslator when the visitor's locale
     * differs from the source the description was stored in.
     */
    protected function maybeAutoTranslate(?string $text, string $mode): ?string
    {
        if ($text === null || $text === '') return $text;
        $desc = $this->getCurrentDescription();
        $descLocaleCode = Language::cachedCodeForId($desc?->language_id) ?? 'tr';
        $current = app()->getLocale();
        if ($current === $descLocaleCode) return $text;
        return \App\Services\AutoTranslator::translate($text, $descLocaleCode, $current, $mode);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_active', true)->where('is_approved', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeUrgent($query)
    {
        return $query->where('is_urgent', true);
    }
}
