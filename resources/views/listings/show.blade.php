@extends('layouts.app')

{{-- SEO Meta Tags --}}
@section('title', App\Helpers\SeoHelper::generateListingTitle($listing))
@section('meta_description', App\Helpers\SeoHelper::generateListingMetaDescription($listing))
@section('meta_keywords', App\Helpers\SeoHelper::generateListingKeywords($listing))
@section('canonical', route('listings.show', $listing->slug))

{{-- Open Graph Tags --}}
@section('og_type', 'product')
@section('og_title', $listing->title)
@section('og_description', App\Helpers\SeoHelper::generateMetaDescription($listing->description, 200))
@section('og_url', route('listings.show', $listing->slug))
@if ($listing->image)
    @section('og_image', listing_image_url($listing->image, 'large'))
    @section('twitter_image', listing_image_url($listing->image, 'large'))
@endif

{{-- Structured Data — pushed onto the jsonld stack that the base
     layout already renders inside <head>. Product + Breadcrumb are
     the two Google rich results that actually show in the SERP for
     real-estate listings. --}}
@push('jsonld')
<script type="application/ld+json">
{!! json_encode(
    App\Helpers\SeoHelper::generateListingSchema($listing),
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
) !!}
</script>
<script type="application/ld+json">
{!! json_encode(
    App\Helpers\SeoHelper::generateBreadcrumbForListing($listing),
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
) !!}
</script>
{{-- VideoObject — ilanda video varsa Google sonuçlarında video
     küçük resmi çıkabilsin diye. Alanlar gerçekten dolu olduğunda
     basılır; eksik veriyle şema yazılmaz. --}}
@php
    $videoEmbedUrl = \App\Services\VideoEmbedService::embedUrl($listing->video_url);
    $videoFileUrl  = $listing->video_file ? asset($listing->video_file) : null;
    $videoSchema   = null;

    if ($videoEmbedUrl || $videoFileUrl) {
        $videoSchema = array_filter([
            '@context'     => 'https://schema.org',
            '@type'        => 'VideoObject',
            'name'         => $listing->title,
            'description'  => \Illuminate\Support\Str::limit(strip_tags((string) $listing->description), 200),
            'thumbnailUrl' => $listing->image ? listing_image_url($listing->image, 'large', 'crop') : null,
            'uploadDate'   => optional($listing->created_at)->toIso8601String(),
            'embedUrl'     => $videoEmbedUrl,
            'contentUrl'   => $videoEmbedUrl ? null : $videoFileUrl,
        ], fn ($value) => $value !== null && $value !== '');
    }
