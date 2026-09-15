@extends('layouts.app')

@php
    // Keyword-first title / description pattern. City comes from the
    // agency's default service area setting so "Satılık Daire Kuşadası"
    // shows up in the SERP head-term instead of the raw category name.
    $seoCity        = trim((string) get_setting('seo_default_city', 'Kuşadası'));
    // Başlıkta zaten "Kuşadası" geçiyor; marka kuyruğundan atılır (tekrar + uzunluk).
    $seoBrandFull   = trim((string) get_setting('site_title', 'Real Estate CMS Demo'));
    $seoBrand       = trim(\Illuminate\Support\Str::before($seoBrandFull, ' Kuşadası')) ?: $seoBrandFull;
    $categoryName   = $category ? $category->name : __('slug.all_listings_title');
    $seoTitle       = $category
        ? __('categories.seo_category_title', ['category' => $categoryName, 'city' => $seoCity, 'count' => number_format($listings->total()), 'brand' => $seoBrand])
        : __('categories.seo_all_title', ['city' => $seoCity, 'count' => number_format($listings->total()), 'brand' => $seoBrand]);
    $seoDescription = $category
        ? __('categories.seo_category_description', ['category' => mb_strtolower($categoryName), 'city' => $seoCity, 'count' => number_format($listings->total()), 'brand' => $seoBrand])
        : __('categories.seo_all_description', ['city' => $seoCity, 'count' => number_format($listings->total()), 'brand' => $seoBrand]);
    $seoKeywords    = $category
        ? mb_strtolower($categoryName . ' ' . $seoCity) . ', ' . mb_strtolower($categoryName) . ' ilan, ' . mb_strtolower($categoryName . ' ' . $seoCity . ' satılık') . ', ' . mb_strtolower($seoCity . ' emlak') . ', ' . mb_strtolower($categoryName . ' fiyatları')
        : mb_strtolower($seoCity . ' emlak, ' . $seoCity . ' satılık daire, ' . $seoCity . ' kiralık daire, ' . $seoCity . ' villa');
    // This same set powers both the visible FAQ and the schema markup.
    // Keeping one source of truth prevents invisible/duplicated SEO copy.
    $faqItems = [
        [
            'q' => __('categories.faq_price_question', ['category' => $categoryName, 'city' => $seoCity]),
            'a' => __('categories.faq_price_answer', ['category' => mb_strtolower($categoryName), 'city' => $seoCity]),
        ],
        [
            'q' => __('categories.faq_neighbourhoods_question', ['city' => $seoCity]),
            'a' => __('categories.faq_neighbourhoods_answer', ['city' => $seoCity]),
        ],
        [
            'q' => __('categories.faq_viewing_question'),
            'a' => __('categories.faq_viewing_answer'),
        ],
    ];
@endphp

{{-- SEO Meta Tags --}}
@section('title', $seoTitle)
@section('meta_description', $seoDescription)
@section('meta_keywords', $seoKeywords)
@section('canonical', $category ? route('categories.show', $category->slug) : route('categories.show.all'))
@section('robots', $listings->total() > 0 ? 'index, follow' : 'noindex, follow')

{{-- Open Graph Tags --}}
@section('og_title', $seoTitle)
@section('og_description', $seoDescription)
@section('og_url', $category ? route('categories.show', $category->slug) : route('categories.show.all'))

