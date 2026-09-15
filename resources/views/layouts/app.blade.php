<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light">

    {{-- SEO Meta Tags --}}
    @php
        // Brand-neutral defaults — site owner configures via admin > settings.
        $siteTitle = get_setting('site_title', config('app.name'));
        $siteDescription = get_setting('site_description', __('general.default_meta_description'));
        $defaultOgImage = get_setting('site_og_image');
        if ($defaultOgImage) {
            $defaultOgImage = asset($defaultOgImage);
        } elseif (file_exists(public_path('uploads/settings/home-hero.jpg'))) {
            $defaultOgImage = asset('uploads/settings/home-hero.jpg');
        }
        // Brand tokens ({site_name} etc.) get resolved at render time.
        $siteTitle = \App\Helpers\BrandHelper::render($siteTitle);
        $siteDescription = \App\Helpers\BrandHelper::render($siteDescription);
        $localeForOg = str_replace('-', '_', app()->getLocale()) . '_' . strtoupper(substr(app()->getLocale(), 0, 2));
    @endphp
    <title>@yield('title', $siteTitle)</title>
    <meta name="description" content="@yield('meta_description', $siteDescription)">
    <meta name="keywords" content="@yield('meta_keywords', __('general.default_meta_keywords'))">
    <meta name="author" content="{{ $siteTitle }}">

    {{-- Canonical URL — the English query variant is a crawlable document,
         so it must canonicalize to itself rather than the Turkish page. --}}
    @php
        $canonicalUrl = trim($__env->yieldContent('canonical', url()->current()));
        if (request()->query('lang') === 'en' && !str_contains($canonicalUrl, 'lang=')) {
            $canonicalUrl .= (str_contains($canonicalUrl, '?') ? '&' : '?') . 'lang=en';
        }
    @endphp
    <link rel="canonical" href="{{ $canonicalUrl }}">

    {{-- Open Graph Meta Tags --}}
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('og_title', $siteTitle)">
    <meta property="og:description" content="@yield('og_description', $siteDescription)">
    <meta property="og:url" content="@yield('og_url', url()->current())">
    <meta property="og:site_name" content="{{ $siteTitle }}">
    <meta property="og:locale" content="{{ $localeForOg }}">
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')">
        <meta property="og:image:alt" content="@yield('og_image_alt', $siteTitle)">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
    @elseif($defaultOgImage)
        <meta property="og:image" content="{{ $defaultOgImage }}">
        <meta property="og:image:alt" content="{{ $siteTitle }}">
    @endif

    {{-- Article-only OG properties. Blog show + landing pages hook
         @section('article_published_time') vb.; boşsa hiçbir şey basılmaz. --}}
    @hasSection('article_published_time')
        <meta property="article:published_time" content="@yield('article_published_time')">
    @endif
    @hasSection('article_modified_time')
        <meta property="article:modified_time" content="@yield('article_modified_time')">
    @endif
    @hasSection('article_section')
        <meta property="article:section" content="@yield('article_section')">
    @endif

    {{-- Twitter Card Meta Tags --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('twitter_title', $siteTitle)">
    <meta name="twitter:description" content="@yield('twitter_description', $siteDescription)">
    @hasSection('twitter_image')
        <meta name="twitter:image" content="@yield('twitter_image')">
        <meta name="twitter:image:alt" content="@yield('og_image_alt', $siteTitle)">
    @elseif($defaultOgImage)
        <meta name="twitter:image" content="{{ $defaultOgImage }}">
        <meta name="twitter:image:alt" content="{{ $siteTitle }}">
    @endif
    @php
        $twitterSite = trim((string) get_setting('social_twitter_handle', ''));
    @endphp
    @if($twitterSite !== '')
        <meta name="twitter:site" content="{{ Str::startsWith($twitterSite, '@') ? $twitterSite : '@' . $twitterSite }}">
    @endif

    {{-- Robots (per-page override via @section('robots')) --}}
    <meta name="robots" content="@yield('robots', 'index, follow')">

    {{-- Additional SEO / mobile tags --}}
    <meta name="language" content="{{ app()->getLocale() }}">
    <meta name="theme-color" content="#1E6F5C">
    <meta name="msapplication-TileColor" content="#1E6F5C">
    <meta name="format-detection" content="telephone=no">

    {{-- Performance — DNS + TCP el sıkışmasını sayfa yüklenirken
         paralelize et. Chrome/Safari en önemli 4 domain'e preconnect
         (aktif bağlantı) kurar; diğerleri için ucuz dns-prefetch. --}}
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://images.unsplash.com">
    <link rel="dns-prefetch" href="https://unpkg.com">
    <link rel="dns-prefetch" href="https://basemaps.cartocdn.com">
    <link rel="dns-prefetch" href="https://a.basemaps.cartocdn.com">
    <link rel="dns-prefetch" href="https://b.basemaps.cartocdn.com">
    <link rel="dns-prefetch" href="https://c.basemaps.cartocdn.com">
    <link rel="dns-prefetch" href="https://d.basemaps.cartocdn.com">

    {{-- Favicons + touch icons --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    {{-- Google Search Console doğrulama + Analytics (ayar-tabanlı).
         Değer boşsa hiçbir şey basılmaz; admin > ayarlardan veya
         set_setting() ile doldurulur. --}}
    @php
        $seoVerify = trim((string) get_setting('seo_google_verification', ''));
        $seoGa4    = trim((string) get_setting('seo_ga4_id', ''));
    @endphp
    @if($seoVerify !== '')
        <meta name="google-site-verification" content="{{ $seoVerify }}">
    @endif
    @if($seoGa4 !== '')
        <link rel="preconnect" href="https://www.googletagmanager.com">
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $seoGa4 }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ $seoGa4 }}');
        </script>
    @endif

    {{-- Baseline WebSite + Organization structured data. Individual
         pages (listing, blog show, contact) can add richer JSON-LD via
         their own @push('jsonld'). --}}
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type'    => 'WebSite',
        'name'     => $siteTitle,
        'url'      => url('/'),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    {{-- JSON-LD structured data hook (per-page schemas) --}}
    {{-- LocalBusiness / RealEstateAgent — isim/adres/telefon (NAP) HER
         sayfada. Yerel "Kuşadası emlak" aramaları için güçlü sinyal;
         değerler admin > ayarlardan gelir. --}}
    @php
        $bizPhone = trim((string) get_setting('contact_phone', ''));
        $bizEmail = trim((string) get_setting('contact_email', ''));
        $bizAddr  = trim((string) get_setting('contact_address', ''));
        $bizLat   = trim((string) get_setting('office_latitude', ''));
        $bizLng   = trim((string) get_setting('office_longitude', ''));
        $bizHours = trim((string) get_setting('contact_opening_hours', ''));
        $bizSameAs = array_values(array_filter([
            trim((string) get_setting('social_facebook_url', '')),
            trim((string) get_setting('social_instagram_url', '')),
            trim((string) get_setting('social_youtube_url', '')),
        ]));
        $bizSchema = array_filter([
            '@context'     => 'https://schema.org',
            '@type'        => 'RealEstateAgent',
            '@id'          => url('/') . '#organization',
            'name'         => $siteTitle,
            'url'          => url('/'),
            'image'        => $defaultOgImage ?: null,
            'telephone'    => $bizPhone ?: null,
            'email'        => $bizEmail ?: null,
            'address'      => array_filter([
                '@type'           => 'PostalAddress',
                'streetAddress'   => $bizAddr ?: null,
                'addressLocality' => 'Kuşadası',
                'addressRegion'   => 'Aydın',
                'addressCountry'  => 'TR',
            ], fn ($v) => $v !== null && $v !== ''),
            'geo'          => ($bizLat !== '' && $bizLng !== '') ? [
                '@type' => 'GeoCoordinates', 'latitude' => (float) $bizLat, 'longitude' => (float) $bizLng,
            ] : null,
            'openingHours' => $bizHours ?: null,
            'areaServed'   => 'Kuşadası, Aydın',
            'sameAs'       => $bizSameAs ?: null,
        ], fn ($v) => $v !== null && $v !== '' && $v !== []);
    @endphp
    <script type="application/ld+json">
    {!! json_encode($bizSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    @stack('jsonld')

    <!-- Vite -->
    @vite('resources/css/app.css')
    @if(request()->routeIs('user.listings.*'))
        {{-- The image manager is the only public-layout feature that needs
             the JavaScript bundle (Sortable). Keep it off visitor pages. --}}
        @vite('resources/js/app.js')
    @endif
    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" rel="stylesheet">

    <!-- Lightbox2 CSS -->
    @if(request()->routeIs('listings.show'))
        <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet">
    @endif


    <!-- Alternate Links -->
    <x-head-alternate />

    <!-- Dynamic Head Content (for page-specific scripts/styles) -->
    @stack('head')

    <style>
        :root {
            /* Tailwind theme colors as CSS variables */
            --color-primary: #1E6F5C;
            --color-primary-dark: #13493E;
            --color-primary-soft: #E7F2EF;     /* soft accent fill */
            --color-accent: #F2A65A;           /* warm complementary */
            --color-text: #1A1A1A;
            --color-text-muted: #545B66;
            --color-secondary: #F5F7F8;
            --color-surface: #FFFFFF;
            --color-dark: #0F1F1A;
            --shadow-soft: 0 2px 4px rgba(15, 31, 26, 0.04), 0 4px 12px rgba(15, 31, 26, 0.06);
            --shadow-lift: 0 6px 16px rgba(15, 31, 26, 0.08), 0 12px 32px rgba(15, 31, 26, 0.10);
            --radius-sm: 0.75rem;
            --radius-md: 1rem;
            --radius-lg: 1.5rem;
        }

        /* (legacy ri- icon ::before override removed — was forcing every
            RemixIcon to render as the same checkbox glyph because the
            :where() rule won the cascade in some browsers.) */

        /* Global polish — applied site-wide without touching the existing
           Tailwind class soup. Adds smoother shadows, micro animations,
           and friendlier defaults to buttons and cards. */
        body {
            -webkit-font-smoothing: antialiased;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        a, button { transition: color .2s ease, background-color .2s ease, transform .2s ease, box-shadow .2s ease; }
        .bg-white:not(header):not(nav) {
            box-shadow: var(--shadow-soft);
        }
        .rounded-2xl, .rounded-xl {
            transition: box-shadow .25s ease, transform .25s ease;
        }
        .rounded-2xl:hover { box-shadow: var(--shadow-lift); }
        .btn-primary, button[type="submit"]:not(.no-style) {
            letter-spacing: .01em;
        }
        /* Subtle focus ring */
        input:focus, select:focus, textarea:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(30, 111, 92, 0.15);
            border-color: var(--color-primary) !important;
        }
        /* Selection color match */
        ::selection { background: var(--color-primary); color: #fff; }

        /* ========== Hero search — full pill (oval) ========== */
        .hero-search-pill {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            padding: 0.5rem;
            background: rgba(255, 255, 255, 0.96);
            border-radius: 999px;
            box-shadow:
                0 24px 64px -16px rgba(15, 31, 26, 0.45),
                0 0 0 1px rgba(255, 255, 255, 0.6) inset;
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }
        .hero-search-row {
            display: flex;
            align-items: stretch;
            gap: 0;
        }
        .hero-search-cell {
            flex: 1 1 0;
            min-width: 0;
            padding: 0.6rem 1.25rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border-radius: 999px;
            transition: background-color .2s ease;
        }
        .hero-search-cell:hover {
            background: rgba(30, 111, 92, 0.06);
        }
        .hero-search-divider {
            width: 1px;
            background: linear-gradient(180deg, transparent, rgba(15, 31, 26, 0.12), transparent);
            margin: 0.5rem 0;
            flex-shrink: 0;
        }
        .hero-search-label {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.68rem;
            font-weight: 800;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            margin-bottom: 0.15rem;
        }
        .hero-search-label i {
            color: #1E6F5C;
            font-size: 0.85rem;
        }
        .hero-search-control {
            position: relative;
            display: flex;
            align-items: center;
        }
        .hero-search-control select,
        .hero-search-control input {
            width: 100%;
            padding: 0.2rem 1.25rem 0.2rem 0;
            font-size: 0.95rem;
            font-weight: 700;
            color: #0F1F1A;
            background: transparent;
            border: 0;
            outline: 0;
            box-shadow: none !important;
            appearance: none;
            -webkit-appearance: none;
            cursor: pointer;
        }
        .hero-search-control input { cursor: text; }
        .hero-search-control select::-ms-expand { display: none; }
        .hero-search-arrow {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            color: #1E6F5C;
            font-size: 1.1rem;
            pointer-events: none;
        }
        .hero-search-suffix {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            color: #1E6F5C;
            font-weight: 800;
            font-size: 0.95rem;
            pointer-events: none;
        }
        /* Compact search CTA — fixed height + margin so it does not
           stretch to fill the pill and stays visually balanced with
           the slimmer field cells next to it. */
        .hero-search-btn {
            flex-shrink: 0;
            align-self: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            height: 2.75rem;
            padding: 0 1.25rem;
            margin: 0 0.25rem 0 0.5rem;
            color: #fff;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            background: linear-gradient(135deg, #1E6F5C 0%, #155946 100%);
            border: 0;
            border-radius: 999px;
            cursor: pointer;
            box-shadow: 0 6px 14px -4px rgba(30, 111, 92, 0.45);
            transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
        }
        .hero-search-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px -4px rgba(30, 111, 92, 0.55);
            filter: brightness(1.05);
        }
        .hero-search-btn:active { transform: translateY(0); }
        .hero-search-btn i { font-size: 1rem; }

        @media (max-width: 880px) {
            .hero-search-pill { border-radius: 1.5rem; padding: 0.75rem; }
            .hero-search-row { flex-direction: column; gap: 0.25rem; }
            .hero-search-divider { display: none; }
            .hero-search-cell {
                border-radius: 1rem;
                padding: 0.65rem 1rem;
                border: 1px solid rgba(15, 31, 26, 0.06);
            }
            .hero-search-btn {
                width: 100%;
                padding: 0.95rem 1.5rem;
            }
        }

        /* ========== Search form (home hero) — LEGACY (kept for inner views) ========== */
        .search-field { position: relative; }
        .search-label {
            display: flex; align-items: center;
            font-size: 0.75rem; font-weight: 700;
            color: #545B66; text-transform: uppercase; letter-spacing: 0.04em;
            margin-bottom: 0.5rem;
        }
        .search-select { position: relative; }
        .search-select select {
            appearance: none; -webkit-appearance: none;
            width: 100%; padding: 0.875rem 2.5rem 0.875rem 1rem;
            font-size: 0.95rem; font-weight: 600; color: #0F1F1A;
            background: #F7F9F8; border: 1.5px solid #E5E9EA;
            border-radius: 0.875rem; cursor: pointer;
            transition: border-color .2s, background-color .2s, box-shadow .2s;
        }
        .search-select select:hover { border-color: #1E6F5C; background: #fff; }
        .search-select-arrow {
            position: absolute; right: 0.875rem; top: 50%; transform: translateY(-50%);
            color: #1E6F5C; font-size: 1.25rem; pointer-events: none;
        }
        .search-input-wrap { position: relative; }
        .search-input-wrap input {
            width: 100%; padding: 0.875rem 2.5rem 0.875rem 1rem;
            font-size: 0.95rem; font-weight: 600; color: #0F1F1A;
            background: #F7F9F8; border: 1.5px solid #E5E9EA;
            border-radius: 0.875rem;
            transition: border-color .2s, background-color .2s, box-shadow .2s;
        }
        .search-input-wrap input:hover { border-color: #1E6F5C; background: #fff; }
        .search-input-suffix {
            position: absolute; right: 1rem; top: 50%; transform: translateY(-50%);
            color: #1E6F5C; font-weight: 700; pointer-events: none;
        }
        .search-submit-btn {
            display: inline-flex; align-items: center; justify-content: center;
            width: 100%; margin-top: 1.5rem; padding: 1.05rem 2rem;
            color: #fff; font-size: 1rem; font-weight: 700; letter-spacing: 0.02em;
            border-radius: 1rem; border: none; cursor: pointer;
            box-shadow: 0 12px 24px -8px rgba(30,111,92,0.45);
            transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
        }
        .search-submit-btn:hover { transform: translateY(-1px); box-shadow: 0 16px 32px -8px rgba(30,111,92,0.55); filter: brightness(1.05); }
        .search-submit-btn:active { transform: translateY(0); }
        .search-submit-btn .arrow-icon { transition: transform .25s; }
        .search-submit-btn:hover .arrow-icon { transform: translateX(4px); }

        /* ========== Universal card lift on home ========== */
        .listing-card { position: relative; transition: transform .35s ease, box-shadow .35s ease; }
        .listing-card:hover { transform: translateY(-6px); box-shadow: 0 28px 56px -20px rgba(15,31,26,0.28); }

        /* ========== Modern listing card upgrades ========== */
        /* Subtle image scale + brand-tinted border on hover for any
           listing card structure used across the site. */
        article[class*="rounded"] img,
        a[href*="/listing/"] img {
            transition: transform .6s cubic-bezier(.2,.8,.2,1);
        }
        article[class*="rounded"]:hover img,
        a[href*="/listing/"]:hover img {
            transform: scale(1.04);
        }

        /* Smooth headings on home — slightly tighter tracking */
        section h2 {
            letter-spacing: -0.01em;
        }

        /* Section eyebrow polish — small uppercase label above h2 */
        .section-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.9rem;
            margin-bottom: 0.85rem;
            border-radius: 999px;
            background: rgba(30, 111, 92, 0.08);
            color: #1E6F5C;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(30, 111, 92, 0.18);
        }
        .section-eyebrow i { font-size: 0.85rem; }
        .section-eyebrow + h2 { margin-top: 0.5rem; }

        /* Pulse animation for live-status indicators (chatbot, "online" dots) */
        @keyframes brand-pulse-soft {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%      { opacity: 0.6; transform: scale(1.25); }
        }
        .brand-live-dot { animation: brand-pulse-soft 2s ease-in-out infinite; }

        /* Section padding refinement */
        section.py-24 { padding-top: 5rem; padding-bottom: 5rem; }
        @media (min-width: 1024px) {
            section.py-24 { padding-top: 6.5rem; padding-bottom: 6.5rem; }
        }

        /* ========== Primary CTA pill (View All Listings) ========== */
        .cta-pill-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 1rem 1.25rem 1rem 1.85rem;
            color: #ffffff;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            border-radius: 999px;
            background: #1E6F5C;
            transition: background .3s ease, transform .3s ease, box-shadow .3s ease;
            box-shadow: 0 6px 18px -6px rgba(30, 111, 92, 0.35);
            text-decoration: none;
        }
        .cta-pill-primary:hover {
            background: #155946;
            transform: translateY(-2px);
            box-shadow: 0 14px 32px -10px rgba(30, 111, 92, 0.5);
            color: #ffffff;
        }
        .cta-pill-arrow {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.18);
            transition: transform .3s ease;
        }
        .cta-pill-primary:hover .cta-pill-arrow {
            transform: translateX(3px);
        }
        .cta-pill-arrow i { font-size: 1.05rem; line-height: 1; }

        /* ========== Why-card (Why Choose Us section) ========== */
        .why-card {
            background: #ffffff;
            border: 1px solid #EEF2EF;
            border-radius: 1.5rem;
            padding: 2.5rem 2rem;
            text-align: center;
            transition: transform .4s cubic-bezier(.2,.8,.2,1), box-shadow .4s ease, border-color .4s ease;
            box-shadow: 0 1px 3px rgba(15,31,26,0.04);
        }
        .why-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 28px 56px -24px rgba(30,111,92,0.22);
            border-color: rgba(30,111,92,0.18);
        }
        .why-card-icon {
            width: 4.5rem;
            height: 4.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            border-radius: 1.25rem;
            background: linear-gradient(135deg, #1E6F5C 0%, #13493E 100%);
            color: #ffffff;
            font-size: 2rem;
            box-shadow: 0 12px 28px -10px rgba(30,111,92,0.45);
            transition: transform .4s cubic-bezier(.2,.8,.2,1);
        }
        .why-card:hover .why-card-icon {
            transform: scale(1.08) rotate(-3deg);
        }

        /* ========== City card polish (Popular Cities) ========== */
        a[href*="city_id="] {
            transition: transform .4s ease, box-shadow .4s ease;
        }
        a[href*="city_id="]:hover {
            transform: translateY(-4px);
            box-shadow: 0 24px 48px -20px rgba(15,31,26,0.35);
        }

        /* ========== Smooth fade-up on first paint for hero copy ========== */
        @keyframes hero-rise {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        section.relative.w-full.min-h-\[80vh\] h1,
        section.relative.w-full.min-h-\[80vh\] p,
        section.relative.w-full.min-h-\[80vh\] form {
            animation: hero-rise .8s cubic-bezier(.2,.8,.2,1) both;
        }
        section.relative.w-full.min-h-\[80vh\] p { animation-delay: .15s; }
        section.relative.w-full.min-h-\[80vh\] form { animation-delay: .3s; }

        /* ========== Section labels ========== */
        .section-eyebrow {
            display: inline-flex; align-items: center; gap: .5rem;
            padding: .375rem 1rem; border-radius: 999px;
            background: var(--color-primary-soft); color: var(--color-primary);
            font-size: 0.75rem; font-weight: 800; text-transform: uppercase;
            letter-spacing: 0.08em; margin-bottom: 1rem;
        }
        .section-eyebrow::before {
            content: ''; width: 6px; height: 6px; border-radius: 50%;
            background: var(--color-primary);
        }

        /* ========== Corporate footer ========== */
        .site-footer {
            position: relative;
            overflow: hidden;
            background: linear-gradient(145deg, #0b211b 0%, #102f27 100%);
            color: #fff;
            border-top: 4px solid var(--color-primary);
        }
        .site-footer::before {
            content: '';
            position: absolute;
            width: 24rem;
            height: 24rem;
            right: -10rem;
            top: -16rem;
            border-radius: 999px;
            background: rgba(103, 194, 166, .08);
            pointer-events: none;
        }
        .site-footer-inner {
            position: relative;
            z-index: 1;
            max-width: 1320px;
            margin: 0 auto;
            padding: 3.5rem 1.5rem 1.5rem;
        }
        .site-footer-grid {
            display: grid;
            grid-template-columns: minmax(16rem, 1.45fr) repeat(3, minmax(10rem, 1fr));
            gap: clamp(2rem, 5vw, 5rem);
            align-items: start;
        }
        .site-footer-logo {
            display: inline-flex;
            align-items: center;
            margin-bottom: 1.1rem;
            text-decoration: none;
        }
        .site-footer-logo img {
            display: block;
            height: 3.1rem;
            width: auto;
            max-width: 17rem;
            object-fit: contain;
            object-position: left center;
            filter: brightness(0) invert(1);
        }
        .site-footer-logo-text {
            color: #fff;
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: -.02em;
        }
        .site-footer-summary {
            max-width: 22rem;
            margin: 0;
            color: rgba(255,255,255,.68);
            font-size: .9rem;
            line-height: 1.75;
        }
        .site-footer-heading {
            margin: .35rem 0 1.1rem;
            color: #fff;
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: .11em;
            text-transform: uppercase;
        }
        .site-footer-heading::after {
            content: '';
            display: block;
            width: 2rem;
            height: 2px;
            margin-top: .65rem;
            border-radius: 999px;
            background: #6ec7ad;
        }
        .site-footer-links {
            display: grid;
            gap: .72rem;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .site-footer a { transition: color .2s, transform .2s, opacity .2s; }
        .site-footer-links a,
        .site-footer-contact a,
        .site-footer-contact > span {
            display: inline-flex;
            align-items: flex-start;
            gap: .55rem;
            color: rgba(255,255,255,.68);
            font-size: .875rem;
            line-height: 1.55;
            text-decoration: none;
        }
        .site-footer-links a:hover,
        .site-footer-contact a:hover {
            color: #fff;
            transform: translateX(3px);
        }
        .site-footer-contact {
            display: grid;
            gap: .85rem;
        }
        .site-footer-contact i {
            flex: 0 0 auto;
            margin-top: .05rem;
            color: #6ec7ad;
            font-size: 1.05rem;
        }
        .site-footer-socials {
            display: flex;
            flex-wrap: wrap;
            gap: .55rem;
            margin-top: 1.3rem;
        }
        .site-footer-socials a {
            display: inline-flex;
            width: 2.35rem;
            height: 2.35rem;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255,255,255,.13);
            border-radius: .75rem;
            background: rgba(255,255,255,.06);
            color: #fff;
        }
        .site-footer-socials a:hover {
            border-color: #6ec7ad;
            background: var(--color-primary);
            transform: translateY(-2px);
        }
        .site-footer-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 2.75rem;
            padding-top: 1.35rem;
            border-top: 1px solid rgba(255,255,255,.1);
            color: rgba(255,255,255,.48);
            font-size: .78rem;
        }
        .home-blog-section {
            padding-top: 5rem;
            padding-bottom: 3.5rem;
        }
        @media (max-width: 980px) {
            .site-footer-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 2.5rem 3rem; }
        }
        @media (max-width: 620px) {
            .site-footer-inner { padding: 2.75rem 1.25rem 1.25rem; }
            .site-footer-grid { grid-template-columns: 1fr; gap: 2rem; }
            .site-footer-logo img { height: 2.7rem; max-width: 15rem; }
            .site-footer-heading { margin-top: 0; }
            .site-footer-bottom { align-items: flex-start; flex-direction: column; margin-top: 2.25rem; }
            .home-blog-section { padding-top: 3.75rem; padding-bottom: 3rem; }
        }

        /* ========== Smooth scroll for in-page links ========== */
        html { scroll-behavior: smooth; }

        /* ========== Blog cards — readable spacing / clamp ========== */
        .blog-card-body { padding: 1.5rem; display: flex; flex-direction: column; gap: 0.75rem; }
        .blog-card-meta { display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; color: #6B7280; }
        .blog-card-title {
            font-size: 1.0625rem; font-weight: 700; line-height: 1.4;
            color: #1A1A1A;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        .blog-card-excerpt {
            font-size: 0.875rem; line-height: 1.55; color: #4B5563;
            display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
        }
        .blog-card-foot {
            margin-top: auto; padding-top: 1rem;
            border-top: 1px solid #F1F2F3;
            display: flex; align-items: center; justify-content: space-between;
        }
        .blog-card-cover {
            position: relative; aspect-ratio: 16/10; overflow: hidden;
        }
        .blog-card-cover img {
            width: 100%; height: 100%; object-fit: cover;
            transition: transform .5s ease;
        }
        .blog-card:hover .blog-card-cover img { transform: scale(1.06); }

        /* ========== Skip-to-content link (a11y) ========== */
        .skip-to-content {
            position: absolute;
            left: 1rem;
            top: -3.5rem;
            z-index: 10000;
            padding: 0.55rem 1.15rem;
            background: #1E6F5C;
            color: #fff;
            font-family: Inter, sans-serif;
            font-size: 0.85rem;
            font-weight: 700;
            border-radius: 999px;
            box-shadow: 0 10px 30px -6px rgba(30, 111, 92, 0.55);
            transition: top .18s ease-in-out;
            text-decoration: none;
        }
        .skip-to-content:focus {
            top: 1rem;
            outline: 3px solid rgba(30, 111, 92, 0.32);
            outline-offset: 2px;
        }

        /* ========== Tailwind arbitrary max-width fallbacks ==========
           Kritik container'lar için Tailwind JIT bazen arbitrary
           class'ları compile CSS'e katmıyor (özellikle production
           minified sürüm). Bu güvenlik ağı, sık kullanılan değerler
           için manuel utility sağlar — tüm sayfalarda garantili. */
        .max-w-\[1320px\] { max-width: 1320px !important; }
        .max-w-\[1200px\] { max-width: 1200px !important; }
        .max-w-\[900px\]  { max-width: 900px  !important; }
        .max-w-\[820px\]  { max-width: 820px  !important; }
        .max-w-\[600px\]  { max-width: 600px  !important; }
        .max-w-\[500px\]  { max-width: 500px  !important; }
        .max-w-\[400px\]  { max-width: 400px  !important; }
        .max-w-\[335px\]  { max-width: 335px  !important; }

        /* ========== Blog related posts grid ==========
           Mobile'da tek sütun; ≥640px'de 2, ≥900px'de 3 sütun.
           Inline style ile başlangıçta 3-col koyduk; küçük ekranlar
           için bu media query devreye giriyor. */
        @media (max-width: 899px) {
            .related-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
        }
        @media (max-width: 599px) {
            .related-grid { grid-template-columns: minmax(0, 1fr) !important; }
        }

        /* ========== Blog article — nefes alan içerik ==========
           Prose plugin varsayılan spacing'i sıkı — okuma keyfi için
           heading'leri ayır, paragraph'ları rahatlat. */
        article .prose h2,
        article .prose h3,
        article .prose h4 {
            margin-top: 2.25rem;
            margin-bottom: 0.75rem;
            font-family: Inter, sans-serif;
            font-weight: 700;
            letter-spacing: -0.01em;
            color: #0F1F1A;
        }
        article .prose h2 { font-size: 1.55rem; line-height: 1.3; }
        article .prose h3 { font-size: 1.25rem; line-height: 1.35; }
        article .prose h2:first-child,
        article .prose h3:first-child { margin-top: 0; }
        article .prose p {
            margin-bottom: 1.15rem;
            color: #2A3A34;
        }
        article .prose ul,
        article .prose ol {
            margin: 0.75rem 0 1.25rem 1.5rem;
        }
        article .prose li { margin-bottom: 0.45rem; }
        article .prose strong { color: #0F1F1A; font-weight: 700; }
        article .prose a { color: #1E6F5C; text-decoration: underline; text-underline-offset: 3px; }
        article .prose a:hover { color: #155946; }

        /* ========== Featured blog post (index hero card) ========== */
        .featured-post {
            transition: transform .4s ease, box-shadow .4s ease;
            border: 1px solid #EEF2EF;
        }
        .featured-post:hover {
            transform: translateY(-4px);
            box-shadow: 0 32px 72px -30px rgba(15, 31, 26, 0.35) !important;
        }
        .featured-badge {
            position: absolute;
            top: 1.25rem;
            left: 1.25rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 0.95rem;
            color: #ffffff;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            border-radius: 999px;
            background: linear-gradient(135deg, #1E6F5C 0%, #13493E 100%);
            box-shadow: 0 8px 20px -6px rgba(15, 31, 26, 0.4);
            z-index: 2;
        }
        .featured-badge i { font-size: 0.78rem; }

        /* Blog card hover lift refinement */
        .blog-card {
            border: 1px solid #EEF2EF;
            transition: transform .35s cubic-bezier(.2,.8,.2,1), box-shadow .35s ease, border-color .35s ease;
        }
        .blog-card:hover {
            border-color: rgba(30, 111, 92, 0.18);
            box-shadow: 0 28px 56px -24px rgba(15, 31, 26, 0.22) !important;
        }
        .blog-card-title a {
            color: inherit;
            text-decoration: none;
            transition: color .25s ease;
        }
        .blog-card:hover .blog-card-title a { color: #1E6F5C; }

        /* ========== Header language switcher (TR / EN pill) ========== */
        .lang-switch-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.125rem;
            padding: 0.25rem;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 999px;
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            transition: background-color .25s ease, border-color .25s ease;
        }
        .lang-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2rem;
            padding: 0.35rem 0.65rem;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            color: rgba(255, 255, 255, 0.85);
            border-radius: 999px;
            transition: background-color .2s ease, color .2s ease;
            text-decoration: none;
        }
        .lang-pill:hover { color: #fff; background: rgba(255, 255, 255, 0.15); }
        .lang-pill.is-active {
            color: #fff;
            background: var(--color-primary);
            box-shadow: 0 4px 12px -2px rgba(30, 111, 92, 0.45);
        }
        .lang-pill.is-active:hover { color: #fff; background: var(--color-primary-dark); }

        /* ========== Corporate header — footer ile aynı görsel dil ========== */
        .site-header {
            position: sticky;
            background: linear-gradient(145deg, rgba(11, 33, 27, .98) 0%, rgba(16, 47, 39, .98) 100%);
            border-bottom: 3px solid #1E6F5C;
            box-shadow: 0 16px 36px -24px rgba(3, 16, 12, .8);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            isolation: isolate;
        }
        .site-header::before {
            content: '';
            position: absolute;
            z-index: -1;
            width: 10rem;
            height: 18rem;
            right: 0;
            top: -13rem;
            border-radius: 999px;
            background: rgba(103, 194, 166, .08);
            pointer-events: none;
        }
        .site-header-inner {
            min-height: 5.25rem;
            padding-top: .7rem !important;
            padding-bottom: .7rem !important;
        }
        .site-header-logo {
            display: inline-flex;
            align-items: center;
            gap: .8rem;
            min-width: 0;
            color: #fff;
            text-decoration: none;
        }
        .site-header-logo:hover { opacity: 1; }
        .site-header-logo-mark {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.9rem;
            height: 2.9rem;
            flex: 0 0 2.9rem;
            color: #fff;
            font-size: 1.5rem;
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: 1rem;
            background: rgba(255, 255, 255, .08);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .08);
            transition: transform .25s ease, background-color .25s ease;
        }
        .site-header-logo-mark::after {
            content: '';
            position: absolute;
            width: .55rem;
            height: .55rem;
            right: -.12rem;
            bottom: .2rem;
            border: 2px solid #102f27;
            border-radius: 999px;
            background: #e96b12;
        }
        .site-header-logo:hover .site-header-logo-mark {
            transform: translateY(-1px);
            background: rgba(255, 255, 255, .13);
        }
        .site-header-logo-copy {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .site-header-logo-text {
            color: #fff;
            font-size: 1.22rem;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -.02em;
        }
        .site-header-logo-subtitle {
            margin-top: .28rem;
            color: rgba(255, 255, 255, .55);
            font-size: .61rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: .16em;
            text-transform: uppercase;
        }
        .desktop-navigation {
            gap: .15rem !important;
            padding: .32rem;
            border: 1px solid rgba(255, 255, 255, .09);
            border-radius: 999px;
            background: rgba(255, 255, 255, .045);
        }
        .desktop-navigation .nav-link {
            padding: .7rem .9rem;
            color: rgba(255, 255, 255, .72);
            font-size: .82rem;
            font-weight: 700;
            line-height: 1;
            letter-spacing: 0;
            border-radius: 999px;
            transition: color .2s ease, background-color .2s ease, transform .2s ease;
        }
        .desktop-navigation .nav-link::after {
            display: none;
        }
        .desktop-navigation .nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, .08);
        }
        .desktop-navigation .nav-link.is-active {
            color: #fff;
            background: rgba(255, 255, 255, .12);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .05);
        }
        .site-header-menu-button {
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .15);
            background: rgba(255, 255, 255, .07);
        }
        .site-header-menu-button:hover { background: rgba(255, 255, 255, .13); }
        .site-header.is-scrolled { box-shadow: 0 18px 42px -24px rgba(3, 16, 12, .95); }

        /* User dropdown color transitions */
        .desktop-user-dropdown .user-dropdown-button {
            color: white;
            transition: color 0.3s ease;
        }

        .desktop-user-dropdown .user-dropdown-button:hover {
            color: var(--color-primary);
        }

        @media (max-width: 1023px) {
            .site-header-inner {
                min-height: 4.7rem;
                padding-top: .55rem !important;
                padding-bottom: .55rem !important;
            }
            .site-header-logo-mark { width: 2.65rem; height: 2.65rem; flex-basis: 2.65rem; }
            .site-header-logo-text { font-size: 1.08rem; }
            .site-header-logo-subtitle { font-size: .55rem; letter-spacing: .13em; }
        }
        @media (max-width: 390px) {
            .site-header-logo-subtitle { display: none; }
            .site-header-logo-text { font-size: 1rem; }
        }
        @media (max-width: 767px) {
            .home-hero {
                min-height: auto !important;
                align-items: flex-start !important;
                padding-top: 7.25rem !important;
                padding-bottom: 3.25rem !important;
            }
        }

        /* Search bar enhancement */
        .search-wrapper input {
            transition: all 0.3s ease;
            background: white;
        }

        .search-wrapper input:focus {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        /* Navigation link hover effect */
        .nav-link {
            position: relative;
            transition: color 0.3s ease;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: currentColor;
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        /* Button enhancement */
        .btn-primary {
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(30, 111, 92, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(30, 111, 92, 0.3);
        }

        /* Dropdown enhancement */
        .user-dropdown-menu {
            animation: slideDown 0.2s ease-out;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Mobile menu animation */
        .mobile-menu {
            transform: translateX(-100%);
            transition: transform 0.2s ease-out;
        }

        .mobile-menu.open {
            transform: translateX(0);
        }

        .mobile-menu-backdrop {
            opacity: 0;
            transition: opacity 0.2s ease-out;
            pointer-events: none;
        }

        .mobile-menu-backdrop.open {
            opacity: 1;
            pointer-events: auto;
        }
        .mobile-menu-is-open #chatbot-root {
            opacity: 0;
            pointer-events: none;
        }

        /* Print styles */
        @media print {

            /* Hide unnecessary elements for printing */
            header,
            nav,
            footer,
            .mobile-menu,
            .mobile-menu-backdrop,
            button:not([onclick*="print"]),
            form,
            .social-media,
            .action-buttons,
            .sidebar,
            .print-hide {
                display: none !important;
            }

            /* Ensure page breaks properly */
            body {
                font-size: 12pt;
                line-height: 1.4;
                background: white !important;
                color: black !important;
                margin: 0;
            }

            /* Style the main content for print */
            main {
                margin: 0 !important;
                padding: 20px !important;
                max-width: none !important;
            }

            /* Ensure images are properly sized for print */
            img {
                max-width: 100% !important;
                height: auto !important;
            }

            /* Style tables for print */
            table {
                border-collapse: collapse;
                width: 100%;
            }

            th,
            td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }

            /* Add page breaks before certain elements */
            h1,
            h2 {
                page-break-after: avoid;
            }

            /* Style for listing details in print */
            .listing-title {
                font-size: 18pt;
                font-weight: bold;
                margin-bottom: 10px;
            }

            .listing-price {
                font-size: 16pt;
                font-weight: bold;
                color: #000 !important;
            }

            .listing-description {
                margin: 15px 0;
            }
        }
    </style>

    @stack('styles')


</head>

<body class="min-h-screen">
    {{-- Skip-to-content link — a11y (klavye kullanıcıları TAB'la
         gördüklerinde direkt içeriğe geçer) + SEO (Google
         accessibility score'unu Core Web Vitals ile birleştiriyor).
         Visually hidden ama focus'lanınca yeşil brand pill olur. --}}
    <a href="#main-content" class="skip-to-content">{{ __('general.skip_to_content') }}</a>

    <!-- Mobile menu - Enhanced design -->
    <div class="relative" style="z-index: 1300;">
        <!-- Mobile menu backdrop with blur effect -->
        <div id="mobileMenuBackdrop" class="fixed inset-0 bg-black bg-opacity-50 mobile-menu-backdrop backdrop-blur-sm"></div>

        <!-- Mobile menu panel with modern styling -->
        <div id="mobileMenu" class="fixed inset-y-0 left-0 max-w-xs w-full bg-gradient-to-br from-white to-gray-50 shadow-2xl mobile-menu">
            <!-- Header section -->
            <div class="p-6 bg-gradient-to-r from-[#1E6F5C] to-[#13493E]">
                <div class="flex items-center justify-between">
                    <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3 text-white">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-white/20 bg-white/10 text-xl" aria-hidden="true">
                            <i class="ri-home-5-line"></i>
                        </span>
                        <span class="flex min-w-0 flex-col">
                            <strong class="truncate text-lg font-extrabold tracking-tight">{{ str_ireplace(' Gayrimenkul', '', $siteTitle) }}</strong>
                            <span class="mt-1 text-[9px] font-bold uppercase tracking-[.14em] text-white/60">{{ __('general.brand_subtitle') }}</span>
                        </span>
                    </a>
                    <button id="closeMobileMenu" type="button" aria-label="{{ __('general.close_menu') }}" class="text-white hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition-all duration-200">
                        <i class="ri-close-line text-2xl"></i>
                    </button>
                </div>
            </div>

            <!-- Content section -->
            <div class="p-4 overflow-y-auto" style="max-height: calc(100vh - 88px);">
                <div class="flex items-center justify-between mb-5 p-2 rounded-xl bg-[#E7F2EF]">
                    <span class="px-2 text-xs font-bold uppercase tracking-wider text-[#1E6F5C]">{{ __('general.language') }}</span>
                    <div class="flex items-center gap-1" role="group" aria-label="{{ __('general.language') }}">
                        <a href="{{ route('locale.switch', ['code' => 'tr']) }}" class="px-3 py-2 rounded-lg text-xs font-extrabold {{ app()->getLocale() === 'tr' ? 'bg-[#1E6F5C] text-white' : 'text-gray-600' }}">TR</a>
                        <a href="{{ route('locale.switch', ['code' => 'en']) }}" class="px-3 py-2 rounded-lg text-xs font-extrabold {{ app()->getLocale() === 'en' ? 'bg-[#1E6F5C] text-white' : 'text-gray-600' }}">EN</a>
                    </div>
                </div>

                <!-- Navigation with enhanced styling -->
                <nav class="space-y-2">
                    <a href="{{ route('home') }}"
                        class="flex items-center text-gray-700 hover:text-primary hover:bg-[#1E6F5C]/10 py-3 px-4 rounded-lg transition-all duration-200">
                        <i class="ri-home-line mr-3 text-xl"></i>
                        {{ __('general.home') }}
                    </a>
                    <a href="{{ route('categories.show.all') }}"
                        class="flex items-center text-gray-700 hover:text-primary hover:bg-[#1E6F5C]/10 py-3 px-4 rounded-lg transition-all duration-200">
                        <i class="ri-building-4-line mr-3 text-xl"></i>
                        {{ __('general.listings') }}
                    </a>
                    <a href="{{ route('blog.index') }}"
                        class="flex items-center text-gray-700 hover:text-primary hover:bg-[#1E6F5C]/10 py-3 px-4 rounded-lg transition-all duration-200">
                        <i class="ri-article-line mr-3 text-xl"></i>
                        {{ __('blog.blog') }}
                    </a>
                    <a href="{{ route('pages.show', getPageSlug('about')) }}"
                        class="flex items-center text-gray-700 hover:text-primary hover:bg-[#1E6F5C]/10 py-3 px-4 rounded-lg transition-all duration-200">
                        <i class="ri-information-line mr-3 text-xl"></i>
                        {{ __('general.about_us') }}
                    </a>
                    <a href="{{ route('pages.show', getPageSlug('contact')) }}"
                        class="flex items-center text-gray-700 hover:text-primary hover:bg-[#1E6F5C]/10 py-3 px-4 rounded-lg transition-all duration-200">
                        <i class="ri-phone-line mr-3 text-xl"></i>
                        {{ __('general.contact') }}
                    </a>

                    @guest
                        {{-- Guests: no public login CTA on the mobile menu.
                             Admin reaches /login directly. --}}
                    @else
                        <div class="border-t border-gray-200 my-4"></div>
                        <a href="{{ route('user.dashboard') }}"
                            class="flex items-center text-gray-700 hover:text-primary hover:bg-blue-50 py-3 px-4 rounded-lg transition-all duration-200">
                            <i class="ri-dashboard-line mr-3 text-xl"></i>
                            {{ __('general.user_panel') }}
                        </a>
                        <a href="{{ route('user.profile') }}"
                            class="flex items-center text-gray-700 hover:text-primary hover:bg-blue-50 py-3 px-4 rounded-lg transition-all duration-200">
                            <i class="ri-user-line mr-3 text-xl"></i>
                            {{ __('general.my_profile') }}
                        </a>
                        <a href="{{ route('user.listings.my') }}"
                            class="flex items-center text-gray-700 hover:text-primary hover:bg-blue-50 py-3 px-4 rounded-lg transition-all duration-200">
                            <i class="ri-file-list-line mr-3 text-xl"></i>
                            {{ __('general.my_listings') }}
                        </a>
                        @if (Auth::user()->isAdmin())
                            <div class="border-t border-gray-200 my-4"></div>
                            <a href="{{ route('admin.dashboard') }}"
                                class="flex items-center bg-gradient-to-r from-[#1E6F5C]/10 to-[#1E6F5C]/20 text-primary font-semibold py-3 px-4 rounded-lg hover:from-[#1E6F5C]/20 hover:to-[#1E6F5C]/30 transition-all duration-200">
                                <i class="ri-admin-line mr-3 text-xl"></i>
                                {{ __('general.admin_panel') }}
                            </a>
                        @endif
                        <div class="border-t border-gray-200 my-4"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="flex items-center w-full text-red-600 hover:bg-red-50 py-3 px-4 rounded-lg transition-all duration-200">
                                <i class="ri-logout-box-line mr-3 text-xl"></i>
                                {{ __('general.logout') }}
                            </button>
                        </form>
                    @endguest



                    {{-- "İlan Ver" CTA removed — single-agency site, not
                         a marketplace. Admins use /admin to add listings. --}}
                </nav>
            </div>
        </div>
    </div>

    <!-- Header - Real Estate Design -->
    {{-- Header must sit above every widget on the page — Leaflet's
         default z-index for its panes/controls is 400-1000; even
         after clamping the map, keep a safety margin. --}}
    <header class="site-header sticky top-0 left-0 right-0 transition-all duration-300"
            style="z-index: 1200;">
        <div class="max-w-[1320px] mx-auto px-6">
            <div class="site-header-inner flex items-center justify-between transition-all duration-300 header-control">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="{{ route('home') }}" class="site-header-logo" aria-label="{{ $siteTitle }}">
                        <span class="site-header-logo-mark" aria-hidden="true">
                            <i class="ri-home-5-line"></i>
                        </span>
                        <span class="site-header-logo-copy">
                            <strong class="site-header-logo-text">{{ str_ireplace(' Gayrimenkul', '', $siteTitle) }}</strong>
                            <span class="site-header-logo-subtitle">{{ __('general.brand_subtitle') }}</span>
                        </span>
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <nav class="desktop-navigation hidden lg:flex items-center space-x-8" aria-label="{{ __('general.main_menu') }}">
                    <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'is-active' : '' }}">{{ __('general.home') }}</a>
                    <a href="{{ route('categories.show.all') }}" class="nav-link {{ request()->routeIs('categories.*', 'listings.*') ? 'is-active' : '' }}">{{ __('general.listings') }}</a>
                    <a href="{{ route('blog.index') }}" class="nav-link {{ request()->routeIs('blog.*') ? 'is-active' : '' }}">{{ __('blog.blog') }}</a>
                    <a href="{{ route('pages.show', getPageSlug('about')) }}" class="nav-link {{ request()->is('page/' . getPageSlug('about')) ? 'is-active' : '' }}">{{ __('general.about_us') }}</a>
                    <a href="{{ route('pages.show', getPageSlug('contact')) }}" class="nav-link {{ request()->is('page/' . getPageSlug('contact')) ? 'is-active' : '' }}">{{ __('general.contact') }}</a>
                </nav>

                <!-- Right Section -->
                <div class="hidden lg:flex items-center gap-4">
                    {{-- Language switcher (TR / EN). Each pill is a normal
                         link to /locale/{code} for crawlers, but a tiny
                         click handler also writes the cookie locally so
                         the switch works even when the server's middleware
                         is held by stale `php artisan serve` workers. --}}
                    @php
                        $currentLocale = app()->getLocale();
                    @endphp
                    <div class="lang-switch-pill" role="group" aria-label="Language">
                        <a href="{{ route('locale.switch', ['code' => 'tr']) }}"
                           data-locale="tr"
                           class="lang-pill lang-pill-js {{ $currentLocale === 'tr' ? 'is-active' : '' }}"
                           aria-label="Türkçe">TR</a>
                        <a href="{{ route('locale.switch', ['code' => 'en']) }}"
                           data-locale="en"
                           class="lang-pill lang-pill-js {{ $currentLocale === 'en' ? 'is-active' : '' }}"
                           aria-label="English">EN</a>
                    </div>
                    {{-- Plain server-side links — LocaleController handles
                         session + cookie. No JS interception needed. --}}
                    @auth
                        <!-- User Dropdown -->
                        <div class="desktop-user-dropdown relative user-dropdown mr-4">
                            <button class="user-dropdown-button flex items-center text-sm font-medium px-3 py-2 rounded-lg hover:bg-gray-100 transition-all duration-200">
                                <div class="w-8 h-8 bg-gradient-to-br from-[#1E6F5C] to-[#13493E] rounded-full flex items-center justify-center text-white font-semibold mr-2">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                {{ Auth::user()->name }}
                                <i class="ri-arrow-down-s-line ml-1 text-lg"></i>
                            </button>
                            <div class="user-dropdown-menu absolute right-0 mt-3 w-56 rounded-xl bg-white ring-1 ring-black ring-opacity-5 hidden overflow-hidden shadow-xl">
                                <div class="py-2">
                                    <a href="{{ route('user.dashboard') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-[#1E6F5C]/10 hover:text-primary transition-all">
                                        <i class="ri-dashboard-line mr-3 text-lg"></i> {{ __('general.user_panel') }}
                                    </a>
                                    <a href="{{ route('user.profile') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-[#1E6F5C]/10 hover:text-primary transition-all">
                                        <i class="ri-user-line mr-3 text-lg"></i> {{ __('general.my_profile') }}
                                    </a>
                                    <a href="{{ route('user.listings.my') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-[#1E6F5C]/10 hover:text-primary transition-all">
                                        <i class="ri-file-list-line mr-3 text-lg"></i> {{ __('general.my_listings') }}
                                    </a>
                                    @if (Auth::user()->isAdmin())
                                        <div class="border-t border-gray-100 my-2"></div>
                                        <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-2.5 text-sm font-semibold bg-[#1E6F5C]/10 text-primary hover:bg-[#1E6F5C]/20 transition-all">
                                            <i class="ri-admin-line mr-3 text-lg"></i> {{ __('general.admin_panel') }}
                                        </a>
                                    @endif
                                    <div class="border-t border-gray-100 my-2"></div>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex items-center w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-all">
                                            <i class="ri-logout-box-line mr-3 text-lg"></i> {{ __('general.logout') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endauth
                    {{-- Public-facing CTA for posting a listing was
                         removed — this is a single-agency site, not
                         a multi-user marketplace. Admin login is
                         reachable directly at /login when needed. --}}
                </div>

                <!-- Mobile Menu Button -->
                <button id="openMobileMenu" type="button" aria-label="{{ __('general.open_menu') }}" aria-controls="mobileMenu" aria-expanded="false" class="site-header-menu-button lg:hidden w-11 h-11 flex items-center justify-center rounded-full hover:bg-white hover:bg-opacity-15">
                    <i class="ri-menu-line text-2xl"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Email Verification Alert -->
    @include('components.email-verification-alert')

    <!-- Main Content -->
    <main id="main-content" class="mb-16" role="main">
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative container mx-auto max-w-[1320px] mt-4"
                role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
                <button class="absolute top-0 bottom-0 right-0 px-4 py-3 close-alert">
                    <i class="ri-close-line"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative container mx-auto max-w-[1200px] mt-4"
                role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
                <button class="absolute top-0 bottom-0 right-0 px-4 py-3 close-alert">
                    <i class="ri-close-line"></i>
                </button>
            </div>
        @endif

        @if (session('info'))
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative container mx-auto max-w-[1200px] mt-4"
                role="alert">
                <span class="block sm:inline">{{ session('info') }}</span>
                <button class="absolute top-0 bottom-0 right-0 px-4 py-3 close-alert">
                    <i class="ri-close-line"></i>
                </button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Cookie Consent Banner -->
    @include('components.cookie-consent')

    <!-- Footer -->
    <footer class="site-footer">
        <div class="site-footer-inner">
            <div class="site-footer-grid">
                <div>
                    @php
                        $logoPath = get_setting('site_logo');
                        $logoPath = $logoPath ? ltrim(\Illuminate\Support\Str::after($logoPath, 'public/'), '/') : null;
                        $socialLinks = [
                            ['key' => 'social_facebook_url', 'icon' => 'ri-facebook-fill', 'name' => 'Facebook'],
                            ['key' => 'social_instagram_url', 'icon' => 'ri-instagram-fill', 'name' => 'Instagram'],
                            ['key' => 'social_youtube_url', 'icon' => 'ri-youtube-fill', 'name' => 'YouTube'],
                        ];
                    @endphp
                    <a href="{{ route('home') }}" class="site-footer-logo" aria-label="{{ $siteTitle }}">
                        @if($logoPath)
                            <img src="{{ asset($logoPath) }}" alt="{{ $siteTitle }}">
                        @else
                            <span class="site-footer-logo-text">{{ $siteTitle }}</span>
                        @endif
                    </a>
                    <p class="site-footer-summary">{{ __('general.footer_description') }}</p>
                    <div class="site-footer-socials">
                        @foreach ($socialLinks as $social)
                            @php $url = get_setting($social['key']); @endphp
                            @if ($url)
                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $social['name'] }}">
                                    <i class="{{ $social['icon'] }}" aria-hidden="true"></i>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div>
                    <h2 class="site-footer-heading">{{ __('general.footer_corporate') }}</h2>
                    <ul class="site-footer-links">
                        @php
                            $footerPages = $footerPages ?? \App\Models\Page::active()
                                ->where('show_footer', true)
                                ->whereBetween('sort_order', [1, 20])
                                ->with('descriptions')
                                ->ordered()
                                ->get();
                            $corporatePages = $footerPages->whereBetween('sort_order', [1, 10])->take(6);
                        @endphp
                        @forelse($corporatePages as $page)
                            <li><a href="{{ route('pages.show', $page->slug) }}">{{ $page->title }}</a></li>
                        @empty
                            <li><a href="{{ route('pages.show', 'hakkimizda') }}">{{ __('general.about_us') }}</a></li>
                            <li><a href="{{ route('pages.show', 'iletisim') }}">{{ __('general.contact') }}</a></li>
                        @endforelse
                    </ul>
                </div>

                <div>
                    <h2 class="site-footer-heading">{{ __('general.footer_support') }}</h2>
                    <ul class="site-footer-links">
                        @php
                            $helpPages = $footerPages->whereBetween('sort_order', [11, 20])->take(4);
                        @endphp
                        @forelse($helpPages as $page)
                            <li><a href="{{ route('pages.show', $page->slug) }}">{{ $page->title }}</a></li>
                        @empty
                            <li><a href="{{ route('pages.show', 'gizlilik-politikasi') }}">{{ __('general.privacy_policy') }}</a></li>
                        @endforelse
                    </ul>
                </div>

                <div>
                    <h2 class="site-footer-heading">{{ __('general.footer_contact') }}</h2>
                    @php
                        $contactEmail = trim((string) get_setting('contact_email', ''));
                        $contactPhone = get_setting('contact_phone');
                    @endphp
                    <div class="site-footer-contact">
                        @if($contactEmail !== '')
                            <a href="mailto:{{ $contactEmail }}"><i class="ri-mail-line" aria-hidden="true"></i><span>{{ $contactEmail }}</span></a>
                        @endif
                        @if($contactPhone)
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contactPhone) }}"><i class="ri-phone-line" aria-hidden="true"></i><span>{{ $contactPhone }}</span></a>
                        @endif
                        <span><i class="ri-map-pin-2-line" aria-hidden="true"></i><span>{{ __('general.footer_location') }}</span></span>
                    </div>
                </div>
            </div>

            @php
                // Taşınmaz Ticareti Hakkında Yönetmelik m.14/2-(i): yetki belgesi
                // numarası ve belgedeki unvan okunabilir şekilde gösterilir.
                // Ayar boşsa satır hiç basılmaz.
                $footerLegalTitle = trim((string) get_setting('legal_company_title', ''));
                $footerLegalNo    = trim((string) get_setting('legal_authorization_no', ''));
            @endphp
            <div class="site-footer-bottom">
                <p>© {{ date('Y') }} {{ get_setting('site_title') }}. {{ \App\Services\AutoTranslator::auto(get_setting('footer_copyright_text', __('general.all_rights_reserved'))) }}</p>
                @if($footerLegalNo !== '')
                    <p>
                        @if($footerLegalTitle !== ''){{ $footerLegalTitle }}@endif
                        @if($footerLegalTitle !== '' && $footerLegalNo !== '') &middot; @endif
                        @if($footerLegalNo !== ''){{ __('listings.authorization_no') }}: {{ $footerLegalNo }}@endif
                    </p>
                @endif
                <p>{{ __('general.footer_local_note') }}</p>
            </div>
        </div>
    </footer>


    @if(request()->routeIs('listings.show'))
        <!-- Lightbox is only needed on listing detail pages. -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
    @endif

    <!-- Global script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const openMobileMenuBtn = document.getElementById('openMobileMenu');
            const closeMobileMenuBtn = document.getElementById('closeMobileMenu');
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileMenuBackdrop = document.getElementById('mobileMenuBackdrop');

            function openMobileMenu() {
                if (!mobileMenu || !mobileMenuBackdrop) return;
                mobileMenu.classList.add('open');
                mobileMenuBackdrop.classList.add('open');
                openMobileMenuBtn?.setAttribute('aria-expanded', 'true');
                document.body.style.overflow = 'hidden';
                document.body.classList.add('mobile-menu-is-open');
            }

            function closeMobileMenu() {
                if (!mobileMenu || !mobileMenuBackdrop) return;
                mobileMenu.classList.remove('open');
                mobileMenuBackdrop.classList.remove('open');
                openMobileMenuBtn?.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
                document.body.classList.remove('mobile-menu-is-open');
            }

            if (openMobileMenuBtn) {
                openMobileMenuBtn.addEventListener('click', openMobileMenu);
            }

            if (closeMobileMenuBtn) {
                closeMobileMenuBtn.addEventListener('click', closeMobileMenu);
            }

            if (mobileMenuBackdrop) {
                mobileMenuBackdrop.addEventListener('click', closeMobileMenu);
            }

            // Close mobile menu when clicking any link inside it (better UX on mobile)
            if (mobileMenu) {
                mobileMenu.querySelectorAll('a').forEach(function(link) {
                    link.addEventListener('click', closeMobileMenu);
                });
            }

            // User dropdown toggle
            const userDropdownButton = document.querySelector('.user-dropdown-button');
            if (userDropdownButton) {
                userDropdownButton.addEventListener('click', function(e) {
                    e.stopPropagation();
                    document.querySelector('.user-dropdown-menu').classList.toggle('hidden');
                });
            }

            // Close dropdowns when clicking elsewhere
            document.addEventListener('click', function() {
                const dropdownMenu = document.querySelector('.user-dropdown-menu');
                if (dropdownMenu) {
                    dropdownMenu.classList.add('hidden');
                }
            });

            // Prevent dropdown from closing when clicking inside it
            const userDropdownMenu = document.querySelector('.user-dropdown-menu');
            if (userDropdownMenu) {
                userDropdownMenu.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }

        });

   </script>
    <script>
        (function () {
            const header = document.querySelector('.site-header');
            if (!header) return;

            const updateHeader = () => header.classList.toggle('is-scrolled', window.scrollY > 12);
            updateHeader();
            window.addEventListener('scroll', updateHeader, { passive: true });
        })();
    </script>

    {{-- The public assistant is intentionally hidden from internal auth screens. --}}
    @unless(request()->routeIs('login', 'password.*', 'user.*'))
        <x-chatbot />
    @endunless

    @stack('scripts')

    {{-- Structured Data (Schema.org) --}}
    @hasSection('schema')
    <script type="application/ld+json">
    @yield('schema')
    </script>
    @endif

    {{-- Custom Footer Scripts from Settings --}}
    {{-- footer_scripts: ücretsiz sürümde ön yüzde kullanılmaz --}}
</body>

</html>
