@php
    /** @var \App\Models\Blog $post */
    $title   = \App\Helpers\BrandHelper::render($post->title);
    $excerpt = \App\Helpers\BrandHelper::render($post->excerpt);
    $url     = route('blog.show', $post->slug);
    $hasCover = (bool) $post->image;
    $cover   = $hasCover
        ? (str_starts_with($post->image, 'http') ? $post->image : route('image.resize', ['size' => 'blog_cover', 'fit' => 'crop', 'path' => $post->image]))
        : null;
    $date      = optional($post->published_at)->translatedFormat('d M Y') ?? $post->created_at->translatedFormat('d M Y');
@endphp

<article class="blog-card group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-1 flex flex-col">
    <a href="{{ $url }}" class="blog-card-cover block"
       style="{{ $hasCover ? 'background:#f3f4f6;' : 'background: linear-gradient(135deg, #1E6F5C 0%, #13493E 100%);' }}">
        @if($hasCover)
            <img src="{{ $cover }}" alt="{{ $title }}" loading="lazy" decoding="async">
        @else
            <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color: rgba(255,255,255,0.8);">
                <i class="ri-article-line" style="font-size: 4rem;"></i>
            </div>
        @endif
    </a>

    <div class="blog-card-body">
        <div class="blog-card-meta">
            <i class="ri-calendar-line"></i>
            <span>{{ $date }}</span>
        </div>

        <h3 class="blog-card-title group-hover:text-[#1E6F5C]" style="font-family: Inter, sans-serif;">
            <a href="{{ $url }}">{{ $title }}</a>
        </h3>

        @if($excerpt)
            <p class="blog-card-excerpt">{{ Str::limit($excerpt, 140) }}</p>
        @endif

        <div class="blog-card-foot" style="justify-content: flex-end;">
            <a href="{{ $url }}" style="display:inline-flex; align-items:center; gap:.25rem; font-size:.75rem; font-weight:700; color:#1E6F5C; transition: transform .25s;">
                {{ __('blog.read_more') }}
                <i class="ri-arrow-right-line"></i>
            </a>
        </div>
    </div>
</article>
