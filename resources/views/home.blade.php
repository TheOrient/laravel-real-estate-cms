@extends('layouts.app')

{{-- SEO Meta Tags — brand-neutral, settings-driven.
     Description prefers the locale-aware translation file over the
     DB setting, so EN visitors get an EN meta description (Google
     ranks the language-matched snippet). Falls back to setting when
     translation is missing. --}}
@php
    // SEO: birincil anahtar kelime BAŞTA, marka sonda. Marka kuyruğundaki
    // " Kuşadası" atılır — başlıkta zaten geçtiği için tekrarı önler.
    $brandFull  = trim((string) get_setting('site_title', config('app.name')));
    $brandShort = trim(\Illuminate\Support\Str::before($brandFull, ' Kuşadası')) ?: $brandFull;
    $homeTitle  = __('general.home_meta_tagline') . ' | ' . $brandShort;
    $localizedDefault = __('general.default_meta_description');
    $settingDesc      = trim((string) get_setting('site_description', ''));
    // If active locale is not TR (source), prefer the localized string.
    $homeDescription  = app()->getLocale() === 'tr'
        ? ($settingDesc !== '' ? $settingDesc : $localizedDefault)
        : $localizedDefault;
    $homeDescription  = \App\Helpers\BrandHelper::render($homeDescription);
@endphp
@section('title', $homeTitle)
@section('meta_description', $homeDescription)
@section('meta_keywords', __('general.default_meta_keywords'))
@section('canonical', route('home'))

{{-- Open Graph Tags --}}
@section('og_title', $homeTitle)
@section('og_description', $homeDescription)
@section('og_url', route('home'))
@section('content')
@php
    // Hero görsel yolu: admin ayarı varsa onu kullan, yoksa varsayılan dosyaya düş
    // Path, ImageResizeController'ın kullandığı "uploads" diskine göre verilir
    $defaultHeroImagePath = 'uploads/settings/home-hero.jpg';
    $heroImagePath = get_setting('home_hero_image') ?: $defaultHeroImagePath;
@endphp

