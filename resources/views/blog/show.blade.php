@php
    /** @var \App\Models\Blog $post */
    $title    = \App\Helpers\BrandHelper::render($post->title ?? '');
    $body     = \App\Helpers\BrandHelper::render($post->body ?? '');
    $excerpt  = \App\Helpers\BrandHelper::render($post->excerpt ?? '');
    $metaDesc = \App\Helpers\BrandHelper::render($post->meta_description ?? $excerpt ?? $title);
    // Support both local Glide-managed images and external URLs.
    $coverUrl = $post->image
        ? (str_starts_with($post->image, 'http') ? $post->image : route('image.resize', ['size' => 'blog_cover', 'fit' => 'crop', 'path' => $post->image]))
        : null;
    $pageUrl   = url()->current();
    $shareText = rawurlencode($title);
    $shareUrl  = rawurlencode($pageUrl);
@endphp

@extends('layouts.app')

@section('title', \App\Helpers\BrandHelper::render($post->meta_title ?: $title))
@section('meta_description', $metaDesc)
@if($post->meta_keywords)
    @section('meta_keywords', \App\Helpers\BrandHelper::render($post->meta_keywords))
@endif
@if($coverUrl)
    @section('og_image', $coverUrl)
    @section('twitter_image', $coverUrl)
@endif
@section('og_type', 'article')

{{-- Article-specific OG properties: Facebook + LinkedIn newsfeed'de
     yayın tarihi ve son güncellenme okunaklı biçimde gösterilir. --}}
@if($post->published_at)
    @section('article_published_time', $post->published_at->toIso8601String())
@endif
@if($post->updated_at)
    @section('article_modified_time', $post->updated_at->toIso8601String())
@endif
@section('article_section', 'Kuşadası Gayrimenkul Rehberi')
@if($coverUrl)
    @section('og_image_alt', $title)
@endif

@push('jsonld')
@php
    // wordCount + timeRequired — Google Discover ve Bing kartlarda
    // "5 dk okuma" ibaresi olarak gösterir, engagement artırır.
    $readingStats = \App\Helpers\SeoHelper::computeReadingStats((string) $body);
    $brandOrg = [
        '@type' => 'Organization',
        'name'  => trim((string) get_setting('site_title', 'Real Estate CMS Demo')) ?: 'Real Estate CMS Demo',
        'url'   => url('/'),
    ];
