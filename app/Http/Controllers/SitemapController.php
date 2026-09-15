<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Page;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class SitemapController extends Controller
{
    /**
     * Sitemap index — lists all sub-sitemaps. Search engines fetch this
     * first and then walk the URLs listed inside.
     */
    public function index(): Response
    {
        $sitemaps = [
            ['loc' => route('sitemap.pages'),      'lastmod' => now()->toW3cString()],
            ['loc' => route('sitemap.categories'), 'lastmod' => now()->toW3cString()],
            ['loc' => route('sitemap.listings'),   'lastmod' => now()->toW3cString()],
        ];

        // Blog sub-sitemap is only advertised when the table exists
        // (avoids 404s on installs that haven't migrated yet).
        if (Schema::hasTable('blogs')) {
            $sitemaps[] = ['loc' => route('sitemap.blogs'), 'lastmod' => now()->toW3cString()];
        }

        return $this->xml('sitemap.index', compact('sitemaps'));
    }

    /**
     * Static pages: home, about, contact, FAQ, blog index, plus any
     * admin-created CMS page that has a published default-language
     * description.
     */
    public function pages(): Response
    {
        $urls = [
            [
                'loc'        => route('home'),
                'lastmod'    => now()->toW3cString(),
                'changefreq' => 'daily',
                'priority'   => '1.0',
            ],
            [
                'loc'        => route('categories.show.all'),
                'lastmod'    => optional(Listing::where('is_active', true)->latest('updated_at')->first(['updated_at']))->updated_at?->toW3cString()
                    ?? now()->toW3cString(),
                'changefreq' => 'daily',
                'priority'   => '0.9',
            ],
            [
                'loc'        => route('faq.index'),
                'lastmod'    => now()->toW3cString(),
                'changefreq' => 'weekly',
                'priority'   => '0.5',
            ],
        ];

        // Blog index — only when the blog feature is migrated.
        if (Schema::hasTable('blogs')) {
            $urls[] = [
                'loc'        => route('blog.index'),
                'lastmod'    => now()->toW3cString(),
                'changefreq' => 'daily',
                'priority'   => '0.8',
            ];
        }

        // CMS pages (about, contact, privacy, …).
        if (Schema::hasTable('pages')) {
            $pages = Page::with('descriptions')->where('is_active', true)->get();
            foreach ($pages as $page) {
                $desc = $page->descriptions->first();
                if (! $desc?->slug) continue;
                $urls[] = [
                    'loc'        => route('pages.show', $desc->slug),
                    'lastmod'    => $page->updated_at?->toW3cString() ?? now()->toW3cString(),
                    'changefreq' => 'monthly',
                    'priority'   => '0.5',
                ];
            }
        }

        return $this->xml('sitemap.urlset', compact('urls'));
    }

    public function categories(): Response
    {
        $urls = [];

        if (Schema::hasTable('categories')) {
            // Only submit category pages that can lead to a published
            // listing. Empty taxonomy pages remain accessible but noindex.
            $allCategories = Category::where('is_active', true)
                ->with('descriptions')
                ->get()
                ->keyBy('id');

            $includedIds = collect();
            $listingCategoryIds = Listing::query()
                ->where('status', 'active')
                ->where('is_approved', true)
                ->where('is_active', true)
                ->pluck('category_id')
                ->filter()
                ->unique();

            foreach ($listingCategoryIds as $categoryId) {
                $current = $allCategories->get($categoryId);
                while ($current) {
                    $includedIds->push($current->id);
                    $current = $current->parent_id ? $allCategories->get($current->parent_id) : null;
                }
            }

            $categories = $allCategories->only($includedIds->unique()->all())->values();

            foreach ($categories as $category) {
                $slug = $category->slug;
                if (! $slug) continue;
                try {
                    $loc = route('categories.show', $slug);
                } catch (\Throwable $e) {
                    continue;
                }
                $urls[] = [
                    'loc'        => $loc,
                    'lastmod'    => ($category->updated_at ?? now())->toW3cString(),
                    'changefreq' => 'weekly',
                    'priority'   => '0.8',
                ];
            }
        }

        return $this->xml('sitemap.urlset', compact('urls'));
    }

    public function listings(): Response
    {
        $urls = [];

        // Eager-load images: sitemap Image extension her mülkün TÜM
        // fotoğraflarını Google'a bildirir → İlan görselleri Google
        // Images'ta indekslenip ekstra trafik kaynağı olur.
        $listings = Listing::with('images')
            ->where('status', 'active')
            ->where('is_approved', true)
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->limit(50000)   // Sitemap protocol limit
            ->get();

        foreach ($listings as $listing) {
            // Görsel havuzunu topla — kapak + gallery (aynı URL varsa
            // dedupe). Sitemap Image protokolü URL başına 1000'e
            // kadar image kabul eder ama pratikte < 20 tutmak yeter.
            $images = [];
            if ($listing->image) {
                $images[] = [
                    'url'     => listing_image_url($listing->image, 'large'),
                    'title'   => $listing->title,
                    'caption' => \App\Helpers\SeoHelper::generateMetaDescription($listing->description ?? '', 100),
                ];
            }
            foreach ($listing->images->take(15) as $img) {
                $u = str_starts_with($img->image, 'http') ? $img->image : listing_image_url($img->image, 'large');
                $images[] = ['url' => $u, 'title' => $listing->title];
            }
            // Aynı URL'i tekrar etme
            $images = collect($images)->unique('url')->values()->all();

            $urls[] = [
                'loc'        => route('listings.show', $listing->slug),
                'lastmod'    => $listing->updated_at->toW3cString(),
                'changefreq' => 'daily',
                'priority'   => '0.9',
                'images'     => $images,   // yeni: çoklu image dizisi
                'image'      => $images[0]['url'] ?? null, // geriye dönük
                'title'      => $listing->title,
                'caption'    => \App\Helpers\SeoHelper::generateMetaDescription($listing->description ?? '', 100),
            ];
        }

        return $this->xml('sitemap.urlset', compact('urls'));
    }

    /**
     * Blog posts sitemap — only published, non-soft-deleted posts.
     */
    public function blogs(): Response
    {
        $urls = [];

        if (! Schema::hasTable('blogs')) {
            return $this->xml('sitemap.urlset', ['urls' => $urls]);
        }

        $posts = Blog::published()
            ->with(['descriptions'])
            ->orderByDesc('published_at')
            ->limit(50000)
            ->get();

        foreach ($posts as $post) {
            $desc = $post->description ?? $post->descriptions->first();
            if (! $desc?->slug) continue;

            $urls[] = [
                'loc'        => route('blog.show', $desc->slug),
                'lastmod'    => ($post->published_at ?? $post->updated_at)->toW3cString(),
                'changefreq' => 'monthly',
                'priority'   => '0.7',
                'image'      => $post->image ? asset($post->image) : null,
                'title'      => $desc->title,
                'caption'    => \App\Helpers\SeoHelper::generateMetaDescription($desc->excerpt ?? $desc->body ?? '', 100),
            ];
        }

        return $this->xml('sitemap.urlset', compact('urls'));
    }

    /**
     * Dynamic robots.txt. Reflects the current host so the Sitemap:
     * line is correct regardless of deployment domain.
     */
    public function robots(): Response
    {
        $sitemap = route('sitemap.index');
        $extra = trim((string) (get_setting('seo_robots_extra') ?? ''));

        $body = "User-agent: *\n"
              . "Disallow: /admin\n"
              . "Disallow: /panel\n"
              . "Disallow: /password/\n"
              . "Disallow: /email/verify\n"
              . "Disallow: /login\n"
              . "Disallow: /documentation/\n"
              . "Disallow: /feed/\n"
              . "Allow: /\n\n"
              . "Sitemap: {$sitemap}\n";

        if ($extra !== '') {
            $body .= "\n" . $extra . "\n";
        }

        return response($body, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    protected function xml(string $view, array $data): Response
    {
        $xml = view($view, $data)->render();
        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
