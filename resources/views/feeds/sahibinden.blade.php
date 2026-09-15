@php echo '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' . "\n"; @endphp
{{-- Sahibinden corporate flavour — TAKKO / SahibindenIndex tipi feed.
     Her kurumsal Sahibinden üyesine özel bir şablon verilir; aşağıdaki
     yapı yaygın referans şemadır. Sahibinden ile sözleşme sonrası
     Platform desteğinden "Referans XML" isteyin ve alan
     adlarını burada güncelleyin. --}}
<Ads xmlns="http://www.sahibinden.com/schema/ads">
@foreach($listings as $listing)
    @php
        $categoryName = optional(optional($listing->category)?->getCurrentDescription())->name;
        $listingType  = str_contains(mb_strtolower((string) $categoryName), 'kiralık') ? 'RENT' : 'SALE';
    @endphp
    <Ad>
        <AdvertNo>DEMO-{{ $listing->id }}</AdvertNo>
        <Title>{!! htmlspecialchars($listing->title, ENT_XML1 | ENT_QUOTES, 'UTF-8') !!}</Title>
        <Description><![CDATA[{{ strip_tags((string) $listing->description) }}]]></Description>
        <Category>{{ $categoryName }}</Category>
        <AdvertType>{{ $listingType }}</AdvertType>
        <Price>
            <Amount>{{ (int) $listing->price }}</Amount>
            <Currency>TRY</Currency>
        </Price>
        <Location>
            <City>{{ optional($listing->city)->name }}</City>
            <District>{{ optional($listing->district)->name }}</District>
            <Neighborhood>{{ optional($listing->neighborhood)->name }}</Neighborhood>
            @if($listing->latitude && $listing->longitude)
                <Latitude>{{ $listing->latitude }}</Latitude>
                <Longitude>{{ $listing->longitude }}</Longitude>
            @endif
        </Location>
        <Photos>
            @if($listing->image)
                <Photo IsMain="true">{{ str_starts_with($listing->image, 'http') ? $listing->image : url($listing->image) }}</Photo>
            @endif
            @foreach($listing->images as $img)
                @php $imgUrl = str_starts_with($img->image, 'http') ? $img->image : url($img->image); @endphp
                <Photo IsMain="false">{{ $imgUrl }}</Photo>
            @endforeach
        </Photos>
        <ContactUrl>{{ route('listings.show', $listing->slug) }}</ContactUrl>
        <UpdatedDate>{{ $listing->updated_at->format('Y-m-d H:i:s') }}</UpdatedDate>
    </Ad>
@endforeach
</Ads>
