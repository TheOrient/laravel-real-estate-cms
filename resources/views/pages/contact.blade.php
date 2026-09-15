@extends('layouts.app')

@php
    $contactPageTitle = \App\Helpers\BrandHelper::render($page->title);
    $contactPageBrand = \App\Helpers\BrandHelper::render(get_setting('site_title', config('app.name')));
    $contactPageDescription = \App\Helpers\BrandHelper::render($page->meta_description ?: $contactPageTitle);
    $contactPageUrl = route('pages.show', $page->slug);
@endphp

@section('title', $contactPageTitle . ' | ' . $contactPageBrand)
@section('canonical', $contactPageUrl)
@section('og_title', $contactPageTitle . ' | ' . $contactPageBrand)
@section('og_description', $contactPageDescription)
@section('og_url', $contactPageUrl)

@section('meta_description', $contactPageDescription)

@push('jsonld')
<script type="application/ld+json">
{!! json_encode(App\Helpers\SeoHelper::generateBreadcrumbSchema([
    ['name' => __('general.home'), 'url' => route('home')],
    ['name' => $contactPageTitle, 'url' => $contactPageUrl],
]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@php
    // Office location — configurable through /admin/settings so the map
    // stays truthful if the agency moves. Falls back to Kuşadası
    // (Türkmen) defaults if the settings are unset.
    $savedOfficeLat = trim((string) get_setting('office_latitude', ''));
    $savedOfficeLng = trim((string) get_setting('office_longitude', ''));
    $hasExactOfficeLocation = $savedOfficeLat !== '' && $savedOfficeLng !== '';
    $officeLat     = (float) ($savedOfficeLat ?: '37.8579');
    $officeLng     = (float) ($savedOfficeLng ?: '27.2610');
    $officeAddr    = trim((string) get_setting('contact_address', 'Kuşadası / Aydın')) ?: 'Kuşadası / Aydın';
    $officePhone   = trim((string) get_setting('contact_phone', ''));
    $officeEmail   = trim((string) get_setting('contact_email',   ''));
    // WhatsApp ayrı girilmediyse ofis telefonuna düşer.
    $officeWa      = trim((string) get_setting('contact_whatsapp', '')) ?: $officePhone;
    $contactWaHref = $officeWa    ? preg_replace('/[^0-9]/',  '', $officeWa)    : null;
    $contactTelHref= $officePhone ? preg_replace('/[^0-9+]/', '', $officePhone) : null;
    $officeName    = trim((string) get_setting('site_title',      'Real Estate CMS Demo'));
@endphp


@section('content')
<div style="background: #F5F7F8;">
    {{-- Hero — matches the new page show / blog index treatment --}}
    <section style="position: relative; overflow: hidden; background: linear-gradient(135deg, #1E6F5C 0%, #155946 60%, #0F1F1A 100%);">
        <div style="position: absolute; inset: 0; opacity: .10; background-image: radial-gradient(circle at 22% 28%, white 1px, transparent 1.5px), radial-gradient(circle at 78% 72%, white 1px, transparent 1.5px); background-size: 56px 56px; pointer-events: none;"></div>
        <div style="position: relative; max-width: 1100px; margin: 0 auto; padding: 5rem 1.5rem 5.5rem;">
            <div style="text-align: center;">
                <span style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.35rem 1rem; border-radius: 999px; background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.22); color: #fff; font-size: 0.7rem; font-weight: 800; letter-spacing: 0.12em; margin-bottom: 1.25rem; text-transform: uppercase;">
                    <i class="ri-customer-service-2-line"></i> {{ $contactPageBrand }}
                </span>
                <h1 style="font-family: Inter, sans-serif; font-weight: 800; color: #fff; font-size: clamp(2rem, 4.5vw, 3.25rem); line-height: 1.1; margin: 0 0 1rem; text-shadow: 0 4px 24px rgba(0,0,0,0.30);">
                    {{ $page->title }}
                </h1>
                @if($page->meta_description)
                    <p style="max-width: 36rem; margin: 0 auto; font-size: 1.05rem; line-height: 1.55; color: rgba(255,255,255,0.85); text-shadow: 0 2px 10px rgba(0,0,0,0.30);">
                        {{ $page->meta_description }}
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

    <!-- Contact Content -->
    <div style="padding: 3rem 1.5rem 5rem;">
        <div style="max-width: 1100px; margin: 0 auto;">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                <!-- Contact Information -->
                <div class="prose prose-lg max-w-none" style="background: #fff; border-radius: 1.25rem; padding: 2rem; box-shadow: 0 2px 4px rgba(15,31,26,0.04), 0 4px 16px rgba(15,31,26,0.06);">
                    @if($page->content)
                        {!! $page->content !!}
                    @endif
                </div>

                <!-- Doğrudan İletişim — form yok, tek tıkla WhatsApp / telefon -->
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <h3 class="text-2xl font-bold text-[#1A1A1A] mb-2" style="font-family: Inter, sans-serif;">{{ __('contact.direct_title') }}</h3>
                    <p class="text-sm text-[#5B6770] mb-6" style="font-family: Inter, sans-serif; line-height: 1.6;">
                        {{ __('contact.direct_subtitle') }}
                    </p>

                    <div class="space-y-3">
                        @if ($contactWaHref)
                            <a href="https://wa.me/{{ $contactWaHref }}?text={{ urlencode(__('contact.wa_prefill', ['site' => $officeName])) }}"
                               target="_blank" rel="noopener"
                               class="flex items-center justify-center w-full px-6 py-4 rounded-xl transition-all cursor-pointer whitespace-nowrap"
                               style="font-family: Inter, sans-serif; font-weight: 700; background: #25D366; color: #fff; box-shadow: 0 8px 20px -10px rgba(37,211,102,.8);">
                                <i class="ri-whatsapp-line mr-2" style="font-size: 1.25rem;"></i>{{ __('contact.wa_button') }}
                            </a>
                        @endif

                        @if ($officePhone && $contactTelHref)
                            <a href="tel:{{ $contactTelHref }}"
                               class="flex items-center justify-center w-full px-6 py-4 bg-[#1E6F5C] text-white rounded-xl hover:bg-[#13493E] transition-all cursor-pointer whitespace-nowrap"
                               style="font-family: Inter, sans-serif; font-weight: 700;">
                                <i class="ri-phone-line mr-2" style="font-size: 1.25rem;"></i>{{ $officePhone }}
                            </a>
                        @endif
                    </div>

                    <div class="mt-7 pt-6" style="border-top: 1px solid #EEF2F1;">
                        <div class="flex items-start gap-3">
                            <span style="flex: 0 0 auto; display: inline-flex; align-items: center; justify-content: center; width: 2.4rem; height: 2.4rem; border-radius: 0.75rem; background: rgba(30,111,92,0.10); color: #1E6F5C;">
                                <i class="ri-map-pin-2-line"></i>
                            </span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-[#5B6770] mb-1" style="font-family: Inter, sans-serif; letter-spacing: .08em;">{{ __('contact.address_label') }}</p>
                                <p class="text-sm text-[#1A1A1A]" style="font-family: Inter, sans-serif; line-height: 1.55;">{{ $officeAddr }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Office location map — full-width below the two-column
                 grid so mobile users see it right after the form and
                 desktop users get a large, immersive view. --}}
            <div style="margin-top: 3rem; background: #fff; border-radius: 1.25rem; overflow: hidden; box-shadow: 0 2px 4px rgba(15,31,26,0.04), 0 4px 16px rgba(15,31,26,0.06);">
                <div style="padding: 1.75rem 1.75rem 1rem;">
                    <span class="section-eyebrow">
                        <i class="ri-map-pin-2-line"></i> {{ $hasExactOfficeLocation ? __('contact.map_eyebrow') : __('contact.map_area_eyebrow') }}
                    </span>
                    <h3 style="font-family: Inter, sans-serif; font-size: 1.5rem; font-weight: 700; color: #1A1A1A; margin: 0.35rem 0 0.4rem; letter-spacing: -0.01em;">
                        {{ $hasExactOfficeLocation ? __('contact.map_title') : __('contact.map_area_title') }}
                    </h3>
                    <p style="color: #5B6770; margin: 0 0 0.75rem;">
                        <i class="ri-map-pin-line" style="color: #1E6F5C; margin-right: 0.35rem;"></i>
                        {{ $officeAddr }}
                    </p>
                    <a href="https://www.openstreetmap.org/?mlat={{ $officeLat }}&mlon={{ $officeLng }}#map=17/{{ $officeLat }}/{{ $officeLng }}"
                       target="_blank" rel="noopener"
                       style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.55rem 1.1rem; border-radius: 999px; background: rgba(30,111,92,0.10); color: #1E6F5C; font-size: 0.85rem; font-weight: 700; text-decoration: none; transition: background .25s ease;"
                       onmouseover="this.style.background='rgba(30,111,92,0.16)'"
                       onmouseout="this.style.background='rgba(30,111,92,0.10)'">
                        <i class="ri-navigation-line"></i> {{ $hasExactOfficeLocation ? __('contact.map_directions') : __('contact.map_area_directions') }}
                    </a>
                </div>
                <x-map-display
                    :latitude="$officeLat"
                    :longitude="$officeLng"
                    :zoom="16"
                    height="420px"
                    :title="$officeName . ' — ' . $officeAddr" />
            </div>
        </div>
    </div>
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

</style>
@endpush
