@extends('layouts.app')

@section('title', __('blog.meta_index_title'))
@section('meta_description', __('blog.meta_index_description'))
@section('canonical', route('blog.index'))

@section('content')
<div class="bg-[#F5F7F8] min-h-screen">

    {{-- Sayfa başlığı — sade markalı banner (görsel yok, hızlı + tutarlı). --}}
    <section class="relative overflow-hidden" style="background: linear-gradient(135deg, #1E6F5C 0%, #155946 55%, #0F1F1A 100%);">
        <div class="absolute inset-0 pointer-events-none" style="opacity: .12; background-image: radial-gradient(circle at 22% 30%, white 1px, transparent 1.5px), radial-gradient(circle at 78% 70%, white 1px, transparent 1.5px); background-size: 56px 56px;"></div>
        <div class="relative max-w-[1320px] mx-auto px-6 text-center" style="padding-top: 5rem; padding-bottom: 7rem;">
            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold tracking-wider mb-5"
                  style="background: rgba(255,255,255,0.18); color: #fff; letter-spacing: .18em;">
                <i class="ri-article-line mr-2"></i>{{ strtoupper(__('blog.blog')) }}
            </span>
            <h1 class="text-4xl lg:text-6xl font-bold leading-[1.1] mb-5 mx-auto max-w-4xl"
                style="font-family: Inter, sans-serif; color: #ffffff; text-shadow: 0 2px 24px rgba(0,0,0,0.25); letter-spacing: -0.01em;">
                {{ __('blog.hero_title') }}
            </h1>
            <p class="text-lg lg:text-xl mx-auto max-w-2xl" style="color: rgba(255,255,255,0.88);">
                {{ __('blog.meta_index_description') }}
            </p>
        </div>
        <div class="absolute bottom-0 left-0 right-0 pointer-events-none">
            <svg viewBox="0 0 1440 80" preserveAspectRatio="none" class="block w-full" style="height: 72px; fill: #F5F7F8;">
                <path d="M0,40 C360,100 1080,-20 1440,40 L1440,80 L0,80 Z"></path>
            </svg>
        </div>
    </section>

    <div class="max-w-[1320px] mx-auto px-6 py-12 lg:py-16">

        @if($posts->total() === 0)
            <div class="text-center py-24">
                <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-[#1E6F5C]/10 flex items-center justify-center">
                    <i class="ri-article-line text-3xl text-[#1E6F5C]"></i>
                </div>
                <h2 class="text-2xl font-bold text-[#1A1A1A] mb-2">{{ __('blog.no_posts_yet') }}</h2>
                <p class="text-gray-500">{{ __('blog.no_posts_hint') }}</p>
            </div>
        @else
            <div class="mb-8">
                <h2 class="text-2xl lg:text-3xl font-bold"
                    style="font-family: Inter, sans-serif; color: #1A1A1A; letter-spacing: -0.01em;">{{ __('blog.latest_posts') }}</h2>
                <p class="text-sm mt-1" style="color: #6B7280;">{{ __('blog.latest_hint', ['count' => $posts->total()]) }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-7">
                @foreach($posts as $post)
                    @include('blog.partials.card', ['post' => $post])
                @endforeach
            </div>

            <div class="mt-12">
                {{ $posts->onEachSide(1)->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
