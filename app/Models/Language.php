<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Language extends Model
{
    use HasFactory;

    /** @var Collection<int, self>|null */
    protected static ?Collection $runtimeLanguages = null;

    protected $fillable = [
        'code',
        'title',
        'sort_order',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => static::clearRuntimeCache());
        static::deleted(fn () => static::clearRuntimeCache());
    }

    /**
     * Languages are read by nearly every translated accessor. Loading the
     * two-row table once per request prevents hundreds of duplicate queries.
     */
    public static function runtimeList(): Collection
    {
        return static::$runtimeLanguages ??= static::query()->get();
    }

    public static function cachedByCode(string $code): ?self
    {
        return static::runtimeList()->firstWhere('code', $code);
    }

    public static function defaultCached(): ?self
    {
        return static::runtimeList()->firstWhere('is_default', true)
            ?? static::runtimeList()->first();
    }

    public static function cachedCodeForId(?int $id): ?string
    {
        return $id ? static::runtimeList()->firstWhere('id', $id)?->code : null;
    }

    public static function clearRuntimeCache(): void
    {
        static::$runtimeLanguages = null;
    }

    /**
     * Scope: only active languages.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: apply default ordering.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    /**
     * Forms that need multilingual content (blog posts, pages, listing
     * descriptions) iterate over this list. Returns every active
     * language so TR/EN tabs both show up; for installs that
     * deliberately want a single-language admin UI, simply mark the
     * other language `is_active = 0`.
     */
    public static function defaultListForForms(): Collection
    {
        return static::runtimeList()
            ->where('is_active', true)
            ->sortBy(fn (self $language) => sprintf('%010d|%s', $language->sort_order, $language->title))
            ->values();
    }

    /**
     * URL that switches the current visitor to this locale and bounces
     * them back to the page they were on. The matching route is
     * `locale.switch` in routes/web.php.
     */
    public function getUrlAttribute(): string
    {
        try {
            return route('locale.switch', ['code' => $this->code]);
        } catch (\Throwable $e) {
            // During config:cache the routes may not be loaded yet.
            return '#';
        }
    }
}
