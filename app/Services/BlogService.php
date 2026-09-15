<?php

namespace App\Services;

use App\Models\Blog;
use App\Models\BlogDescription;
use App\Models\Language;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * BlogService
 *
 * Centralizes all blog-related DB writes so admin controllers, the
 * future AI assistant, and any background job can call into one place.
 * Keep this thin: read-side queries should stay on the model where
 * scopes are already defined.
 */
class BlogService
{
    /**
     * Return the latest published posts, eager-loaded with the
     * description row for the current locale. Used by the home page
     * "blog" section and the public /blog index.
     */
    public function latestPublished(int $limit = 6): Collection
    {
        return Blog::latestPublished()
            ->with(['descriptions', 'author:id,first_name,last_name'])
            ->limit($limit)
            ->get();
    }

    public function paginatePublished(int $perPage = 9): LengthAwarePaginator
    {
        return Blog::latestPublished()
            ->with(['descriptions', 'author:id,first_name,last_name'])
            ->paginate($perPage);
    }

    /**
     * Look up a published post by its localized slug. Tries the
     * current locale first, then falls back to the default language
     * so an English visitor visiting a TR slug still lands on the post.
     */
    public function findPublishedBySlug(string $slug): ?Blog
    {
        $localeCode = app()->getLocale();

        $description = BlogDescription::query()
            ->where('slug', $slug)
            ->whereHas('language', function ($q) use ($localeCode) {
                $q->where('code', $localeCode);
            })
            ->first();

        // Fall back: search across all languages.
        if (! $description) {
            $description = BlogDescription::where('slug', $slug)->first();
        }

        if (! $description) {
            return null;
        }

        $post = Blog::published()
            ->with(['descriptions.language', 'author:id,first_name,last_name'])
            ->find($description->blog_id);

        return $post;
    }

    /**
     * Create a post + its per-language description rows in one txn.
     *
     * @param  array  $attributes          Top-level blog attributes (status, image, …)
     * @param  array  $descriptionsByLang  [language_id => [title, slug?, body, …]]
     */
    public function create(array $attributes, array $descriptionsByLang): Blog
    {
        return DB::transaction(function () use ($attributes, $descriptionsByLang) {
            // Auto-set published_at when transitioning to "published"
            // and no explicit value was provided.
            if (($attributes['status'] ?? null) === 'published'
                && empty($attributes['published_at'])) {
                $attributes['published_at'] = now();
            }

            $blog = Blog::create($attributes);
            $this->syncDescriptions($blog, $descriptionsByLang);

            return $blog;
        });
    }

    /**
     * Update existing post + descriptions.
     */
    public function update(Blog $blog, array $attributes, array $descriptionsByLang): Blog
    {
        return DB::transaction(function () use ($blog, $attributes, $descriptionsByLang) {
            // If the operator is publishing now and there's no
            // published_at yet, stamp it.
            if (($attributes['status'] ?? $blog->status) === 'published'
                && empty($attributes['published_at'])
                && empty($blog->published_at)) {
                $attributes['published_at'] = now();
            }

            $blog->update($attributes);
            $this->syncDescriptions($blog, $descriptionsByLang);

            return $blog->fresh(['descriptions']);
        });
    }

    /**
     * Upsert each language description and prune ones that were
     * cleared from the form. Slug auto-generated from title when
     * the operator didn't provide one, with collision suffixing.
     */
    protected function syncDescriptions(Blog $blog, array $descriptionsByLang): void
    {
        foreach ($descriptionsByLang as $languageId => $payload) {
            $title = trim((string) ($payload['title'] ?? ''));
            $slug  = trim((string) ($payload['slug'] ?? ''));

            // If a non-default language has no content, skip it
            // entirely rather than creating an empty row.
            if ($title === '' && $slug === '') {
                continue;
            }

            if ($slug === '' && $title !== '') {
                $slug = $this->uniqueSlug($title, (int) $languageId, $blog->id);
            }

            BlogDescription::updateOrCreate(
                [
                    'blog_id'     => $blog->id,
                    'language_id' => (int) $languageId,
                ],
                [
                    'title'            => $title,
                    'slug'             => $slug,
                    'excerpt'          => $payload['excerpt'] ?? null,
                    'body'             => $payload['body'] ?? null,
                    'meta_title'       => $payload['meta_title'] ?? null,
                    'meta_description' => $payload['meta_description'] ?? null,
                    'meta_keywords'    => $payload['meta_keywords'] ?? null,
                ]
            );
        }
    }

    /**
     * Generate a slug that doesn't collide with existing posts in the
     * same language. Appends -2, -3, … on conflicts.
     */
    public function uniqueSlug(string $source, int $languageId, ?int $ignoreBlogId = null): string
    {
        $base = Str::slug($source);
        if ($base === '') {
            $base = 'blog-' . now()->timestamp;
        }

        $slug = $base;
        $i = 2;

        while (
            BlogDescription::where('language_id', $languageId)
                ->where('slug', $slug)
                ->when($ignoreBlogId, fn ($q) => $q->where('blog_id', '!=', $ignoreBlogId))
                ->exists()
        ) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    public function delete(Blog $blog): void
    {
        $blog->delete();
    }
}
