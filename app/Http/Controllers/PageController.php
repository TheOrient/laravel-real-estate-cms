<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageDescription;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Display the specified page by slug.
     *
     * Slugs are stored on the TR (default) description row. Visitors on
     * EN still hit the same TR slug (Page::getSlugAttribute returns the
     * TR slug for stability) — so the lookup ignores language and just
     * resolves any description row with that slug.
     */
    public function show(string $slug)
    {
        // ── Path 1: a real description row exists with this slug
        $description = PageDescription::where('slug', $slug)->first();
        $page = $description
            ? Page::with('descriptions')->find($description->page_id)
            : null;

        // ── Path 2: no description row — match the footer's
        // Str::slug(title) fallback so pages still open even when
        // someone forgot to create the description row.
        if (! $page) {
            foreach (Page::with('descriptions')->where('is_active', true)->get() as $candidate) {
                $candidateTitle = $candidate->descriptions->first()?->title
                    ?? $candidate->getAttribute('title');
                if ($candidateTitle && \Illuminate\Support\Str::slug($candidateTitle) === $slug) {
                    $page = $candidate;
                    break;
                }
            }
        }

        if (! $page || ! $page->is_active) {
            abort(404);
        }

        $resolvedSlug = $description?->slug ?? $slug;

        // Special handling for contact page — recognised by the
        // canonical TR/EN slug variants.
        if (in_array($resolvedSlug, ['iletisim', 'contact'], true)) {
            return view('pages.contact', compact('page'));
        }

        return view('pages.show', compact('page'));
    }
}
