{{-- hreflang alternates for each active language, plus an
     x-default fallback that points at the default-language version so
     Google knows which one to serve when no locale matches. --}}
@foreach ($active_langauges as $active_langauge)
    <link rel="alternate" hreflang="{{ $active_langauge->hreflang }}" href="{{ request()->fullUrlWithQuery(['lang' => $active_langauge->code]) }}">
@endforeach

@php
    $defaultLang = collect($active_langauges)->firstWhere('is_default', true)
        ?? collect($active_langauges)->first();
@endphp
@if($defaultLang)
    <link rel="alternate" hreflang="x-default" href="{{ request()->fullUrlWithQuery(['lang' => $defaultLang->code]) }}">
@endif
