@php echo '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' . "\n"; @endphp
{{-- Hurriyet Emlak corporate flavour.
     Hurriyet Emlak, XML feed'i "ExportInterface" ismiyle kabul eder.
     Sözleşme sonrası feed URL'i partner panelinize eklersiniz. --}}
<realEstateExport>
@foreach($listings as $listing)
    @php
        $categoryName = optional(optional($listing->category)?->getCurrentDescription())->name;
    @endphp
    <realEstate id="DEMO-{{ $listing->id }}">
        <title><![CDATA[{{ $listing->title }}]]></title>
        <description><![CDATA[{{ strip_tags((string) $listing->description) }}]]></description>
        <categoryName>{{ $categoryName }}</categoryName>
        <price>{{ (int) $listing->price }}</price>
        <currency>TRY</currency>
        <city>{{ optional($listing->city)->name }}</city>
        <town>{{ optional($listing->district)->name }}</town>
        <neighborhood>{{ optional($listing->neighborhood)->name }}</neighborhood>
        @if($listing->latitude && $listing->longitude)
            <coordinates>
                <lat>{{ $listing->latitude }}</lat>
                <lng>{{ $listing->longitude }}</lng>
            </coordinates>
        @endif
        <detailUrl>{{ route('listings.show', $listing->slug) }}</detailUrl>
        <lastUpdate>{{ $listing->updated_at->format('Y-m-d\TH:i:s') }}</lastUpdate>
        <photos>
            @if($listing->image)
                <photo main="true">{{ str_starts_with($listing->image, 'http') ? $listing->image : url($listing->image) }}</photo>
            @endif
            @foreach($listing->images as $img)
                @php $imgUrl = str_starts_with($img->image, 'http') ? $img->image : url($img->image); @endphp
                <photo main="false">{{ $imgUrl }}</photo>
            @endforeach
        </photos>
    </realEstate>
@endforeach
</realEstateExport>
