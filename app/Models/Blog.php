<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Blog post.
 *
 * The language-neutral container — author, image, status flags, view
 * count. All human-readable text lives in BlogDescription.
 *
 * Public-side conventions match the Page model so the rest of the
 * codebase (sitemap, SEO helpers, BrandHelper) can treat blog posts
 * the same way it treats pages.
 */
class Blog extends Model
{
    use SoftDeletes;

    protected bool $currentDescriptionResolved = false;
    protected ?BlogDescription $currentDescriptionCache = null;

    protected $fillable = [
        'user_id',
        'image',
        'status',
        'is_active',
        'is_featured',
        'view_count',
        'published_at',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'is_featured'  => 'boolean',
        'view_count'   => 'integer',
        'published_at' => 'datetime',
    ];

    /* ---------------- Relations ---------------- */

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function descriptions(): HasMany
    {
        return $this->hasMany(BlogDescription::class);
    }

    /**
     * Description filtered by the current session language. Used by
     * Eloquent eager loading; falls back to the default language when
     * no language is set on the session.
     */
    public function description()
    {
        $currentLanguage = session('current_language');
        $languageId = $currentLanguage?->id
            ?? Language::cachedByCode(app()->getLocale())?->id
            ?? Language::defaultCached()?->id;

        return $this->hasOne(BlogDescription::class)
            ->when($languageId, fn ($q) => $q->where('language_id', $languageId));
    }

    /* ---------------- Scopes ---------------- */

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->where('is_active', true)
                     ->where(function ($q) {
                         $q->whereNull('published_at')
                           ->orWhere('published_at', '<=', now());
                     });
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeLatestPublished($query)
    {
        return $query->published()->orderByDesc('published_at')->orderByDesc('id');
    }

    /* ---------------- Per-locale resolvers ---------------- */

    /**
     * Fetch the description row for a given language (or default).
     */
    public function getDescriptionForLanguage(?int $languageId = null): ?BlogDescription
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
     * Description for the current request locale, with sensible
     * fallback to the default language so the post never 404s just
     * because a translation is missing.
     */
    public function getCurrentDescription(): ?BlogDescription
    {
        if ($this->currentDescriptionResolved) {
            return $this->currentDescriptionCache;
        }

        $language = Language::cachedByCode(app()->getLocale());

        if ($language) {
            $desc = $this->getDescriptionForLanguage($language->id);
            if ($desc) return $this->rememberCurrentDescription($desc);
        }

        // Fall back to default language so we always show something.
        $default = Language::defaultCached();
        if ($default) {
            $desc = $this->getDescriptionForLanguage($default->id);
            if ($desc) return $this->rememberCurrentDescription($desc);
        }

        // Last resort: any description row that exists for this blog.
        return $this->rememberCurrentDescription($this->descriptions->first());
    }

    protected function rememberCurrentDescription(?BlogDescription $description): ?BlogDescription
    {
        $this->currentDescriptionResolved = true;
        return $this->currentDescriptionCache = $description;
    }

    /* ---------------- Accessors (proxy to current description) ---------------- */

    public function getCurrentDescriptionAttribute(): ?BlogDescription
    {
        return $this->getCurrentDescription();
    }

    public function getTitleAttribute(): ?string
    {
        return $this->maybeAutoTranslate($this->getCurrentDescription()?->title, 'plain');
    }

    public function getSlugAttribute(): string
    {
        // Slugs stay deterministic — never auto-translate.
        $desc = $this->getDescriptionForLanguage(Language::defaultCached()?->id)
            ?? $this->getCurrentDescription();
        return $desc?->slug ?: Str::slug((string) ($desc?->title ?? 'blog-' . $this->id));
    }

    public function getExcerptAttribute(): ?string
    {
        return $this->maybeAutoTranslate($this->getCurrentDescription()?->excerpt, 'plain');
    }

    public function getBodyAttribute(): ?string
    {
        return $this->maybeAutoTranslate($this->getCurrentDescription()?->body, 'html');
    }

    public function getMetaTitleAttribute(): ?string
    {
        $desc = $this->getCurrentDescription();
        return $this->maybeAutoTranslate($desc?->meta_title ?: $desc?->title, 'plain');
    }

    public function getMetaDescriptionAttribute(): ?string
    {
        $desc = $this->getCurrentDescription();
        return $this->maybeAutoTranslate($desc?->meta_description ?: $desc?->excerpt, 'plain');
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

    public function getMetaKeywordsAttribute(): ?string
    {
        return $this->getCurrentDescription()?->meta_keywords;
    }

    /**
     * Public-facing URL for this post.
     */
    public function getUrlAttribute(): string
    {
        return route('blog.show', $this->slug);
    }

    /**
     * Increment view counter without bumping updated_at.
     *
     * Uses a raw SQL increment so concurrent requests don't lose
     * counts to read-modify-write races, and so the listing order
     * ("latest published" relies on updated_at) isn't perturbed by
     * read traffic.
     */
    public function recordView(): void
    {
        // Direct DB::table() so timestamps aren't auto-touched and
        // concurrent reads can't race the counter.
        DB::table($this->getTable())
            ->where('id', $this->getKey())
            ->update(['view_count' => DB::raw('view_count + 1')]);
    }
}
