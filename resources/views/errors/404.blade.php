@extends('layouts.app')

@section('title', __('errors.404_title'))
@section('meta_description', __('errors.404_meta_description'))

@push('head')
<meta name="robots" content="noindex, follow">
@endpush

@section('content')
{{-- SEO uyumlu 404: kullanıcıyı dead-end'de bırakmak yerine popüler
     ilanlara ve arama kutusuna yönlendirir. Google 404'e "soft 404"
     etiketi koymadığı sürece siteye faydası vardır. --}}
@php
    // En son 3 aktif ilan — kullanıcı 404'ten sonra bunlara tıklarsa
    // hem bounce rate düşer hem session süresi uzar.
    try {
        $popularListings = \App\Models\Listing::with(['city', 'district'])
            ->where('status', 'active')
            ->where('is_approved', true)
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->take(3)
            ->get();
    } catch (\Throwable $e) {
        $popularListings = collect();
    }
@endphp

<div style="background: radial-gradient(ellipse 55% 45% at 50% 0%, rgba(30,111,92,0.06), transparent 70%), #F5F7F8; padding: 3rem 1.5rem 4rem;">
    <div class="mx-auto" style="max-width: 900px;">

        {{-- Ana 404 kartı --}}
        <div style="background: #fff; border-radius: 1.5rem; padding: 3rem 2rem; text-align: center; box-shadow: 0 2px 4px rgba(15,31,26,0.04), 0 12px 32px -20px rgba(15,31,26,0.15); border: 1px solid #EEF2EF;">
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 5rem; height: 5rem; border-radius: 1.25rem; background: linear-gradient(135deg, rgba(30,111,92,0.12) 0%, rgba(30,111,92,0.04) 100%); margin: 0 auto 1.5rem;">
                <i class="ri-compass-3-line" style="font-size: 2.5rem; color: #1E6F5C;"></i>
            </div>

            <div style="font-family: Inter, sans-serif; font-size: 5.5rem; font-weight: 800; line-height: 1; color: transparent; background: linear-gradient(135deg, #1E6F5C 0%, #13493E 100%); background-clip: text; -webkit-background-clip: text; letter-spacing: -0.03em; margin-bottom: 0.5rem;">
                404
            </div>
            <h1 style="font-family: Inter, sans-serif; font-size: 1.65rem; font-weight: 700; color: #0F1F1A; margin: 0 0 0.75rem; letter-spacing: -0.01em;">
                {{ __('errors.404_headline') }}
            </h1>
            <p style="font-size: 0.98rem; color: #5B6770; max-width: 500px; margin: 0 auto 1.75rem; line-height: 1.55;">
                {{ __('errors.404_description') }}
            </p>

            {{-- Site içi arama — Google 404 sayfalarında arama önerir. --}}
            <form action="{{ route('categories.show.all') }}" method="GET"
                  style="max-width: 480px; margin: 0 auto 1.25rem; position: relative;">
                <input type="text" name="s"
                       placeholder="{{ __('errors.404_search_placeholder') }}"
                       style="width: 100%; height: 3rem; padding: 0 3rem 0 1.25rem; border-radius: 999px; border: 1px solid #D8DFDC; font-size: 0.95rem; outline: none; background: #F1F5F4; transition: border-color .2s, background .2s;"
                       onfocus="this.style.borderColor='#1E6F5C'; this.style.background='#fff';"
                       onblur="this.style.borderColor='#D8DFDC'; this.style.background='#F1F5F4';">
                <button type="submit" aria-label="{{ __('errors.404_search_placeholder') }}"
                        style="position: absolute; right: 0.35rem; top: 50%; transform: translateY(-50%); width: 2.35rem; height: 2.35rem; border-radius: 50%; background: #1E6F5C; color: #fff; border: 0; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: background .2s;"
                        onmouseover="this.style.background='#155946'" onmouseout="this.style.background='#1E6F5C'">
                    <i class="ri-search-line" style="font-size: 1.1rem;"></i>
                </button>
            </form>

            {{-- Aksiyon butonları --}}
            <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 0.75rem;">
                <a href="{{ route('home') }}"
                   style="display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.7rem 1.35rem; border-radius: 999px; background: #1E6F5C; color: #fff; font-weight: 700; font-size: 0.9rem; text-decoration: none; transition: background .2s, transform .2s;"
                   onmouseover="this.style.background='#155946'; this.style.transform='translateY(-1px)';"
                   onmouseout="this.style.background='#1E6F5C'; this.style.transform='';">
                    <i class="ri-home-3-line"></i>{{ __('errors.404_home') }}
                </a>
                <a href="{{ route('categories.show.all') }}"
                   style="display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.7rem 1.35rem; border-radius: 999px; background: #F1F5F4; color: #0F1F1A; font-weight: 700; font-size: 0.9rem; text-decoration: none; transition: background .2s;"
                   onmouseover="this.style.background='#E4EBE8'" onmouseout="this.style.background='#F1F5F4'">
                    <i class="ri-price-tag-3-line" style="color: #1E6F5C;"></i>{{ __('errors.404_listings') }}
                </a>
                <a href="{{ route('blog.index') }}"
                   style="display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.7rem 1.35rem; border-radius: 999px; background: #F1F5F4; color: #0F1F1A; font-weight: 700; font-size: 0.9rem; text-decoration: none; transition: background .2s;"
                   onmouseover="this.style.background='#E4EBE8'" onmouseout="this.style.background='#F1F5F4'">
                    <i class="ri-article-line" style="color: #1E6F5C;"></i>{{ __('errors.404_blog') }}
                </a>
            </div>
        </div>

        {{-- Popüler ilanlar — internal link + kullanıcı yönlendirme --}}
        @if($popularListings->count() > 0)
            <div style="margin-top: 2.5rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.15rem;">
                    <i class="ri-fire-line" style="color: #1E6F5C; font-size: 1.15rem;"></i>
                    <h2 style="font-family: Inter, sans-serif; font-size: 1.1rem; font-weight: 700; color: #0F1F1A; margin: 0; letter-spacing: -0.01em;">
                        {{ __('errors.404_popular_listings') }}
                    </h2>
                </div>
                <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.85rem;" class="related-grid">
                    @foreach($popularListings as $pl)
                        @php
                            $plCover = $pl->image ? listing_image_url($pl->image, 'medium') : null;
                            $plUrl   = route('listings.show', $pl->slug);
                        @endphp
                        <a href="{{ $plUrl }}"
                           style="display: block; background: #fff; border-radius: 0.85rem; overflow: hidden; text-decoration: none; box-shadow: 0 1px 3px rgba(15,31,26,0.05); border: 1px solid #EEF2EF; transition: transform .3s ease, box-shadow .3s ease;"
                           onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 22px -10px rgba(15,31,26,0.20)';"
                           onmouseout="this.style.transform=''; this.style.boxShadow='0 1px 3px rgba(15,31,26,0.05)';">
                            <div style="width: 100%; height: 130px; background: #F1F5F4; overflow: hidden;">
                                @if($plCover)
                                    <img src="{{ $plCover }}" alt="{{ $pl->title }}" loading="lazy"
                                         style="width: 100%; height: 100%; object-fit: cover; display: block;">
                                @endif
                            </div>
                            <div style="padding: 0.75rem 0.9rem 0.9rem;">
                                <p style="font-size: 0.9rem; font-weight: 700; color: #1E6F5C; margin: 0 0 0.25rem;">
                                    @if($pl->price){{ number_format($pl->price, 0, ',', '.') }} ₺@endif
                                </p>
                                <h3 style="font-family: Inter, sans-serif; font-size: 0.85rem; font-weight: 600; color: #0F1F1A; margin: 0; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    {{ $pl->title }}
                                </h3>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