@endphp
<script type="application/ld+json">
{!! json_encode([
    '@context'         => 'https://schema.org',
    '@type'            => 'BlogPosting',
    'headline'         => $title,
    'description'      => $metaDesc,
    'image'            => $coverUrl,
    'author'           => $brandOrg,
    'publisher'        => $brandOrg,
    'datePublished'    => optional($post->published_at)->toIso8601String(),
    'dateModified'     => $post->updated_at->toIso8601String(),
    'mainEntityOfPage' => $pageUrl,
    'wordCount'        => $readingStats['wordCount'],
    'timeRequired'     => $readingStats['timeRequired'],
    'inLanguage'       => app()->getLocale(),
    'articleSection'   => 'Kuşadası Gayrimenkul Rehberi',
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
<div class="bg-[#F5F7F8] min-h-screen">

    {{-- Hero with cover image overlay. Heights pinned inline because
         arbitrary Tailwind values can be missing from compiled CSS. --}}
    <section class="relative">
            {{-- Kapaksız: sade brand hero + wave. --}}
            <div class="relative overflow-hidden"
                 style="background: linear-gradient(135deg, #1E6F5C 0%, #155946 60%, #0F1F1A 100%);">
                <div class="mx-auto px-6 py-16 text-center" style="max-width: 820px;">
                    <a href="{{ route('blog.index') }}"
                       class="inline-flex items-center text-sm text-white/85 hover:text-white mb-5 transition-colors">
                        <i class="ri-arrow-left-line mr-1.5"></i>{{ __('blog.back_to_blog') }}
                    </a>
                    <h1 class="text-3xl lg:text-5xl font-bold leading-tight mb-5 text-white"
                        style="letter-spacing: -0.02em;">{{ $title }}</h1>
                    <div class="inline-flex items-center text-sm text-white/85">
                        <i class="ri-calendar-line mr-2"></i>
                        {{ optional($post->published_at)->translatedFormat('d F Y') ?? $post->created_at->translatedFormat('d F Y') }}
                    </div>
                </div>
                <div class="absolute bottom-0 left-0 right-0 pointer-events-none">
                    <svg viewBox="0 0 1440 80" preserveAspectRatio="none"
                         style="display: block; width: 100%; height: 60px; fill: #F5F7F8;">
                        <path d="M0,40 C360,100 1080,-20 1440,40 L1440,80 L0,80 Z"></path>
                    </svg>
                </div>
            </div>
    </section>

    {{-- Body — başlık bloğuyla akışı bozmadan devam etsin. --}}
    <article class="pt-10 pb-16 lg:pt-14 lg:pb-24">
        <div class="mx-auto px-6" style="max-width: 820px;">
            {{-- Görünen breadcrumb: Home > Blog > İlgili yazı --}}
            <div style="margin-bottom: 1.75rem;">
                <x-breadcrumbs :items="[
                    ['name' => __('general.home'), 'url' => route('home')],
                    ['name' => __('blog.blog'), 'url' => route('blog.index')],
                    ['name' => $title],
                ]" />
            </div>

            @if($excerpt)
                {{-- Excerpt card — soft green tinted background instead
                     of a hard left border. Breathes with the content
                     card below via mb-8. --}}
                <div class="mb-10 p-6 lg:p-8 rounded-2xl"
                     style="background: linear-gradient(135deg, rgba(30,111,92,0.06) 0%, rgba(30,111,92,0.02) 100%); border: 1px solid rgba(30,111,92,0.10);">
                    <div class="flex items-start gap-3">
                        <i class="ri-double-quotes-l" style="color: #1E6F5C; font-size: 1.5rem; line-height: 1; margin-top: 0.15rem; flex-shrink: 0;"></i>
                        <p class="text-lg lg:text-xl leading-relaxed" style="color: #2A3A34; font-style: italic; margin: 0;">
                            {{ $excerpt }}
                        </p>
                    </div>
                </div>
            @endif

            <div class="prose prose-lg max-w-none bg-white rounded-2xl shadow-sm p-8 lg:p-12"
                 style="line-height: 1.75;">
                @if($body)
                    {!! $body !!}
                @else
                    <p class="text-gray-500">{{ __('pages.no_content') }}</p>
                @endif
            </div>

            {{-- İlgili yazılar — internal linking. SEO: crawl depth
                 artar, kullanıcı: 2. sayfaya geçme oranı yükselir.

                 Tüm layout inline stylelenmiştir — Tailwind arbitrary
                 class'ları (grid-cols-3, aspect-ratio, gap-5) production
                 CSS'inde her zaman yer almayabiliyor, o yüzden hem
                 grid hem her card explicit inline stil taşıyor. --}}
            @if(isset($relatedPosts) && $relatedPosts->count() > 0)
                <section style="margin-top: 3.5rem;" aria-label="{{ __('blog.related_posts') }}">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem;">
                        <i class="ri-article-line" style="color: #1E6F5C; font-size: 1.25rem; line-height: 1;"></i>
                        <h3 style="font-family: Inter, sans-serif; font-size: 1.2rem; font-weight: 700; color: #0F1F1A; letter-spacing: -0.01em; margin: 0;">
                            {{ __('blog.related_posts') }}
                        </h3>
                    </div>
                    <div class="related-grid"
                         style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem;">
                        @foreach($relatedPosts as $rp)
                            @php
                                $rpDesc = $rp->description ?? $rp->descriptions->first();
                                $rpTitle = $rpDesc ? \App\Helpers\BrandHelper::render($rpDesc->title) : '';
                                $rpSlug = $rpDesc ? $rpDesc->slug : null;
                                if (! $rpSlug) continue;
                                $rpUrl = route('blog.show', $rpSlug);
                                $rpHasCover = (bool) $rp->image;
                                $rpCover = $rpHasCover
                                    ? (str_starts_with($rp->image, 'http') ? $rp->image : route('image.resize', ['size' => 'blog_cover', 'fit' => 'crop', 'path' => $rp->image]))
                                    : null;
                                $rpDate = optional($rp->published_at)->translatedFormat('d M Y') ?? optional($rp->created_at)->translatedFormat('d M Y');
                            @endphp
                            <a href="{{ $rpUrl }}"
                               style="display: block; background: #fff; border-radius: 0.85rem; overflow: hidden; text-decoration: none; box-shadow: 0 1px 3px rgba(15,31,26,0.05); border: 1px solid #EEF2EF; transition: transform .3s ease, box-shadow .3s ease;"
                               onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 22px -10px rgba(15,31,26,0.20)';"
                               onmouseout="this.style.transform=''; this.style.boxShadow='0 1px 3px rgba(15,31,26,0.05)';">
                                {{-- Kart cover — sabit 140px, 16:10 aspect
                                     ratio yerine (ratio yok compile'da) --}}
                                <div style="width: 100%; height: 140px; background: #F1F5F4; overflow: hidden;">
                                    @if($rpCover)
                                        <img src="{{ $rpCover }}" alt="{{ $rpTitle }}"
                                             loading="lazy"
                                             style="width: 100%; height: 100%; object-fit: cover; display: block;">
                                    @endif
                                </div>
                                <div style="padding: 0.85rem 1rem 1rem;">
                                    <p style="font-size: 0.68rem; color: #6B7280; margin: 0 0 0.3rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">
                                        <i class="ri-calendar-line" style="margin-right: 0.25rem;"></i>{{ $rpDate }}
                                    </p>
                                    <h4 style="font-family: Inter, sans-serif; font-size: 0.9rem; font-weight: 700; color: #0F1F1A; line-height: 1.4; margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        {{ $rpTitle }}
                                    </h4>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Sosyal paylaşım — sadece 4 kanal: Facebook, WhatsApp,
                 Instagram (marka sayfası) ve Bağlantı Kopyala. X ve
                 LinkedIn kaldırıldı; hedef kitlemiz Türkiye'de bu iki
                 kanalı emlak için nadiren kullanıyor.

                 Instagram public share URL'i desteklemez — buton
                 markanın Instagram profiline yönlendirir. --}}
            @php
                $igProfile = trim((string) get_setting('social_instagram_url', ''));
            @endphp
            <div class="mt-10 bg-white rounded-2xl shadow-sm p-6 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-2 text-sm font-bold text-[#1A1A1A]">
                    <i class="ri-share-line text-[#1E6F5C] text-lg"></i>
                    {{ __('blog.share_post') }}
                </div>
                <div class="flex items-center gap-2">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener"
                       class="w-10 h-10 rounded-full text-white flex items-center justify-center transition-colors"
                       style="background: #1877F2;"
                       onmouseover="this.style.background='#0c5dc7'" onmouseout="this.style.background='#1877F2'"
                       title="Facebook">
                        <i class="ri-facebook-fill"></i>
                    </a>
                    <a href="https://wa.me/?text={{ $shareText }}%20{{ $shareUrl }}" target="_blank" rel="noopener"
                       class="w-10 h-10 rounded-full text-white flex items-center justify-center transition-colors"
                       style="background: #25D366;"
                       onmouseover="this.style.background='#1ea554'" onmouseout="this.style.background='#25D366'"
                       title="WhatsApp">
                        <i class="ri-whatsapp-line"></i>
                    </a>
                    @if($igProfile !== '')
                        <a href="{{ $igProfile }}" target="_blank" rel="noopener"
                           class="w-10 h-10 rounded-full text-white flex items-center justify-center transition-transform"
                           style="background: linear-gradient(45deg, #F58529 0%, #DD2A7B 40%, #8134AF 75%, #515BD4 100%);"
                           onmouseover="this.style.transform='scale(1.06)'" onmouseout="this.style.transform='scale(1)'"
                           title="Instagram">
                            <i class="ri-instagram-line"></i>
                        </a>
                    @endif
                    <button type="button"
                            data-copy-url="{{ $pageUrl }}"
                            class="copy-link-btn w-10 h-10 rounded-full text-[#1A1A1A] flex items-center justify-center transition-all"
                            style="background: #F1F5F4;"
                            onmouseover="this.style.background='#E4EBE8'" onmouseout="this.style.background='#F1F5F4'"
                            title="{{ __('blog.copy_link') }}">
                        <i class="ri-link"></i>
                    </button>
                </div>
            </div>

            {{-- Back to blog CTA --}}
            <div class="mt-10 text-center">
                <a href="{{ route('blog.index') }}"
                   class="inline-flex items-center px-6 py-3 rounded-full bg-[#1E6F5C] text-white text-sm font-bold hover:bg-[#155946] transition-colors">
                    <i class="ri-arrow-left-line mr-2"></i>{{ __('blog.back_to_blog') }}
                </a>
            </div>
        </div>
    </article>
</div>

@push('scripts')
<script>
// Copy-to-clipboard for share strip — brief green flash on success.
document.querySelectorAll('.copy-link-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var url = btn.getAttribute('data-copy-url') || window.location.href;
        var flash = function () {
            var prev = btn.style.background;
            btn.style.background = '#1E6F5C';
            btn.querySelector('i').style.color = '#fff';
            setTimeout(function () {
                btn.style.background = prev || '#F1F5F4';
                btn.querySelector('i').style.color = '';
            }, 1200);
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(flash).catch(function () {
                window.prompt('{{ __('blog.copy_link') }}', url);
            });
        } else {
            var ta = document.createElement('textarea');
            ta.value = url; document.body.appendChild(ta); ta.select();
            try { document.execCommand('copy'); flash(); } catch (e) {}
            document.body.removeChild(ta);
        }
    });
});
</script>
@endpush
@endsection

