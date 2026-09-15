@props(['url' => null, 'file' => null, 'aspect' => '16/9'])

@php
    // External embed takes precedence over self-hosted file when both
    // are set. Operator intent: "use the URL if I bothered to put one in".
    [$provider, $videoId] = \App\Services\VideoEmbedService::detect($url);
    $embedUrl = \App\Services\VideoEmbedService::embedUrl($url);
    $fileUrl = $file ? asset($file) : null;
@endphp

@if($embedUrl)
    <div class="relative w-full overflow-hidden rounded-xl bg-black" style="aspect-ratio: {{ $aspect }};">
        <iframe
            src="{{ $embedUrl }}"
            class="absolute inset-0 w-full h-full"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen
            loading="lazy"
            referrerpolicy="strict-origin-when-cross-origin">
        </iframe>
    </div>
@elseif($fileUrl)
    <video controls preload="metadata" playsinline
           class="w-full rounded-xl bg-black"
           style="aspect-ratio: {{ $aspect }}; object-fit: contain;">
        <source src="{{ $fileUrl }}">
        Your browser does not support the video tag.
    </video>
@endif
