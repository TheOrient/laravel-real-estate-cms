@props([
    'latitude'  => null,
    'longitude' => null,
    'zoom'      => 15,
    'height'    => '420px',
    'title'     => null,
])

@php
    // OpenStreetMap via Leaflet. Free, no API key, no billing — works
    // anywhere the visitor's browser can reach the tile CDN.
    $hasCoords = is_numeric($latitude) && is_numeric($longitude);
    $uid = 'md' . substr(md5(uniqid('', true)), 0, 10);
    $mapElId = 'map_' . $uid;
    $initFnName = 'initMapDisplay_' . $uid;

    // CARTO Voyager döşemeleri. Anahtar tanımlıysa sorgu dizisine eklenir.
    $tileKey      = trim((string) config('services.map.tile_key', ''));
    $tileBaseUrl  = 'https://basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png';
    $tileUrl      = $tileBaseUrl . ($tileKey !== '' ? '?key=' . urlencode($tileKey) : '');
@endphp

@if($hasCoords)
    {{-- position: relative + z-index: 0 pens the map + its internal
         Leaflet panes into their own stacking context so nothing
         escapes over the sticky header or over sidebar cards. --}}
    <div id="{{ $mapElId }}"
         style="width:100%; height:{{ $height }}; border-radius: 1rem; background: #f3f4f6; position: relative; z-index: 0; overflow: hidden;"></div>

    @push('head')
    @once('leaflet-css')
    {{-- Leaflet 1.9.4 — kararlı sürüm (2.0 halen alpha). unpkg birincil,
         erişilemezse jsDelivr yedeği devreye girer. Her iki CDN de aynı npm
         paketini sunduğu için SRI özeti ortaktır: bayt farklıysa tarayıcı
         dosyayı reddeder. --}}
    <link rel="stylesheet"
          href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin="anonymous"
          onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css';">
    {{-- Leaflet'in kendi z-index değerleri (panolar 400, kontroller 1000)
         sticky header'ın üzerine çıkar. Haritanın tüm iç panolarını
         header'ın (z-index: 1200 — bkz. layouts/app.blade.php) ALTINA
         sabitliyoruz. --}}
    <style>
        .leaflet-container            { z-index: 0 !important; }
        .leaflet-pane                 { z-index: 1 !important; }
        .leaflet-tile-pane            { z-index: 2 !important; }
        .leaflet-overlay-pane         { z-index: 4 !important; }
        .leaflet-shadow-pane          { z-index: 5 !important; }
        .leaflet-marker-pane          { z-index: 6 !important; }
        .leaflet-tooltip-pane         { z-index: 7 !important; }
        .leaflet-popup-pane           { z-index: 8 !important; }
        .leaflet-map-pane canvas      { z-index: 1 !important; }
        .leaflet-map-pane svg         { z-index: 2 !important; }
        .leaflet-top,
        .leaflet-bottom               { z-index: 9 !important; }
    </style>

    @endonce
    @endpush

    @push('scripts')
    @once('leaflet-js')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin="anonymous"
            onerror="this.onerror=null;var s=document.createElement('script');s.src='https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js';s.integrity=this.integrity;s.crossOrigin='anonymous';document.head.appendChild(s);"></script>
    @endonce
    <script>
        window['{{ $initFnName }}'] = function () {
            if (typeof L === 'undefined') { return setTimeout(window['{{ $initFnName }}'], 50); }
            var el = document.getElementById('{{ $mapElId }}');
            if (!el || el.dataset.mapReady === '1') return;
            el.dataset.mapReady = '1';

            var map = L.map(el).setView([{{ (float) $latitude }}, {{ (float) $longitude }}], {{ (int) $zoom }});
            // Carto Voyager — softer, more modern looking OSM tiles.
            var tiles_{{ $uid }} = L.tileLayer(@json($tileUrl), {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>'
            }).addTo(map);

            @if($tileKey !== '')
            {{-- Anahtar CARTO tarafından reddedilirse (alan adı kısıtı, askıya
                 alınmış veya yanlış anahtar) harita boş kalmasın: ilk döşeme
                 hatasında anahtarsız URL'ye düşülür. --}}
            var carto_{{ $uid }}_fellBack = false;
            tiles_{{ $uid }}.on('tileerror', function () {
                if (carto_{{ $uid }}_fellBack) return;
                carto_{{ $uid }}_fellBack = true;
                if (window.console) console.warn('[harita] CARTO anahtarı reddedildi, anahtarsız döşemelere geçildi.');
                tiles_{{ $uid }}.setUrl(@json($tileBaseUrl));
            });
            @endif

            // Slightly larger custom marker styling for a brand feel.
            L.marker([{{ (float) $latitude }}, {{ (float) $longitude }}])
                .addTo(map)
                .bindPopup(@json($title ?? ''))
                .openPopup();
        };
        window['{{ $initFnName }}']();
    </script>
    @endpush
@endif
