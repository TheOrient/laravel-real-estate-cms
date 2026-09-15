<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    protected bool $currentDescriptionResolved = false;
    protected ?PageDescription $currentDescriptionCache = null;

    protected $fillable = [
        'title',
        'is_active',
        'sort_order',
        'show_header',
        'show_footer'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'show_header' => 'boolean',
        'show_footer' => 'boolean'
    ];

    /**
     * Automatically generate slug from title if not provided
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });

        static::updating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });
    }

    /**
     * Scope for active pages only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered pages
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('title', 'asc');
    }

    /**
     * Page descriptions by language
     */
    public function descriptions(): HasMany
    {
        return $this->hasMany(PageDescription::class);
    }

    /**
     * Description relation filtered by current session language (fallback default)
     */
    public function description()
    {
        $currentLanguage = session('current_language');
        $languageId = $currentLanguage?->id;

        if (!$languageId) {
            $languageId = Language::cachedByCode(app()->getLocale())?->id
                ?? Language::defaultCached()?->id;
        }

        return $this->hasOne(PageDescription::class)
            ->when($languageId, function ($query) use ($languageId) {
                $query->where('language_id', $languageId);
            });
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
     * Get description for current locale with multi-level fallback:
     *   1. Active locale (e.g. EN if visitor switched)
     *   2. DB-default language (TR)
     *   3. ANY description row — so the page never renders empty
     *      just because the EN row hasn't been created yet.
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

        // Last resort: any description row that exists for this page.
        return $this->rememberCurrentDescription($this->descriptions->first());
    }

    protected function rememberCurrentDescription(?PageDescription $description): ?PageDescription
    {
        $this->currentDescriptionResolved = true;
        return $this->currentDescriptionCache = $description;
    }

    /**
     * Accessor: current description model
     */
    public function getCurrentDescriptionAttribute()
    {
        return $this->getCurrentDescription();
    }

    /**
     * Accessor: translated slug (falls back to placeholder)
     */
    public function getSlugAttribute()
    {
        $description = $this->getDescriptionForLanguage(Language::defaultCached()?->id)
            ?? $this->getCurrentDescription();
        return $description
            ? $description->slug
            : Str::slug($this->attributes['title'] ?? '');
    }

    /**
     * Accessor: page title — auto-translated when the visitor's locale
     * differs from the source language (TR by default). Used by the
     * footer's "Corporate / Support" link lists and the page header.
     */
    public function getTitleAttribute()
    {
        $description = $this->getCurrentDescription();
        $title = $description?->title ?? $this->attributes['title'] ?? '';
        return $this->maybeAutoTranslate($title, 'plain');
    }

    /**
     * Accessor: translated description text
     */
    public function getMetaDescriptionAttribute()
    {
        $description = $this->getCurrentDescription();
        return $this->maybeAutoTranslate($description?->meta_description, 'plain');
    }

    /**
     * Accessor: translated content
     */
    public function getContentAttribute()
    {
        $description = $this->getCurrentDescription();
        return $this->maybeAutoTranslate($description?->content, 'html');
    }

    /**
     * Auto-translate a string when the visitor's locale differs from
     * the source language the description row was stored in.
     */
    protected function maybeAutoTranslate(?string $text, string $mode): ?string
    {
        if ($text === null || $text === '') return $text;
        $desc = $this->getCurrentDescription();
        $descLocale = Language::cachedCodeForId($desc?->language_id) ?? 'tr';
        $current = app()->getLocale();
        if ($current === $descLocale) return $text;
        return \App\Services\AutoTranslator::translate($text, $descLocale, $current, $mode);
    }

    /**
     * Get the URL for this page (tek dil — slug)
     */
    public function getUrlAttribute()
    {
        return route('pages.show', $this->slug);
    }
}
