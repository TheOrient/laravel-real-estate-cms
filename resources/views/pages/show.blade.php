@extends('layouts.app')

@php
    $pageTitle = \App\Helpers\BrandHelper::render($page->title);
    $pageBrand = \App\Helpers\BrandHelper::render(get_setting('site_title', config('app.name')));
    $pageDescription = \App\Helpers\BrandHelper::render($page->meta_description ?: $pageTitle);
    $pageUrl = route('pages.show', $page->slug);
@endphp

@section('title', $pageTitle . ' | ' . $pageBrand)
@section('canonical', $pageUrl)
@section('og_title', $pageTitle . ' | ' . $pageBrand)
@section('og_description', $pageDescription)
@section('og_url', $pageUrl)

@section('meta_description', $pageDescription)

@push('jsonld')
<script type="application/ld+json">
{!! json_encode(App\Helpers\SeoHelper::generateBreadcrumbSchema([
    ['name' => __('general.home'), 'url' => route('home')],
    ['name' => $pageTitle, 'url' => $pageUrl],
]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
<div style="background: #F5F7F8; min-height: 100vh;">
    {{-- Hero — brand gradient + soft wave divider, mirrors the blog index hero --}}
    <section style="position: relative; overflow: hidden; background: linear-gradient(135deg, #1E6F5C 0%, #155946 60%, #0F1F1A 100%);">
        <div style="position: absolute; inset: 0; opacity: .10; background-image: radial-gradient(circle at 22% 28%, white 1px, transparent 1.5px), radial-gradient(circle at 78% 72%, white 1px, transparent 1.5px); background-size: 56px 56px; pointer-events: none;"></div>
        <div style="position: relative; max-width: 1100px; margin: 0 auto; padding: 5.5rem 1.5rem 6rem;">
            <div style="text-align: center;">
                <span style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.35rem 1rem; border-radius: 999px; background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.22); color: #fff; font-size: 0.7rem; font-weight: 800; letter-spacing: 0.12em; margin-bottom: 1.25rem; text-transform: uppercase; backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);">
                    <i class="ri-file-text-line"></i> {{ $pageBrand }}
                </span>
                <h1 style="font-family: Inter, sans-serif; font-weight: 800; color: #fff; font-size: clamp(2rem, 4.5vw, 3.25rem); line-height: 1.1; margin: 0 0 1rem; text-shadow: 0 4px 24px rgba(0,0,0,0.30);">
                    {{ \App\Helpers\BrandHelper::render($page->title) }}
                </h1>
                @if($page->meta_description)
                    <p style="max-width: 36rem; margin: 0 auto; font-size: 1.05rem; line-height: 1.55; color: rgba(255,255,255,0.85); text-shadow: 0 2px 10px rgba(0,0,0,0.30);">
                        {{ \App\Helpers\BrandHelper::render($page->meta_description) }}
                    </p>
                @endif
            </div>
        </div>
        <div style="position: absolute; bottom: 0; left: 0; right: 0; pointer-events: none;">
            <svg viewBox="0 0 1440 80" preserveAspectRatio="none" style="display: block; width: 100%; height: 60px; fill: #F5F7F8;">
                <path d="M0,40 C360,100 1080,-20 1440,40 L1440,80 L0,80 Z"></path>
            </svg>
        </div>
    </section>

    {{-- Body --}}
    <section style="padding: 3rem 1.5rem 5rem;">
        <div style="max-width: 820px; margin: 0 auto;">
            <div class="prose prose-lg" style="background: #fff; border-radius: 1.25rem; padding: 2.5rem; box-shadow: 0 2px 4px rgba(15,31,26,0.04), 0 4px 16px rgba(15,31,26,0.06);">
                @if($page->content)
                    {!! \App\Helpers\BrandHelper::render($page->content) !!}
                @else
                    <div style="text-align: center; color: #6B7280; padding: 2rem 0;">
                        <i class="ri-file-text-line" style="font-size: 2.5rem; opacity: 0.5; display: block; margin-bottom: 0.75rem;"></i>
                        <p>{{ __('pages.no_content') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
    /* Prose styling for content */
    .prose {
        color: #374151;
        line-height: 1.75;
    }

    .prose h1,
    .prose h2,
    .prose h3,
    .prose h4,
    .prose h5,
    .prose h6 {
        color: #111827;
        font-weight: 600;
        margin-top: 2rem;
        margin-bottom: 1rem;
    }

    .prose h1 { font-size: 2.25rem; }
    .prose h2 { font-size: 1.875rem; }
    .prose h3 { font-size: 1.5rem; }
    .prose h4 { font-size: 1.25rem; }

    .prose p {
        margin-bottom: 1.25rem;
    }

    .prose a {
        color: #059669;
        text-decoration: none;
    }

    .prose a:hover {
        color: #047857;
        text-decoration: underline;
    }

    .prose ul,
    .prose ol {
        margin-bottom: 1.25rem;
        padding-left: 1.5rem;
    }

    .prose li {
        margin-bottom: 0.5rem;
    }

    .prose blockquote {
        border-left: 4px solid #d1d5db;
        padding-left: 1rem;
        margin: 1.5rem 0;
        font-style: italic;
        color: #6b7280;
    }

    .prose table {
        width: 100%;
        margin-bottom: 1.5rem;
        border-collapse: collapse;
    }

    .prose th,
    .prose td {
        border: 1px solid #d1d5db;
        padding: 0.75rem;
        text-align: left;
    }

    .prose th {
        background-color: #f9fafb;
        font-weight: 600;
    }

    .prose img {
        max-width: 100%;
        height: auto;
        border-radius: 0.5rem;
        margin: 1.5rem 0;
    }
</style>
@endpush