{{-- Structured Data — CollectionPage + Breadcrumb so this URL is
     eligible for Google's "list of listings" rich result. --}}
@push('jsonld')
<script type="application/ld+json">
{!! json_encode([
    '@context'    => 'https://schema.org',
    '@type'       => 'CollectionPage',
    'name'        => $seoTitle,
    'description' => $seoDescription,
    'url'         => url()->current(),
    'inLanguage'  => app()->getLocale(),
    'about'       => [
        '@type' => 'Thing',
        'name'  => $categoryName . ' ' . $seoCity,
    ],
    'numberOfItems' => $listings->total(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
<script type="application/ld+json">
{!! json_encode(App\Helpers\SeoHelper::generateBreadcrumbSchema([
    ['name' => __('general.home'), 'url' => route('home')],
    ['name' => $categoryName, 'url' => url()->current()],
]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>

{{-- Visible FAQ content is also represented as structured data. --}}
<script type="application/ld+json">
{!! json_encode(App\Helpers\SeoHelper::generateFaqSchema($faqItems), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
<!-- Hero Section — SEO-optimized H1 with city keyword -->
<section class="pt-16 pb-16 bg-gradient-to-br from-[#1E6F5C] to-[#13493E]">
    <div class="max-w-[1320px] mx-auto px-6">
        {{-- H1 pattern: "Satılık Daire Kuşadası" — puts the head-term
             at the start where search engines weight it heaviest.
             The category name comes from AI-translated content when
             the visitor is on EN. --}}
        <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">
            {{ $category ? $categoryName . ' ' . $seoCity : __('categories.seo_all_h1', ['city' => $seoCity]) }}
        </h1>
        <p class="text-lg text-white/90">
            {{ number_format($total_listings) }} {{ __('categories.listings_found') }}
            @if($category && $category->description)
                <span class="block mt-2 text-white/70 text-base max-w-2xl">
                    {{ \Illuminate\Support\Str::limit(strip_tags($category->description), 220) }}
                </span>
            @endif
        </p>
    </div>
</section>

<!-- Sticky Filter Bar -->
<section class="py-8 bg-white shadow-sm  top-20 z-40">
    <div class="max-w-[1320px] mx-auto px-6">
        <form action="{{ $category ? route('categories.show', $category->slug) : route('categories.show', 'tumu') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <div class="relative">
                        <i class="ri-search-line absolute left-4 top-1/2 -translate-y-1/2 text-[#1A1A1A]/50"></i>
                        <input type="text" name="s" placeholder="{{ __('categories.search_placeholder') }}"
                            value="{{ request('s') }}"
                            class="w-full pl-12 pr-4 py-3 rounded-xl border border-[#E0E0E0] text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all" style="font-family: Inter, sans-serif;">
                    </div>
                </div>

                <!-- Category Filter -->
                <div>
                    <select name="category_filter" class="w-full px-4 py-3 rounded-xl border border-[#E0E0E0] text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all cursor-pointer" style="font-family: Inter, sans-serif;">
                        <option value="">{{ __('categories.all_categories') }}</option>
                        @if($category && $category->children->isNotEmpty())
                            @foreach($category->children as $subCategory)
                                <option value="{{ $subCategory->slug }}">{{ $subCategory->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <!-- Price Filter -->
                <div>
                    <select name="price_range" class="w-full px-4 py-3 rounded-xl border border-[#E0E0E0] text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all cursor-pointer" style="font-family: Inter, sans-serif;">
                        <option value="">{{ __('categories.all_prices') }}</option>
                        <option value="0-500000">{{ __('categories.price_under_500k') }}</option>
                        <option value="500000-1000000">{{ __('categories.price_500k_1m') }}</option>
                        <option value="1000000-3000000">{{ __('categories.price_1m_3m') }}</option>
                        <option value="3000000-6000000">{{ __('categories.price_3m_6m') }}</option>
                        <option value="6000000-999999999">{{ __('categories.price_over_6m') }}</option>
                    </select>
                </div>

                <!-- Sort -->
                <div>
                    <select name="sort" class="w-full px-4 py-3 rounded-xl border border-[#E0E0E0] text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all cursor-pointer" style="font-family: Inter, sans-serif;">
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>{{ __('categories.newest') }}</option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>{{ __('categories.price_low') }}</option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>{{ __('categories.price_high') }}</option>
                        <option value="area" {{ request('sort') == 'area' ? 'selected' : '' }}>{{ __('categories.area') }}</option>
                    </select>
                </div>
            </div>

            <!-- Advanced Filters Toggle -->
            <div class="mt-4">
                <button type="button" id="toggleFilters" class="w-full px-4 py-3 border border-[#E0E0E0] text-sm font-bold rounded-xl text-[#1A1A1A] bg-white hover:bg-[#F5F7F8] transition-all duration-300 flex items-center justify-center" style="font-family: Inter, sans-serif; font-weight: 700;">
                    <i class="ri-filter-3-line mr-2"></i>
                    {{ __('categories.advanced_filters') }}
                </button>
            </div>

            <!-- Advanced Filters Panel (initially hidden, toggled by button) -->
            <div id="advancedFilters" class="hidden mt-4 p-6 bg-gray-50 rounded-xl">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <!-- Location Filters -->
                    <div>
                        <label class="block text-sm font-semibold text-[#1A1A1A] mb-2" style="font-family: Inter, sans-serif;">{{ __('categories.city') }}</label>
                        <select id="city_id" name="city_id" class="w-full px-4 py-3 rounded-lg border border-[#E0E0E0] text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all cursor-pointer" style="font-family: Inter, sans-serif;">
                            <option value="">{{ __('categories.select_city') }}</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ isset($filters['city_id']) && $filters['city_id'] == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-[#1A1A1A] mb-2" style="font-family: Inter, sans-serif;">{{ __('categories.district') }}</label>
                        <select id="district_id" name="district_id" class="w-full px-4 py-3 rounded-lg border border-[#E0E0E0] text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all cursor-pointer" style="font-family: Inter, sans-serif;">
                            <option value="">{{ __('categories.select_district') }}</option>
                            @foreach($default_districts as $district)
                                <option value="{{ $district->id }}" {{ isset($filters['district_id']) && $filters['district_id'] == $district->id ? 'selected' : '' }}>{{ $district->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div>
                        <label class="block text-sm font-semibold text-[#1A1A1A] mb-2" style="font-family: Inter, sans-serif;">{{ __('categories.min_price') }}</label>
                        <input type="number" name="min_price" value="{{ $filters['min_price'] ?? '' }}" placeholder="Min. Fiyat" class="w-full px-4 py-3 rounded-lg border border-[#E0E0E0] text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all" style="font-family: Inter, sans-serif;">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-[#1A1A1A] mb-2" style="font-family: Inter, sans-serif;">{{ __('categories.max_price') }}</label>
                        <input type="number" name="max_price" value="{{ $filters['max_price'] ?? '' }}" placeholder="Max. Fiyat" class="w-full px-4 py-3 rounded-lg border border-[#E0E0E0] text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all" style="font-family: Inter, sans-serif;">
                    </div>
                </div>

                <div class="mt-4 flex gap-3">
                    <button type="submit" class="px-6 py-3 bg-[#1E6F5C] text-white text-sm font-bold rounded-xl hover:bg-[#13493E] transition-all duration-300 whitespace-nowrap cursor-pointer" style="font-family: Inter, sans-serif; font-weight: 700;">
                        {{ __('categories.apply_filters') }}
                    </button>
                    <a href="{{ $category ? route('categories.show', $category->slug) : route('categories.show', 'tumu') }}" class="px-6 py-3 border border-[#E0E0E0] text-sm font-bold rounded-xl text-[#1A1A1A] bg-white hover:bg-[#F5F7F8] transition-all duration-300 whitespace-nowrap cursor-pointer" style="font-family: Inter, sans-serif; font-weight: 700;">
                        {{ __('categories.clear') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- Listings Grid -->
<section class="py-16 bg-[#F5F7F8]">
    <div class="max-w-[1320px] mx-auto px-6">
        @if($listings->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($listings as $listing)
                    <a href="{{ route('listings.show', $listing->slug) }}" class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-500 group">
                        <!-- Image gallery -->
                        <div class="relative w-full h-64 overflow-hidden">
                            @include('listings.partials.card-gallery', ['listing' => $listing])

                            <!-- Price Badge -->
                            @if($listing->price)
                                <div class="absolute top-4 left-4 bg-[#1E6F5C] text-white px-4 py-2 rounded-xl font-bold text-sm">
                                    {{ number_format($listing->price, 0) }} ₺
                                </div>
                            @endif

                            <!-- Category Badge -->
                            <div class="absolute top-4 right-4 bg-white/90 text-[#1A1A1A] px-3 py-1 rounded-lg text-xs font-semibold">
                                {{ $listing->category->name }}
                            </div>

                        </div>

                        <!-- Content -->
                        <div class="p-5">
                            <h4 class="text-lg font-bold text-[#1A1A1A] mb-2 line-clamp-2">{{ $listing->title }}</h4>
                            <p class="text-sm text-[#1A1A1A]/60 mb-0 flex items-center">
                                <i class="ri-map-pin-line mr-1"></i>{{ $listing->city->name }}, {{ $listing->district->name }}
                            </p>

                            <!-- Property Details from attributes -->
                            @include('listings.partials.quick-specs', ['listing' => $listing])

                            {{-- İlan sahibi bloğu kaldırıldı — tek ofis
                                 mimarisinde her kartın altında aynı isim
                                 tekrarlıyordu, görsel gürültü oluşturuyordu. --}}
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($listings->hasPages())
                <div class="mt-12 flex justify-center">
                    {{ $listings->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-16">
                <i class="ri-inbox-line text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">{{ __('categories.no_listings_found') }}</h3>
                <p class="text-gray-500">{{ __('categories.no_listings_in_category') }}</p>
            </div>
        @endif
    </div>
</section>

{{-- Local expertise + FAQ: useful page content, not a hidden keyword block. --}}
<section class="py-16 bg-white">
    <div class="max-w-[1320px] mx-auto px-6">
        <div class="grid lg:grid-cols-5 gap-8 lg:gap-12 items-start">
            <div class="lg:col-span-2 rounded-3xl p-7 lg:p-8"
                 style="background: linear-gradient(145deg, #F0F7F4 0%, #F8FBF9 100%); border: 1px solid rgba(30,111,92,.12);">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider"
                      style="color:#1E6F5C; background:rgba(30,111,92,.10);">
                    <i class="ri-map-pin-2-line"></i>{{ __('categories.local_guide_eyebrow') }}
                </span>
                <h2 class="mt-4 text-2xl lg:text-3xl font-bold leading-tight" style="color:#0F1F1A; letter-spacing:-.02em;">
                    {{ __('categories.local_guide_title', ['city' => $seoCity]) }}
                </h2>
                <p class="mt-4 leading-relaxed" style="color:#52615B;">
                    {{ __('categories.local_guide_copy', ['city' => $seoCity]) }}
                </p>
                <a href="{{ route('pages.show', ['slug' => 'iletisim']) }}" class="inline-flex items-center gap-2 mt-6 text-sm font-bold" style="color:#1E6F5C;">
                    {{ __('categories.local_guide_cta') }} <i class="ri-arrow-right-line"></i>
                </a>
            </div>
            <div class="lg:col-span-3">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-10 h-10 rounded-full flex items-center justify-center" style="background:#1E6F5C; color:#fff;"><i class="ri-question-answer-line"></i></span>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider" style="color:#1E6F5C;">{{ __('categories.faq_eyebrow') }}</p>
                        <h2 class="text-2xl font-bold" style="color:#0F1F1A; letter-spacing:-.015em;">{{ __('categories.faq_title') }}</h2>
                    </div>
                </div>
                <div class="space-y-3">
                    @foreach($faqItems as $faq)
                        <details class="group rounded-2xl px-5 py-4" style="border:1px solid #E6EEEA; background:#fff;">
                            <summary class="flex items-center justify-between gap-4 cursor-pointer font-bold" style="color:#1D2C26; list-style:none;">
                                <span>{{ $faq['q'] }}</span><i class="ri-add-line shrink-0 text-xl group-open:hidden" style="color:#1E6F5C;"></i><i class="ri-subtract-line shrink-0 text-xl hidden group-open:block" style="color:#1E6F5C;"></i>
                            </summary>
                            <p class="mt-3 pr-8 leading-relaxed" style="color:#607069;">{{ $faq['a'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@include('listings.partials.card-gallery-scripts')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle advanced filters on mobile
    const toggleBtn = document.getElementById('toggleFilters');
    const filtersPanel = document.getElementById('advancedFilters');

    if (toggleBtn && filtersPanel) {
        toggleBtn.addEventListener('click', function() {
            filtersPanel.classList.toggle('hidden');
        });
    }

    // Location dropdowns
    initializeLocationDropdowns('city_id', 'district_id');

});

function initializeLocationDropdowns(citySelectId, districtSelectId) {
    const citySelect = document.getElementById(citySelectId);
    const districtSelect = document.getElementById(districtSelectId);

    if (!citySelect || !districtSelect) return;

    citySelect.addEventListener('change', function() {
        const cityId = this.value;
        districtSelect.innerHTML = '<option value="">{{ __('categories.select_district') }}</option>';

        if (cityId) {
            fetch(`/api/cities/${cityId}/districts`)
                .then(response => response.json())
                .then(districts => {
                    districts.forEach(district => {
                        const option = document.createElement('option');
                        option.value = district.id;
                        option.textContent = district.name;
                        districtSelect.appendChild(option);
                    });
                });
        }
    });
}
</script>
@endpush
