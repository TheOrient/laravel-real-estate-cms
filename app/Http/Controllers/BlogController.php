<?php

namespace App\Http\Controllers;

use App\Services\BlogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public blog routes: list + detail.
 *
 * Admin CRUD lives in App\Http\Controllers\Admin\BlogController.
 */
class BlogController extends Controller
{
    public function __construct(protected BlogService $blogs) {}

    /**
     * Paginated list of published blog posts.
     */
    public function index(Request $request): View
    {
        $posts = $this->blogs->paginatePublished(9);

        return view('blog.index', [
            'posts' => $posts,
        ]);
    }

    /**
     * Single blog post by localized slug.
     */
    public function show(string $slug): View
    {
        $post = $this->blogs->findPublishedBySlug($slug);

        abort_if(! $post, 404);

        // Best-effort view counter. Don't let a counter failure block
        // the response.
        try {
            $post->recordView();
        } catch (\Throwable $e) {
            // Silently ignore — analytics shouldn't break content delivery.
        }

        // Related posts — internal linking hem SEO (Google sayfaların
        // birbirine referans verdiğini görür → crawl depth artar) hem
        // engagement (bounce rate düşer) için kritik.
        try {
            $relatedPosts = \App\Models\Blog::published()
                ->where('id', '!=', $post->id)
                ->with('descriptions')
                ->orderByDesc('published_at')
                ->take(3)
                ->get();
        } catch (\Throwable $e) {
            $relatedPosts = collect();
        }

        return view('blog.show', [
            'post'         => $post,
            'relatedPosts' => $relatedPosts,
        ]);
    }
}
