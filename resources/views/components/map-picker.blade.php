@props([
    'name'      => 'coordinates',
    'latitude'  => null,
    'longitude' => null,
    'zoom'      => 12,
    'height'    => '380px',
    'defaultLat' => 37.8579,  // Centred on Kuşadası (Türkmen) by default
    'defaultLng' => 27.2610,
])

@php
    // OpenStreetMap / Leaflet picker — free, no API key required.
    $uid = 'mp' . substr(md5(uniqid('', true)), 0, 10);
    $mapElId    = 'map_' . $uid;
    $latInputId = 'lat_' . $uid;
    $lngInputId = 'lng_' . $uid;
    $initFnName = 'initMapPicker_' . $uid;
    $lat = is_numeric($latitude)  ? (float) $latitude  : null;
    $lng = is_numeric($longitude) ? (float) $longitude : null;
    $startLat = $lat ?? (float) $defaultLat;
    $startLng = $lng ?? (float) $defaultLng;
    $startZoom = $lat ? (int) max($zoom, 14) : (int) $zoom;
    $hasInitialMarker = $lat !== null;

    // CARTO Voyager döşemeleri. Anahtar tanımlıysa sorgu dizisine eklenir.
    $tileKey      = trim((string) config('services.map.tile_key', ''));
    $tileBaseUrl  = 'https://basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png';
    $tileUrl      = $tileBaseUrl . ($tileKey !== '' ? '?key=' . urlencode($tileKey) : '');
@endphp

<div>
    <div id="{{ $mapElId }}"
         style="width:100%; height:{{ $height }}; border-radius: .75rem; background: #f3f4f6;"></div>
    <p class="text-xs text-gray-500 mt-2">
        <i class="ri-cursor-line"></i>
        {{ __('listings.map_picker_hint') }}
    </p>

    <input type="hidden" id="{{ $latInputId }}" name="{{ $name }}[latitude]"  value="{{ $lat }}">
    <input type="hidden" id="{{ $lngInputId }}" name="{{ $name }}[longitude]" value="{{ $lng }}">
</div>

@push('head')
@once('leaflet-css')
{{-- Leaflet 1.9.4 — kararlı sürüm. unpkg birincil, erişilemezse jsDelivr
     yedeği. Aynı npm paketi olduğu için SRI özeti ortaktır. --}}
<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin="anonymous"
      onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css';">
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

        var map = L.map(el).setView([{{ $startLat }}, {{ $startLng }}], {{ $startZoom }});
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

        var marker = null;
        @if($hasInitialMarker)
            marker = L.marker([{{ $startLat }}, {{ $startLng }}], { draggable: true }).addTo(map);
        @endif

        var latEl = document.getElementById('{{ $latInputId }}');
        var lngEl = document.getElementById('{{ $lngInputId }}');
        function sync(latlng) {
            latEl.value = latlng.lat.toFixed(7);
            lngEl.value = latlng.lng.toFixed(7);
        }
        map.on('click', function (e) {
            if (!marker) {
                marker = L.marker(e.latlng, { draggable: true }).addTo(map);
                marker.on('dragend', function (ev) { sync(ev.target.getLatLng()); });
            } else {
                marker.setLatLng(e.latlng);
            }
            sync(e.latlng);
        });
        if (marker) {
            marker.on('dragend', function (ev) { sync(ev.target.getLatLng()); });
        }
    };
    window['{{ $initFnName }}']();
</script>
@endpush