<!-- Hero Section -->
<section class="home-hero relative w-full min-h-[80vh] md:h-screen flex items-center justify-center overflow-hidden pt-8 md:pt-0 pb-12 md:pb-0">
    <div class="absolute inset-0">
        <picture>
            <source
                media="(max-width: 768px)"
                srcset="{{ route('image.resize', ['size' => 'home_hero_mobile', 'fit' => 'crop', 'path' => $heroImagePath]) }}"
            >
            <img
                src="{{ route('image.resize', ['size' => 'home_hero_desktop', 'fit' => 'crop', 'path' => $heroImagePath]) }}"
                alt="{{ __('general.hero_title') }} — {{ trim((string) get_setting('site_title', 'Real Estate CMS Demo')) }}"
                fetchpriority="high"
                loading="eager"
                decoding="async"
                width="1920" height="1080"
                class="w-full h-full object-cover object-top"
            >
        </picture>
        {{-- Layered gradients: vertical fade to keep top/bottom text legible,
             plus a brand-tinted vignette so the whole hero feels intentional. --}}
        <div class="absolute inset-0" style="background: linear-gradient(180deg, rgba(15,31,26,0.55) 0%, rgba(15,31,26,0.30) 35%, rgba(15,31,26,0.45) 75%, rgba(15,31,26,0.85) 100%);"></div>
        <div class="absolute inset-0 pointer-events-none" style="background: radial-gradient(ellipse 80% 60% at 50% 45%, rgba(30,111,92,0.18), transparent 70%);"></div>
    </div>

    <div class="relative z-10 max-w-[1320px] mx-auto px-6 w-full">
        <div class="text-center mb-8 md:mb-12">
            <span class="inline-flex items-center gap-2 px-4 py-1.5 mb-6 rounded-full text-xs font-bold tracking-widest uppercase"
                  style="background: rgba(255,255,255,0.12); color: #fff; border: 1px solid rgba(255,255,255,0.2); backdrop-filter: blur(6px);">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse"></span>
                {{ __('general.hero_pill') }}
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-bold text-white mb-6 leading-[1.05]"
                style="text-shadow: 0 4px 32px rgba(0,0,0,0.45);">
                {{ __('general.hero_title') }}
            </h1>
            <p class="text-lg md:text-xl max-w-2xl mx-auto" style="color: rgba(255,255,255,0.92); text-shadow: 0 2px 12px rgba(0,0,0,0.4);">
                {{ __('general.hero_description') }}
            </p>
        </div>

        <!-- Search Form -->
        <div class="max-w-5xl mx-auto">
            {{-- Search form: category + type (sale/rent) + price range.
                 No text-search field — too noisy on a listings home; users
                 use category browse + filters instead. --}}
            @php
                /** Use 2nd-level categories (children of root "Emlak") as
                 *  the user-facing category options. The root category is
                 *  too vague to filter by, and leaf categories ("Satılık
                 *  Daire") are already represented via the type filter. */
                $usableCategories = collect();
                foreach ($categories as $rootCat) {
                    if ($rootCat->children && $rootCat->children->count() > 0) {
                        foreach ($rootCat->children as $child) {
                            $usableCategories->push($child);
                        }
                    } else {
                        $usableCategories->push($rootCat);
                    }
                }
            @endphp
            <form action="{{ route('categories.show.all') }}" method="GET" class="hero-search-pill">
                <div class="hero-search-row">
                    <div class="hero-search-cell">
                        <label class="hero-search-label">
                            <i class="ri-price-tag-3-line"></i> {{ __('general.category') }}
                        </label>
                        <div class="hero-search-control">
                            <select name="category_slug">
                                <option value="">{{ __('general.all_categories') }}</option>
                                @foreach($usableCategories as $category)
                                    <option value="{{ $category->slug }}"
                                        {{ request('category_slug') === $category->slug ? 'selected' : '' }}>
                                        {{ $category->name ?? optional($category->descriptions->first())->name }}
                                    </option>
                                @endforeach
                            </select>
                            <i class="ri-arrow-down-s-line hero-search-arrow"></i>
                        </div>
                    </div>
                    <span class="hero-search-divider"></span>
                    <div class="hero-search-cell">
                        <label class="hero-search-label">
                            <i class="ri-home-3-line"></i> {{ __('general.listing_type') }}
                        </label>
                        <div class="hero-search-control">
                            <select name="type">
                                <option value="">{{ __('general.all') }}</option>
                                <option value="satilik" {{ request('type')==='satilik' ? 'selected' : '' }}>{{ __('general.for_sale') }}</option>
                                <option value="kiralik" {{ request('type')==='kiralik' ? 'selected' : '' }}>{{ __('general.for_rent') }}</option>
                            </select>
                            <i class="ri-arrow-down-s-line hero-search-arrow"></i>
                        </div>
                    </div>
                    <span class="hero-search-divider"></span>
                    <div class="hero-search-cell">
                        <label class="hero-search-label">
                            <i class="ri-money-dollar-circle-line"></i> {{ __('general.min_price') }}
                        </label>
                        <div class="hero-search-control">
                            <input type="number" name="min_price" placeholder="0"
                                   value="{{ request('min_price') }}">
                            <span class="hero-search-suffix">₺</span>
                        </div>
                    </div>
                    <span class="hero-search-divider"></span>
                    <div class="hero-search-cell">
                        <label class="hero-search-label">
                            <i class="ri-money-dollar-circle-line"></i> {{ __('general.max_price') }}
                        </label>
                        <div class="hero-search-control">
                            <input type="number" name="max_price" placeholder="∞"
                                   value="{{ request('max_price') }}">
                            <span class="hero-search-suffix">₺</span>
                        </div>
                    </div>
                    <button type="submit" class="hero-search-btn"
                            aria-label="{{ __('general.search_listings') }}">
                        <i class="ri-search-line"></i>
                        <span class="hero-search-btn-label">{{ __('general.search_listings') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    </section>

<!-- Featured Listings Section -->
<section class="py-24 bg-[#F5F7F8]">
    <div class="max-w-[1320px] mx-auto px-6">
        <div class="text-center mb-16">
            <span class="section-eyebrow">
                <i class="ri-star-line"></i> {{ __('general.featured_listings_eyebrow') }}
            </span>
            <h2 class="text-4xl md:text-5xl font-bold text-[#1A1A1A] mb-4">{{ __('general.featured_listings_title') }}</h2>
            <p class="text-lg text-[#1A1A1A]/70 max-w-2xl mx-auto">{{ __('general.featured_listings_description') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($featuredListings as $listing)
                <a href="{{ route('listings.show', $listing->slug) }}" class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-500 group">
                    <div class="relative w-full h-64 overflow-hidden">
                        @include('listings.partials.card-gallery', ['listing' => $listing])
                        @if($listing->price)
                            <div class="absolute top-4 left-4 text-white px-4 py-2 rounded-xl font-bold text-sm shadow-lg"
                                 style="background: linear-gradient(135deg, #1E6F5C 0%, #13493E 100%); box-shadow: 0 8px 24px -8px rgba(30,111,92,0.5);">
                                {{ number_format($listing->price, 0) }} ₺
                            </div>
                        @endif
                    </div>
                    <div class="p-5">
                        <h3 class="text-lg font-bold text-[#1A1A1A] mb-2 line-clamp-2">{{ $listing->title }}</h3>
                        <p class="text-sm text-[#1A1A1A]/60 mb-0 flex items-center">
                            <i class="ri-map-pin-line mr-1"></i>{{ $listing->city->name }}, {{ $listing->district->name }}
                        </p>
                        @include('listings.partials.quick-specs', ['listing' => $listing])
                        {{-- İlan sahibi bloğu kaldırıldı — tek ofis site
                             mimarisinde her ilanı aynı ekip yayınlar,
                             kartlarda tekrar tekrar "Admin User" görmek
                             görsel gürültü. --}}
                    </div>
                </a>
            @empty
                <div class="col-span-4 text-center py-8">
                    <p class="text-gray-500">{{ __('general.no_featured_listings') }}</p>
                </div>
            @endforelse
        </div>

        @if($featuredListings->count() > 0)
            <div class="text-center mt-16">
                <a href="{{ route('categories.show.all') }}" class="cta-pill-primary">
                    <span>{{ __('general.view_all_listings') }}</span>
                    <span class="cta-pill-arrow"><i class="ri-arrow-right-line"></i></span>
                </a>
            </div>
        @endif
    </div>
    </section>

<!-- Latest Listings Section -->
@if($latestListings->count() > 0)
<section class="py-24 bg-white">
    <div class="max-w-[1320px] mx-auto px-6">
        <div class="text-center mb-16">
            <span class="section-eyebrow">
                <i class="ri-time-line"></i> {{ __('general.latest_listings_eyebrow') }}
            </span>
            <h2 class="text-4xl md:text-5xl font-bold text-[#1A1A1A] mb-4">{{ __('general.latest_listings_title') }}</h2>
            <p class="text-lg text-[#1A1A1A]/70 max-w-2xl mx-auto">{{ __('general.latest_listings_description') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($latestListings as $listing)
                <a href="{{ route('listings.show', $listing->slug) }}" class="bg-white border border-gray-200 rounded-2xl overflow-hidden hover:shadow-xl transition-all duration-500 group">
                    <div class="relative w-full h-64 overflow-hidden">
                        @include('listings.partials.card-gallery', ['listing' => $listing])
                        @if($listing->price)
                            <div class="absolute top-4 left-4 text-white px-4 py-2 rounded-xl font-bold text-sm shadow-lg"
                                 style="background: linear-gradient(135deg, #1E6F5C 0%, #13493E 100%); box-shadow: 0 8px 24px -8px rgba(30,111,92,0.5);">
                                {{ number_format($listing->price, 0) }} ₺
                            </div>
                        @endif
                    </div>
                    <div class="p-5">
                        <h3 class="text-lg font-bold text-[#1A1A1A] mb-2 line-clamp-2">{{ $listing->title }}</h3>
                        <p class="text-sm text-[#1A1A1A]/60 mb-0 flex items-center">
                            <i class="ri-map-pin-line mr-1"></i>{{ $listing->city->name }}, {{ $listing->district->name }}
                        </p>
                        @include('listings.partials.quick-specs', ['listing' => $listing])
                        {{-- İlan sahibi bloğu kaldırıldı (single-agency). --}}
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Popüler Şehirler kaldırıldı — Real Estate CMS Demo tek lokasyon (Kuşadası) odaklı,
     marketplace tarzı "şehir kartları" stratejiye uymuyor. --}}

<!-- Why Choose Us Section -->
<section class="py-24 bg-white">
    <div class="max-w-[1320px] mx-auto px-6">
        <div class="text-center mb-16">
            <span class="section-eyebrow">
                <i class="ri-shield-star-line"></i> {{ __('general.why_choose_us_eyebrow') }}
            </span>
            <h2 class="text-4xl md:text-5xl font-bold text-[#1A1A1A] mb-4">
                {{ __('general.why_choose_us_title', ['app_name' => trim((string) get_setting('site_title', 'Real Estate CMS Demo')) ?: 'Real Estate CMS Demo']) }}
            </h2>
            <p class="text-lg text-[#1A1A1A]/70 max-w-2xl mx-auto">
                {{ __('general.why_choose_us_description') }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="why-card">
                <div class="why-card-icon">
                    <i class="ri-shield-check-line"></i>
                </div>
                <h3 class="text-xl font-bold text-[#1A1A1A] mb-3">{{ __('general.reliable_platform') }}</h3>
                <p class="text-base text-[#1A1A1A]/70">
                    {{ __('general.reliable_platform_description') }}
                </p>
            </div>

            <div class="why-card">
                <div class="why-card-icon">
                    <i class="ri-flashlight-line"></i>
                </div>
                <h3 class="text-xl font-bold text-[#1A1A1A] mb-3">{{ __('general.fast_results') }}</h3>
                <p class="text-base text-[#1A1A1A]/70">
                    {{ __('general.fast_results_description') }}
                </p>
            </div>

            <div class="why-card">
                <div class="why-card-icon">
                    <i class="ri-user-star-line"></i>
                </div>
                <h3 class="text-xl font-bold text-[#1A1A1A] mb-3">{{ __('general.user_focused_design') }}</h3>
                <p class="text-base text-[#1A1A1A]/70">
                    {{ __('general.user_focused_design_description') }}
                </p>
            </div>
        </div>
    </div>
</section>

{{-- Blog & Guides — dynamic latest 3 posts (hidden when no posts yet) --}}
@if(!empty($latestBlogPosts) && $latestBlogPosts->count() > 0)
<section class="home-blog-section bg-white">
    <div class="max-w-[1320px] mx-auto px-6">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between mb-12 gap-6">
            <div>
                <h2 class="text-4xl md:text-5xl font-bold text-[#1A1A1A] mb-3">
                    {{ __('general.blog_guides_title') }}
                </h2>
                <p class="text-lg text-[#1A1A1A]/70 max-w-2xl">
                    {{ __('general.blog_guides_description') }}
                </p>
            </div>
            <a href="{{ route('blog.index') }}"
               class="hidden md:inline-flex items-center px-5 py-2.5 rounded-full bg-[#1E6F5C]/10 text-[#1E6F5C] text-sm font-bold hover:bg-[#1E6F5C]/20 transition-colors whitespace-nowrap">
                {{ __('blog.view_all') }}
                <i class="ri-arrow-right-line ml-1.5"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($latestBlogPosts as $post)
                @include('blog.partials.card', ['post' => $post])
            @endforeach
        </div>

        <div class="text-center mt-12 md:hidden">
            <a href="{{ route('blog.index') }}"
               class="inline-flex items-center px-6 py-3 rounded-full bg-[#1E6F5C] text-white text-sm font-bold hover:bg-[#155946] transition-colors">
                {{ __('blog.view_all') }}
                <i class="ri-arrow-right-line ml-2"></i>
            </a>
        </div>
    </div>
</section>
@endif

@endsection

@include('listings.partials.card-gallery-scripts')
