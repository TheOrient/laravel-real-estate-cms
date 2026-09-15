@php
    $galleryImages = ($listing->images ?? collect());

    if ($galleryImages->isEmpty() && $listing->image) {
        // Tek bir kapak görseli varsa onu da galeriye ekleyelim
        $galleryImages = collect([(object)['image' => $listing->image]]);
    }

    $galleryId = 'listing-gallery-' . $listing->id;
@endphp

<div class="relative w-full h-64 overflow-hidden group" data-gallery-id="{{ $galleryId }}">
    @forelse($galleryImages as $index => $image)
        @php
            // External URLs (Unsplash) bypass Glide; local paths get
            // routed through the resize endpoint as before.
            $imgSrc = $image->image;
            $imgUrl = (str_starts_with($imgSrc, 'http://') || str_starts_with($imgSrc, 'https://'))
                ? $imgSrc
                : route('image.resize', ['size' => 'gallery_thumbnail', 'fit' => 'crop', 'path' => 'uploads/listings/' . $imgSrc]);
        @endphp
        @php
            // Alt tag surfaces the head-term keyword — Google Images
            // ranks partly on alt content, and it improves accessibility.
            $categoryName = optional(optional($listing->category)?->getCurrentDescription())->name;
            $districtName = optional($listing->district)->name;
            $altKeywords  = trim(implode(' ', array_filter([$categoryName, $districtName])));
            $imgAlt       = $altKeywords
                ? $altKeywords . ' — ' . $listing->title . ($index > 0 ? ' · Görsel ' . ($index + 1) : '')
                : $listing->title;
        @endphp
        <img
            @if($index === 0) src="{{ $imgUrl }}" @else data-src="{{ $imgUrl }}" @endif
            alt="{{ $imgAlt }}"
            loading="lazy"
            decoding="async"
            class="w-full h-full object-cover transition-opacity duration-300 {{ $index === 0 ? 'opacity-100' : 'opacity-0 pointer-events-none absolute inset-0' }}"
            data-gallery-image="{{ $galleryId }}"
            data-gallery-index="{{ $index }}"
        >
    @empty
        <div class="w-full h-full bg-gray-100 flex items-center justify-center">
            <i class="ri-image-line text-4xl text-gray-400"></i>
        </div>
    @endforelse

    {{-- Subtle bottom gradient overlay so price badges + dots stay legible
         even on bright/white images. Pure decoration; clicks pass through. --}}
    <div class="absolute inset-x-0 bottom-0 h-24 pointer-events-none"
         style="background: linear-gradient(180deg, rgba(15,31,26,0) 0%, rgba(15,31,26,0.35) 100%);"></div>

    @if($galleryImages->count() > 1)
        <!-- Nav Buttons -->
        <button
            type="button"
            class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center bg-white/80 rounded-full hover:bg-white transition-all cursor-pointer listing-gallery-prev opacity-0 group-hover:opacity-100"
            data-gallery-target="{{ $galleryId }}"
        >
            <i class="ri-arrow-left-s-line text-[#1A1A1A]"></i>
        </button>
        <button
            type="button"
            class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center bg-white/80 rounded-full hover:bg-white transition-all cursor-pointer listing-gallery-next opacity-0 group-hover:opacity-100"
            data-gallery-target="{{ $galleryId }}"
        >
            <i class="ri-arrow-right-s-line text-[#1A1A1A]"></i>
        </button>

        <!-- Dots -->
        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-1">
            @foreach($galleryImages as $index => $image)
                <div
                    class="h-2 rounded-full transition-all {{ $index === 0 ? 'bg-white w-6' : 'bg-white/50 w-2' }}"
                    data-gallery-dot="{{ $galleryId }}"
                    data-gallery-index="{{ $index }}"
                ></div>
            @endforeach
        </div>
    @endif
</div>
