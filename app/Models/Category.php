<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Category extends Model
{
    protected bool $currentDescriptionResolved = false;
    protected ?CategoryDescription $currentDescriptionCache = null;

    protected $fillable = [
        'image',
        'icon',
        'parent_id',
        'is_active',
        'is_filterable',
        'listings_count'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'listings_count' => 'integer',
    ];

    // Get child categories
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->with('description');
    }

    // Get child categories with all nested children (recursive)
    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    // Get parent category
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Get listings in this category
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    // Get attributes associated with this category
    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class)
            ->withPivot('order', 'property_detail_position')
            ->withTimestamps();
    }

    // Get active listings count
    public function getActiveListingsCountAttribute(): int
    {
        return $this->listings()->active()->count();
    }

    /**
     * Check if the category is a leaf category (has no subcategories)
     */
    public function isLeaf(): bool
    {
        return $this->children()->count() === 0;
    }

    public function getNestedChildrenCategories()
    {
        return $this->children()->with('children');
    }

    /**
     * Get all attributes for this category, including those from parent categories
     */
    public function getAllAttributes($with = [])
    {
        $attributes = collect();

        // Add this category's attributes - use the relationship method instead of the property
        $query = $this->attributes();
        if (!empty($with)) {
            $query->with($with);
        }
        $attributes = $attributes->merge($query->get());

        // Add parent category attributes recursively
        $category = $this;
        while ($category->parent_id) {
            $category = $category->parent;
            if ($category) {
                $query = $category->attributes();
                if (!empty($with)) {
                    $query->with($with);
                }
                $attributes = $attributes->merge($query->get());
            }
        }

        return $attributes->unique('id')->sortBy('pivot.order');
    }

    /**
     * Get filterable attributes for this category, including those from parent categories
     */
    public function getFilterableAttributes()
    {
        return $this->getAllAttributes()->where('is_filterable', true);
    }

    /**
     * Get all descriptions for this category
     */
    public function descriptions(): HasMany
    {
        return $this->hasMany(CategoryDescription::class);
    }

    /**
     * Get description for a specific language
     */
    public function getDescriptionForLanguage($languageId = null)
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
     * Get description for current locale with multi-level fallback:
     *  1. Try active locale (e.g. EN)
     *  2. Fall back to default language row (usually TR)
     *  3. Fall back to whatever description exists
     *
     * Falling back guarantees the accessors never return null just
     * because an admin hasn't translated a category yet.
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

        $default = Language::defaultCached();
        if ($default) {
            $desc = $this->getDescriptionForLanguage($default->id);
            if ($desc) return $this->rememberCurrentDescription($desc);
        }

        return $this->rememberCurrentDescription($this->descriptions->first());
    }

    protected function rememberCurrentDescription(?CategoryDescription $description): ?CategoryDescription
    {
        $this->currentDescriptionResolved = true;
        return $this->currentDescriptionCache = $description;
    }

    /**
     * Get current description relation
     *
     */
    public function description(): HasOne
    {
        $currentLanguage = session('current_language');
        $languageId = $currentLanguage?->id;

        if (!$languageId) {
            $languageId = Language::cachedByCode(app()->getLocale())?->id
                ?? Language::defaultCached()?->id;
        }
        return $this->hasOne(CategoryDescription::class)
            ->when($languageId, function ($query) use ($languageId) {
                $query->where('language_id', $languageId);
            });
    }

    /**
     * Get category name. Falls back through locale tiers, then runs
     * AutoTranslator if the resolved row is still in the source locale
     * (typical case: only a TR row exists, visitor is on EN).
     */
    public function getNameAttribute()
    {
        $description = $this->getDescriptionForLanguage(Language::defaultCached()?->id)
            ?? $this->getCurrentDescription();
        if (! $description) {
            return 'Category #' . $this->id;
        }
        return $this->maybeAutoTranslate((string) $description->name);
    }

    /**
     * Slug stays in the source language (TR) — slugs are stable URLs
     * and must NOT auto-translate per project rules.
     */
    public function getSlugAttribute()
    {
        $description = $this->getCurrentDescription();
        if ($description && $description->slug) {
            return $description->slug;
        }
        // Final fallback — any description's slug, then synthetic id.
        $any = $this->descriptions->first(fn ($item) => filled($item->slug));
        return $any?->slug ?: 'category-' . $this->id;
    }

    public function getShortDescriptionAttribute()
    {
        $description = $this->getCurrentDescription();
        return $description
            ? $this->maybeAutoTranslate((string) ($description->short_description ?? ''))
            : null;
    }

    public function getDescriptionAttribute()
    {
        $description = $this->getCurrentDescription();
        return $description
            ? $this->maybeAutoTranslate((string) ($description->description ?? ''))
            : null;
    }

    /**
     * If the resolved description row is in the source language but the
     * visitor's locale is different, push the value through the cached
     * AutoTranslator. Safe — falls back to original on failure.
     */
    protected function maybeAutoTranslate(string $value): string
    {
        if ($value === '') return $value;
        if (! app()->bound('app') || app()->getLocale() === 'tr') return $value;
        try {
            return \App\Services\AutoTranslator::auto($value);
        } catch (\Throwable $e) {
            return $value;
        }
    }
}