@endphp
@if($videoSchema)
<script type="application/ld+json">
{!! json_encode($videoSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif
@endpush

@section('content')
    <section class="pt-12 pb-12 bg-white">
        <div class="max-w-[1320px] mx-auto px-6">
            {{-- Görünen breadcrumb — SEO + UX ikili sinyal. Kullanıcı
                 önce nereye ait olduğunu görüyor, sonra tek tıkla
                 kategori sayfasına dönüyor. --}}
            @php
                $listingCategoryName = optional(optional($listing->category)?->getCurrentDescription())->name;
                $listingBreadcrumbs = [
                    ['name' => __('general.home'), 'url' => route('home')],
                ];
                if ($listing->category && $listingCategoryName) {
                    try {
                        $listingBreadcrumbs[] = [
                            'name' => $listingCategoryName,
                            'url'  => route('categories.show', $listing->category->slug),
                        ];
                    } catch (\Throwable $e) { /* fallback slug missing */ }
                }
                $listingBreadcrumbs[] = ['name' => $listing->title];
            @endphp
            <x-breadcrumbs :items="$listingBreadcrumbs" />

            <!-- Image Gallery -->
            {{-- Galeri.
                 Kutu sabit yükseklikte değil: fotoğrafın kendi oranına göre
                 şekillenir. Dikey fotoğrafta dar ve uzun, yatayda geniş ve
                 alçak olur. Böylece hiçbir görsel kırpılmaz, gerilmez ve
                 kenarlarda boş bant kalmaz. Yükseklik ekranın %70'iyle
                 sınırlı, çok uzun fotoğraflar sayfayı taşırmaz. --}}
            <div class="mb-4 flex justify-center">
            <div class="relative rounded-2xl overflow-hidden" style="background:#0F1F1A; max-width:100%;">
                @if ($listing->images->count() > 0)
                    <img src="{{ listing_image_url($listing->images->first()->image, 'full', 'contain') }}"
                        alt="{{ $listing->title }}" id="main-image"
                        style="display:block; width:auto; height:auto; max-width:100%; max-height:70vh;">
                @elseif($listing->image)
                    <img src="{{ listing_image_url($listing->image, 'full', 'contain') }}" alt="{{ $listing->title }}"
                        id="main-image"
                        style="display:block; width:auto; height:auto; max-width:100%; max-height:70vh;">
                @else
                    <div class="bg-gray-100 flex items-center justify-center" style="width:100%; height:320px;">
                        <i class="ri-image-line text-4xl text-gray-400"></i>
                    </div>
                @endif

                @if ($listing->images->count() > 1)
                    <!-- Navigation Buttons -->
                    <button onclick="previousImage()"
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center bg-white/90 rounded-full hover:bg-white transition-all cursor-pointer shadow-lg">
                        <i class="ri-arrow-left-s-line text-[#1A1A1A] text-2xl"></i>
                    </button>
                    <button onclick="nextImage()"
                        class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center bg-white/90 rounded-full hover:bg-white transition-all cursor-pointer shadow-lg">
                        <i class="ri-arrow-right-s-line text-[#1A1A1A] text-2xl"></i>
                    </button>

                    <!-- Image Counter -->
                    <div
                        class="absolute bottom-4 right-4 bg-black/70 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                        <span id="current-image-index">1</span> / {{ $listing->images->count() }}
                    </div>
                @endif
            </div>
            </div>

            <!-- Thumbnails Grid -->
            @if ($listing->images->count() > 1)
                <div class="grid grid-cols-5 gap-4">
                    @foreach ($listing->images->take(5) as $index => $image)
                        <div class="relative w-full h-24 rounded-lg overflow-hidden cursor-pointer transition-all {{ $loop->first ? 'ring-4 ring-[#1E6F5C]' : 'opacity-70 hover:opacity-100' }}"
                            onclick="changeMainImageByIndex({{ $index }})">
                            <img loading="lazy" decoding="async" alt="Thumbnail {{ $index + 1 }}" class="w-full h-full object-cover object-center"
                                src="{{ listing_image_url($image->image, 'thumbnail_small', 'crop') }}">
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Video (YouTube/Vimeo embed or self-hosted file) --}}
            @if($listing->video_url || $listing->video_file)
                <div class="mt-6">
                    <h3 class="text-lg font-bold text-[#1A1A1A] mb-3 flex items-center">
                        <i class="ri-vidicon-line mr-2 text-[#1E6F5C]"></i>
                        {{ __('listings.video') }}
                    </h3>
                    <x-video-player :url="$listing->video_url" :file="$listing->video_file" />
                </div>
            @endif
        </div>
    </section>

    <section class="py-12 bg-[#F5F7F8]">
        <div class="max-w-[1320px] mx-auto px-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column (2/3 width) -->
                <div class="lg:col-span-2">
                    <!-- Title, Location and Price Card -->
                    <div class="bg-white rounded-2xl p-8 mb-6 shadow-sm">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3 md:gap-6 mb-4">
                            <div class="flex-1 min-w-0">
                                <h1 class="text-3xl md:text-4xl font-bold text-[#1A1A1A] mb-2"
                                    style="font-family: Inter, sans-serif; font-weight: 700;">
                                    {{ $listing->title }}
                                </h1>
                                <p class="text-lg text-[#1A1A1A]/60 flex items-center"
                                    style="font-family: Inter, sans-serif; font-weight: 400;">
                                    <i class="ri-map-pin-line mr-2"></i>
                                    {{ $listing->city->name }},
                                    {{ $listing->district->name }}{{ $listing->neighborhood ? ', ' . $listing->neighborhood->name : '' }}
                                </p>
                            </div>
                            <div class="text-left md:text-right md:ml-4">
                                <p class="text-2xl md:text-3xl font-bold text-[#1E6F5C] leading-tight"
                                    style="font-family: Inter, sans-serif; font-weight: 700;">
                                    {{ number_format($listing->price, 0) }} ₺
                                </p>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="flex items-center gap-4 pt-6 border-t border-gray-200 flex-wrap">
                            <button onclick="window.print()"
                                class="flex items-center text-[#1A1A1A] hover:text-[#1E6F5C] transition-colors">
                                <i class="ri-printer-line mr-2"></i>
                                <span class="text-sm font-semibold">{{ __('listings.print') }}</span>
                            </button>

                            {{-- Üst action bar telefon linki: kişi
                                 numarası yerine ofisin ayarlardaki
                                 numarasını kullan. --}}
                            @php
                                $barPhone = trim((string) get_setting('contact_phone', ''));
                                $barTel   = $barPhone ? preg_replace('/[^0-9+]/', '', $barPhone) : null;
                            @endphp
                            @if ($barPhone)
                                <a href="tel:{{ $barTel }}"
                                   class="flex items-center text-[#1E6F5C] hover:text-[#13493E] transition-colors">
                                    <i class="ri-phone-line mr-2 text-lg"></i>
                                    <span class="text-sm font-semibold">{{ __('listings.call') }}: {{ $barPhone }}</span>
                                </a>
                            @endif

                            {{-- İlan paylaşımı — 4 kanal: Facebook,
                                 WhatsApp, Instagram (marka profili) ve
                                 Bağlantı Kopyala. Bu, blog show'daki
                                 aynı share paternidir; kullanıcı iki
                                 sayfada da tutarlı ikonlar görür. --}}
                            @php
                                $listingUrl         = route('listings.show', $listing->slug);
                                $listingTitle       = $listing->title;
                                $listingPrice       = number_format($listing->price, 0) . ' ' . __('listings.currency_symbol');
                                $listingShareText   = urlencode($listingTitle . ' — ' . $listingPrice);
                                $listingShareUrl    = urlencode($listingUrl);
                                $listingIgProfile   = trim((string) get_setting('social_instagram_url', ''));
                            @endphp
                            <div class="flex items-center gap-2 ml-auto">
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ $listingShareUrl }}"
                                   target="_blank" rel="noopener"
                                   class="w-9 h-9 rounded-full text-white flex items-center justify-center transition-colors"
                                   style="background: #1877F2;"
                                   onmouseover="this.style.background='#0c5dc7'" onmouseout="this.style.background='#1877F2'"
                                   title="Facebook">
                                    <i class="ri-facebook-fill"></i>
                                </a>
                                <a href="https://wa.me/?text={{ $listingShareText }}%20{{ $listingShareUrl }}"
                                   target="_blank" rel="noopener"
                                   class="w-9 h-9 rounded-full text-white flex items-center justify-center transition-colors"
                                   style="background: #25D366;"
                                   onmouseover="this.style.background='#1ea554'" onmouseout="this.style.background='#25D366'"
                                   title="WhatsApp">
                                    <i class="ri-whatsapp-line"></i>
                                </a>
                                @if($listingIgProfile !== '')
                                    <a href="{{ $listingIgProfile }}"
                                       target="_blank" rel="noopener"
                                       class="w-9 h-9 rounded-full text-white flex items-center justify-center transition-transform"
                                       style="background: linear-gradient(45deg, #F58529 0%, #DD2A7B 40%, #8134AF 75%, #515BD4 100%);"
                                       onmouseover="this.style.transform='scale(1.06)'" onmouseout="this.style.transform='scale(1)'"
                                       title="Instagram">
                                        <i class="ri-instagram-line"></i>
                                    </a>
                                @endif
                                <button type="button"
                                        data-copy-url="{{ $listingUrl }}"
                                        class="copy-link-btn w-9 h-9 rounded-full text-[#1A1A1A] flex items-center justify-center transition-all"
                                        style="background: #F1F5F4;"
                                        onmouseover="this.style.background='#E4EBE8'" onmouseout="this.style.background='#F1F5F4'"
                                        title="{{ __('listings.copy_link') }}">
                                    <i class="ri-link"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Property Stats Grid -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-6 border-t border-gray-200 mt-6">
                            @if ($listing->size)
                                <div class="text-center">
                                    <div
                                        class="w-12 h-12 flex items-center justify-center bg-[#1E6F5C]/10 rounded-lg mx-auto mb-2">
                                        <i class="ri-ruler-line text-[#1E6F5C] text-xl"></i>
                                    </div>
                                    <p class="text-sm text-[#1A1A1A]/60" style="font-family: Inter, sans-serif;">{{ __('listings.area') }}</p>
                                    <p class="text-lg font-bold text-[#1A1A1A]" style="font-family: Inter, sans-serif;">
                                        {{ number_format($listing->size, 0) }} m²</p>
                                </div>
                            @endif

                            @php
                                // Find room count from attributes
                                $roomCount = null;
                                if (isset($groupedAttributes)) {
                                    foreach ($groupedAttributes as $attributeId => $attributeValues) {
                                        $attribute = $attributeValues->first()->attribute ?? null;
                                        if (
                                            $attribute &&
                                            (str_contains(strtolower($attribute->name), 'oda') ||
                                                str_contains(strtolower($attribute->name), 'room'))
                                        ) {
                                            $roomCount = $attributeValues->first()->value ?? null;
                                            break;
                                        }
                                    }
                                }
                            @endphp

                            @if ($roomCount)
                                <div class="text-center">
                                    <div
                                        class="w-12 h-12 flex items-center justify-center bg-[#1E6F5C]/10 rounded-lg mx-auto mb-2">
                                        <i class="ri-door-open-line text-[#1E6F5C] text-xl"></i>
                                    </div>
                                    <p class="text-sm text-[#1A1A1A]/60" style="font-family: Inter, sans-serif;">{{ __('listings.room_count') }}
                                    </p>
                                    <p class="text-lg font-bold text-[#1A1A1A]" style="font-family: Inter, sans-serif;">
                                        {{ $roomCount }}</p>
                                </div>
                            @endif

                            @php
                                // Find floor from attributes
                                $floor = null;
                                if (isset($groupedAttributes)) {
                                    foreach ($groupedAttributes as $attributeId => $attributeValues) {
                                        $attribute = $attributeValues->first()->attribute ?? null;
                                        if (
                                            $attribute &&
                                            (str_contains(strtolower($attribute->name), 'kat') ||
                                                str_contains(strtolower($attribute->name), 'floor'))
                                        ) {
                                            $floor = $attributeValues->first()->value ?? null;
                                            break;
                                        }
                                    }
                                }
                            @endphp

                            @if ($floor)
                                <div class="text-center">
                                    <div
                                        class="w-12 h-12 flex items-center justify-center bg-[#1E6F5C]/10 rounded-lg mx-auto mb-2">
                                        <i class="ri-building-line text-[#1E6F5C] text-xl"></i>
                                    </div>
                                    <p class="text-sm text-[#1A1A1A]/60" style="font-family: Inter, sans-serif;">{{ __('listings.floor') }}</p>
                                    <p class="text-lg font-bold text-[#1A1A1A]" style="font-family: Inter, sans-serif;">
                                        {{ $floor }}</p>
                                </div>
                            @endif

                            @php
                                // Find bathroom count from attributes
                                $bathroomCount = null;
                                if (isset($groupedAttributes)) {
                                    foreach ($groupedAttributes as $attributeId => $attributeValues) {
                                        $attribute = $attributeValues->first()->attribute ?? null;
                                        if (
                                            $attribute &&
                                            (str_contains(strtolower($attribute->name), 'banyo') ||
                                                str_contains(strtolower($attribute->name), 'bathroom'))
                                        ) {
                                            $bathroomCount = $attributeValues->first()->value ?? null;
                                            break;
                                        }
                                    }
                                }
                            @endphp

                            @if ($bathroomCount)
                                <div class="text-center">
                                    <div
                                        class="w-12 h-12 flex items-center justify-center bg-[#1E6F5C]/10 rounded-lg mx-auto mb-2">
                                        <i class="ri-drop-line text-[#1E6F5C] text-xl"></i>
                                    </div>
                                    <p class="text-sm text-[#1A1A1A]/60" style="font-family: Inter, sans-serif;">{{ __('listings.bathroom') }}</p>
                                    <p class="text-lg font-bold text-[#1A1A1A]" style="font-family: Inter, sans-serif;">
                                        {{ $bathroomCount }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Description Card -->
                    <div class="bg-white rounded-2xl p-8 mb-6 shadow-sm">
                        <h2 class="text-2xl font-bold text-[#1A1A1A] mb-4"
                            style="font-family: Inter, sans-serif; font-weight: 700;">{{ __('listings.description') }}</h2>
                        <div class="text-base text-[#1A1A1A]/70 leading-relaxed"
                            style="font-family: Inter, sans-serif; font-weight: 400;">
                            {!! nl2br(e($listing->description)) !!}
                        </div>
                    </div>

                    <!-- Features Card -->
                    @if ($bottomAttributes->count() > 0)
                        @php
                            // Group attributes by name to handle duplicates - only for bottom attributes
                            $attributesByName = [];

                            // Process select/radio/checkbox attributes - only bottom ones
                            if (isset($groupedAttributes)) {
                                foreach ($groupedAttributes as $attributeId => $attributeValues) {
                                    $attribute = $attributeValues->first()->attribute ?? null;
                                    if ($attribute && $bottomAttributes->contains('id', $attribute->id)) {
                                        $attributesByName[$attribute->name] = $attributesByName[$attribute->name] ?? [];

                                        foreach ($attributeValues as $attributeValue) {
                                            $attributesByName[$attribute->name][] = [
                                                'type' => $attribute->display_type_search,
                                                'value' => $attributeValue->value,
                                                'is_custom' => false,
                                            ];
                                        }
                                    }
                                }
                            }

                            // Process custom attributes - only bottom ones
                            if (isset($customAttributes)) {
                                foreach ($customAttributes as $attributeId => $customValues) {
                                    $customValue = $customValues->first();
                                    $attribute = $customValue->attribute ?? null;
                                    if ($attribute && $bottomAttributes->contains('id', $attribute->id)) {
                                        $attributesByName[$attribute->name] = $attributesByName[$attribute->name] ?? [];

                                        $attributesByName[$attribute->name][] = [
                                            'type' => $attribute->display_type_search,
                                            'value' => $customValue->value,
                                            'is_custom' => true,
                                        ];
                                    }
                                }
                            }

                            // Filter for checkbox type features only
                            $checkboxFeatures = [];
                            foreach ($attributesByName as $attributeName => $attributeData) {
                                if (
                                    !empty($attributeData) &&
                                    isset($attributeData[0]['type']) &&
                                    $attributeData[0]['type'] === 'checkbox'
                                ) {
                                    $checkboxFeatures[$attributeName] = $attributeData;
                                }
                            }
                        @endphp

                        @if (count($checkboxFeatures) > 0)
                            <div class="bg-white rounded-2xl p-8 mb-6 shadow-sm">
                                <h2 class="text-2xl font-bold text-[#1A1A1A] mb-6"
                                    style="font-family: Inter, sans-serif; font-weight: 700;">{{ __('listings.features') }}</h2>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                    @foreach ($checkboxFeatures as $attributeName => $attributeData)
                                        @foreach ($attributeData as $item)
                                            <div class="flex items-center">
                                                <div
                                                    class="w-6 h-6 flex items-center justify-center bg-[#1E6F5C]/10 rounded mr-3">
                                                    <i class="ri-check-line text-[#1E6F5C] text-sm"></i>
                                                </div>
                                                <span class="text-sm text-[#1A1A1A]"
                                                    style="font-family: Inter, sans-serif; font-weight: 500;">{{ $item['value'] }}</span>
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif

                    <!-- Details Card -->
                    <div class="bg-white rounded-2xl p-8 shadow-sm">
                        <h2 class="text-2xl font-bold text-[#1A1A1A] mb-6"
                            style="font-family: Inter, sans-serif; font-weight: 700;">{{ __('listings.details') }}</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex items-center justify-between py-3 border-b border-gray-200">
                                <span class="text-sm text-[#1A1A1A]/60" style="font-family: Inter, sans-serif;">{{ __('listings.listing_no') }}</span>
                                <span class="text-sm font-semibold text-[#1A1A1A]"
                                    style="font-family: Inter, sans-serif;">#{{ $listing->id }}</span>
                            </div>

                            <div class="flex items-center justify-between py-3 border-b border-gray-200">
                                <span class="text-sm text-[#1A1A1A]/60" style="font-family: Inter, sans-serif;">{{ __('listings.listing_date') }}</span>
                                <span class="text-sm font-semibold text-[#1A1A1A]"
                                    style="font-family: Inter, sans-serif;">{{ $listing->created_at->translatedFormat('d F Y') }}</span>
                            </div>

                            @php
                                // Get building age, heating, furnished from attributes
                                $buildingAge = null;
                                $heating = null;
                                $furnished = null;

                                if (isset($topAttributes)) {
                                    foreach ($topAttributes as $attribute) {
                                        $attributeValues =
                                            $groupedAttributes->get($attribute->id, collect()) ?? collect();
                                        $customValues = $customAttributes->get($attribute->id, collect()) ?? collect();

                                        $value = null;
                                        if ($attributeValues->count() > 0) {
                                            $value = $attributeValues->first()->value;
                                        } elseif ($customValues->count() > 0) {
                                            $value = $customValues->first()->value;
                                        }

                                        if ($value) {
                                            $attrName = strtolower($attribute->name);
                                            if (
                                                str_contains($attrName, 'yaş') ||
                                                str_contains($attrName, 'age') ||
                                                str_contains($attrName, 'yapım')
                                            ) {
                                                $buildingAge = $value;
                                            } elseif (
                                                str_contains($attrName, 'ısıtma') ||
                                                str_contains($attrName, 'heating')
                                            ) {
                                                $heating = $value;
                                            } elseif (
                                                str_contains($attrName, 'eşyalı') ||
                                                str_contains($attrName, 'furnished')
                                            ) {
                                                $furnished = $value;
                                            }
                                        }
                                    }
                                }
                            @endphp

                            @if ($buildingAge)
                                <div class="flex items-center justify-between py-3 border-b border-gray-200">
                                    <span class="text-sm text-[#1A1A1A]/60" style="font-family: Inter, sans-serif;">{{ __('listings.building_age') }}</span>
                                    <span class="text-sm font-semibold text-[#1A1A1A]"
                                        style="font-family: Inter, sans-serif;">{{ $buildingAge }}</span>
                                </div>
                            @endif

                            @if ($heating)
                                <div class="flex items-center justify-between py-3 border-b border-gray-200">
                                    <span class="text-sm text-[#1A1A1A]/60"
                                        style="font-family: Inter, sans-serif;">{{ __('listings.heating') }}</span>
                                    <span class="text-sm font-semibold text-[#1A1A1A]"
                                        style="font-family: Inter, sans-serif;">{{ $heating }}</span>
                                </div>
                            @endif

                            @if ($furnished)
                                <div class="flex items-center justify-between py-3 border-b border-gray-200">
                                    <span class="text-sm text-[#1A1A1A]/60"
                                        style="font-family: Inter, sans-serif;">{{ __('listings.furnished') }}</span>
                                    <span class="text-sm font-semibold text-[#1A1A1A]"
                                        style="font-family: Inter, sans-serif;">{{ $furnished }}</span>
                                </div>
                            @endif

                            @if (isset($listing->takas))
                                <div class="flex items-center justify-between py-3 border-b border-gray-200">
                                    <span class="text-sm text-[#1A1A1A]/60"
                                        style="font-family: Inter, sans-serif;">{{ __('listings.exchange') }}</span>
                                    <span class="text-sm font-semibold text-[#1A1A1A]"
                                        style="font-family: Inter, sans-serif;">{{ $listing->takas ? __('listings.yes') : __('listings.no') }}</span>
                                </div>
                            @endif

                            {{-- İlan danışmanı — tek ofis kimliğiyle sabit
                                 gösterilir. Yönetici hesabının "Admin User"
                                 adı veya kişisel avatarı asla ön yüze sızmaz. --}}
                            <div class="flex items-center justify-between py-3 border-b border-gray-200">
                                <span class="text-sm text-[#1A1A1A]/60"
                                    style="font-family: Inter, sans-serif;">{{ __('listings.from_whom') }}</span>
                                <span class="text-sm font-semibold text-[#1A1A1A]"
                                    style="font-family: Inter, sans-serif;">Real Estate CMS Demo</span>
                            </div>
                        </div>

                    </div>

                    <div class="bg-white rounded-2xl p-8 shadow-sm mt-6">
                        <h2 class="text-2xl font-bold text-[#1A1A1A] mb-6"
                            style="font-family: Inter, sans-serif; font-weight: 700;">{{ __('listings.location') }}</h2>
                        <div class="p-3 bg-[#1E6F5C]/10 border border-[#1E6F5C]/20 rounded-lg mb-4">
                            <div class="flex items-center">
                                <i class="ri-map-pin-line text-[#1E6F5C] mr-2"></i>
                                <span class="text-sm text-[#1A1A1A]" style="font-family: Inter, sans-serif;">
                                    {{ __('listings.location_label') }}: {{ $listing->city->name }},
                                    {{ $listing->district->name }}{{ $listing->neighborhood ? ', ' . $listing->neighborhood->name : '' }}
                                </span>
                            </div>
                        </div>

                        {{-- Interactive map. Uses Google Maps when an API key is
                             configured, otherwise falls back to Leaflet so the
                             feature still works on installs without a key. --}}
                        @if($listing->latitude && $listing->longitude)
                            <x-map-display
                                :latitude="$listing->latitude"
                                :longitude="$listing->longitude"
                                :title="$listing->title"
                                :zoom="config('services.map.zoom.listing', 16)" />
                        @endif
                    </div>

                    {{-- Yasal bilgilendirme.
                         Taşınmaz Ticareti Hakkında Yönetmelik m.14/2-(i):
                         ilanlarda yetki belgesi numarasına ve yetki
                         belgesindeki işletme adı/unvanına kolay okunabilir
                         şekilde yer verilir. Ayarlar boşsa satır hiç
                         basılmaz — uydurma numara gösterilmez. --}}
                    @php
                        $legalTitle = trim((string) get_setting('legal_company_title', ''));
                        $legalNo    = trim((string) get_setting('legal_authorization_no', ''));
                    @endphp
                    <div class="bg-white rounded-2xl p-6 mb-6 shadow-sm border border-[#E6EDEB]">
                        <div class="flex items-start gap-3">
                            <i class="ri-shield-check-line text-[#1E6F5C] text-xl mt-0.5" aria-hidden="true"></i>
                            <div class="text-sm text-[#1A1A1A]/70 leading-relaxed" style="font-family: Inter, sans-serif;">
                                <p class="font-semibold text-[#1A1A1A] mb-1">{{ __('listings.legal_notice_title') }}</p>
                                <p>{{ __('listings.legal_notice_body') }}</p>
                                @if($legalNo !== '')
                                    <p class="mt-2 text-[#1A1A1A]/80">
                                        @if($legalTitle !== ''){{ $legalTitle }}@endif
                                        @if($legalTitle !== '' && $legalNo !== '') &middot; @endif
                                        @if($legalNo !== ''){{ __('listings.authorization_no') }}: {{ $legalNo }}@endif
                                    </p>
                                @endif
                                <a href="{{ route('pages.show', 'yasal-bilgilendirme') }}"
                                   class="inline-flex items-center gap-1 mt-2 text-[#1E6F5C] font-medium hover:underline">
                                    {{ __('listings.legal_notice_link') }}<i class="ri-arrow-right-s-line"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Sidebar (1/3 width) -->
                <div class="lg:col-span-1">
                    {{-- Danışman kartı.
                         İsim ve fotoğraf ilanın bağlı olduğu danışmanın
                         profilinden gelir; panelden yüklenen fotoğraf burada
                         görünür. Ayarlarda advisor_name / advisor_photo
                         doluysa onlar öncelik alır (profilden bağımsız,
                         kurumsal bir görsel kullanmak istenirse). --}}
                    @php
                        $brandTitle   = trim((string) get_setting('site_title', 'Real Estate CMS Demo')) ?: 'Real Estate CMS Demo';
                        $brandPhone   = trim((string) get_setting('contact_phone', '')) ?: null;
                        $brandEmail   = trim((string) get_setting('contact_email', '')) ?: null;
                        // WhatsApp ayrı bir numara olarak girilmediyse ofis
                        // telefonuna düşer; böylece düğme her zaman görünür.
                        $brandWA      = trim((string) get_setting('contact_whatsapp', '')) ?: $brandPhone;

                        $advisor      = $listing->user;
                        $advisorFull  = $advisor ? trim($advisor->first_name.' '.$advisor->last_name) : '';
                        $advisorName  = trim((string) get_setting('advisor_name', '')) ?: ($advisorFull ?: $brandTitle);

                        $photoSetting = trim((string) get_setting('advisor_photo', ''));
                        $advisorPhoto = $photoSetting !== ''
                            ? asset($photoSetting)
                            : ($advisor ? $advisor->avatar_large_url : asset('images/demo-avatar.svg'));
                        $telHref      = $brandPhone ? preg_replace('/[^0-9+]/', '', $brandPhone) : null;
                        $waHref       = $brandWA ? preg_replace('/[^0-9]/', '', $brandWA) : null;
                    @endphp
                    <div class=" rounded-2xl p-8 shadow-sm sticky top-28">
                        <div class="bg-white rounded-2xl p-8 shadow-sm">
                        {{-- Advisor identity — code-pinned corporate name
                             and portrait for a consistent public listing. --}}
                        <div class="text-center mb-6">
                            <div class="mx-auto mb-4 overflow-hidden rounded-full"
                                 style="width: 5.5rem; height: 5.5rem; border: 4px solid #fff; box-shadow: 0 10px 24px -10px rgba(15,31,26,.32); background:#EAF2EE;">
                                <img loading="lazy" decoding="async" src="{{ $advisorPhoto }}" alt="{{ $advisorName }}" width="88" height="88"
                                     style="display:block; width:100%; height:100%; object-fit:cover; object-position:top;">
                            </div>
                            <h3 class="text-xl font-bold text-[#1A1A1A] mb-1"
                                style="font-family: Inter, sans-serif; font-weight: 700; letter-spacing: -0.01em;">
                                {{ $advisorName }}
                            </h3>
                            <p class="text-sm text-[#1A1A1A]/60" style="font-family: Inter, sans-serif;">
                                {{ __('listings.real_estate_consultant') }} · {{ $brandTitle }}
                            </p>
                        </div>

                        <div class="space-y-3 mb-6">
                            @if ($brandPhone)
                                <a href="tel:{{ $telHref }}"
                                   class="flex items-center justify-center w-full px-6 py-3 bg-[#1E6F5C] text-white rounded-xl hover:bg-[#13493E] transition-all cursor-pointer whitespace-nowrap"
                                   style="font-family: Inter, sans-serif; font-weight: 600;">
                                    <i class="ri-phone-line mr-2"></i>{{ __('listings.call') }}: {{ $brandPhone }}
                                </a>
                            @endif

                            @if ($brandWA && $waHref)
                                <a href="https://wa.me/{{ $waHref }}?text={{ urlencode(__('listings.wa_prefill', ['title' => $listing->title])) }}"
                                   target="_blank" rel="noopener"
                                   class="flex items-center justify-center w-full px-6 py-3 rounded-xl transition-all cursor-pointer whitespace-nowrap"
                                   style="font-family: Inter, sans-serif; font-weight: 600; background: #25D366; color: #fff;">
                                    <i class="ri-whatsapp-line mr-2"></i>WhatsApp
                                </a>
                            @endif

                            @if ($brandEmail)
                                <a href="mailto:{{ $brandEmail }}?subject={{ urlencode('İlan: ' . $listing->title) }}"
                                    class="flex items-center justify-center w-full px-6 py-3 bg-[#F5F7F8] text-[#1A1A1A] rounded-xl hover:bg-[#E8EAEB] transition-all cursor-pointer whitespace-nowrap"
                                    style="font-family: Inter, sans-serif; font-weight: 600;">
                                    <i class="ri-mail-line mr-2"></i>{{ __('listings.send_email') }}
                                </a>
                            @endif

                        </div>

                        <div class="pt-6 border-t border-gray-200">
                            <p class="text-xs text-[#1A1A1A]/50 text-center"
                               style="font-family: Inter, sans-serif;">{{ __('listings.will_contact_soon') }}</p>
                        </div>
                    </div>

                    <!-- Similar Listings -->
                    @if (isset($similarListings) && $similarListings->count() > 0)
                        <div class="bg-white rounded-2xl p-8 shadow-sm mt-6">
                            <h3 class="text-xl font-bold text-[#1A1A1A] mb-4"
                                style="font-family: Inter, sans-serif; font-weight: 700;">{{ __('listings.similar_listings') }}</h3>
                            <div class="space-y-4">
                                @foreach ($similarListings as $similarListing)
                                    <a href="{{ route('listings.show', $similarListing->slug) }}" class="flex gap-3 group">
                                        <div class="w-20 h-20 rounded-lg overflow-hidden flex-shrink-0 border">
                                            @if ($similarListing->image)
                                                <img loading="lazy" decoding="async" src="{{ listing_image_url($similarListing->image, 'thumbnail_small', 'crop') }}"
                                                     alt="{{ $similarListing->title }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full bg-gray-100 flex items-center justify-center">
                                                    <i class="ri-image-line text-gray-400"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-medium text-sm line-clamp-2 group-hover:text-[#1E6F5C] transition-colors"
                                                style="font-family: Inter, sans-serif;">
                                                {{ $similarListing->title }}
                                            </h4>
                                            <p class="text-[#1E6F5C] text-sm font-medium mt-1"
                                               style="font-family: Inter, sans-serif;">
                                                {{ number_format($similarListing->price, 0) }} ₺
                                            </p>
                                            <p class="text-xs text-[#1A1A1A]/60 mt-1"
                                               style="font-family: Inter, sans-serif;">
                                                {{ $similarListing->city->name }}, {{ $similarListing->district->name }}
                                            </p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>


                </div>
            </div>
        </div>
    </section>

@endsection

@push('scripts')
    <script>
        let currentImageIndex = 0;
        const listingImages = [
            @if ($listing->images->count() > 0)
                @foreach ($listing->images as $image)
                    {
                        large: '{{ listing_image_url($image->image, 'full', 'contain') }}',
                        full:  '{{ listing_image_url($image->image, 'full', 'contain') }}'
                    }
                    {{ !$loop->last ? ',' : '' }}
                @endforeach
            @elseif ($listing->image) {
                    large: '{{ listing_image_url($listing->image, 'full', 'contain') }}',
                    full:  '{{ listing_image_url($listing->image, 'full', 'contain') }}'
                }
            @endif
        ];

        function changeMainImageByIndex(index) {
            if (listingImages[index]) {
                currentImageIndex = index;
                document.getElementById('main-image').src = listingImages[index].large;
                document.getElementById('current-image-index').textContent = index + 1;

                // Update thumbnail active state
                document.querySelectorAll('.grid.grid-cols-5 > div').forEach((thumb, idx) => {
                    if (idx === index) {
                        thumb.classList.add('ring-4', 'ring-[#1E6F5C]');
                        thumb.classList.remove('opacity-70');
                    } else {
                        thumb.classList.remove('ring-4', 'ring-[#1E6F5C]');
                        thumb.classList.add('opacity-70');
                    }
                });
            }
        }

        function previousImage() {
            if (currentImageIndex > 0) {
                changeMainImageByIndex(currentImageIndex - 1);
            } else {
                changeMainImageByIndex(listingImages.length - 1);
            }
        }

        function nextImage() {
            if (currentImageIndex < listingImages.length - 1) {
                changeMainImageByIndex(currentImageIndex + 1);
            } else {
                changeMainImageByIndex(0);
            }
        }

        // Legacy function for thumbnail clicks
        function changeMainImage(thumbnail, imageSrc) {
            const index = listingImages.findIndex(img => img.large === imageSrc || img.full === imageSrc);
            if (index !== -1) {
                changeMainImageByIndex(index);
            }
        }

        // Lightbox gallery function
        function openLightboxGallery() {
            let lightboxContainer = document.getElementById('lightbox-gallery');
            if (!lightboxContainer) {
                lightboxContainer = document.createElement('div');
                lightboxContainer.id = 'lightbox-gallery';
                lightboxContainer.style.display = 'none';
                document.body.appendChild(lightboxContainer);

                listingImages.forEach((img, index) => {
                    const link = document.createElement('a');
                    link.href = img.full;
                    link.setAttribute('data-lightbox', 'listing-gallery');
                    link.setAttribute('data-title', '{{ $listing->title }} - {{ __("listings.image") }} ' + (index + 1));
                    lightboxContainer.appendChild(link);
                });
            }

            // Open current image in lightbox
            const links = lightboxContainer.querySelectorAll('a[data-lightbox="listing-gallery"]');
            if (links[currentImageIndex]) {
                links[currentImageIndex].click();
            } else if (links[0]) {
                links[0].click();
            }
        }

        // DOM ready event
        document.addEventListener('DOMContentLoaded', function() {
            // Configure lightbox options
            if (typeof lightbox !== 'undefined') {
                lightbox.option({
                    'resizeDuration': 200,
                    'wrapAround': true,
                    'showImageNumberLabel': true,
                    'albumLabel': '{{ __("listings.image_label") }}',
                    'maxWidth': 1200,
                    'maxHeight': 800
                });
            }

        });

        // Bağlantıyı kopyala (share strip). Blog show ile aynı davranış:
        // 1.2 sn yeşil flash → başarılı geri bildirim.
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
                        window.prompt('{{ __('listings.copy_link') }}', url);
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