@push('styles')
<style>
    /* Fixed-height blog hero — bypasses any missing Tailwind JIT
       arbitrary-value classes. */
    .blog-hero-img {
        height: 420px;
        min-height: 420px;
    }
    @media (min-width: 1024px) {
        .blog-hero-img { height: 540px; min-height: 540px; }
    }

    .prose { color: #374151; line-height: 1.85; font-size: 17px; }
    .prose h1, .prose h2, .prose h3, .prose h4, .prose h5, .prose h6 {
        color: #0F1F1A; font-weight: 700; margin-top: 2rem; margin-bottom: 1rem;
        font-family: Inter, sans-serif;
    }
    .prose h2 { font-size: 1.875rem; }
    .prose h3 { font-size: 1.5rem; }
    .prose p  { margin-bottom: 1.25rem; }
    .prose a  { color: #1E6F5C; text-decoration: none; border-bottom: 1px solid #1E6F5C40; }
    .prose a:hover { border-bottom-color: #1E6F5C; }
    .prose ul, .prose ol { margin-bottom: 1.25rem; padding-left: 1.75rem; }
    .prose li { margin-bottom: 0.5rem; }
    .prose blockquote {
        border-left: 4px solid #1E6F5C; padding: .75rem 1.25rem;
        margin: 1.75rem 0; font-style: italic; color: #4B5563;
        background: #F5F7F8; border-radius: 0 .5rem .5rem 0;
    }
    .prose img { max-width: 100%; height: auto; border-radius: .75rem; margin: 1.75rem 0; }
    .prose code { background: #F5F7F8; padding: .15rem .4rem; border-radius: .25rem; font-size: 0.95em; }
</style>
@endpush
