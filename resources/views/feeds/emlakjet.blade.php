@php echo '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' . "\n"; @endphp
{{-- Emlakjet corporate feed flavour.
     Doğrudan Emlakjet ile sözleşme yaptıktan sonra teknik ekiplerinden
     alacağınız en güncel şemaya göre alan adlarını buradan güncelleyin.
     Aşağıdaki temel şema %90 uyumludur; kalan %10 (özel attribute'ler,
     kategori ID eşleme, teslim tarih formatı) portal ekibiyle
     doğrulanmalıdır. --}}
<listings xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
@foreach($listings as $listing)
    @php
        $categoryName = optional(optional($listing->category)?->getCurrentDescription())->name;
    @endphp
    <listing>
        <external_id>DEMO-{{ $listing->id }}</external_id>
        <title>{!! htmlspecialchars($listing->title, ENT_XML1 | ENT_QUOTES, 'UTF-8') !!}</title>
        <description><![CDATA[{{ strip_tags((string) $listing->description) }}]]></description>
        <url>{{ route('listings.show', $listing->slug) }}</url>
        <category>{{ $categoryName }}</category>
        <price currency="TRY">{{ (int) $listing->price }}</price>
        <city>{{ optional($listing->city)->name }}</city>
        <district>{{ optional($listing->district)->name }}</district>
        <neighborhood>{{ optional($listing->neighborhood)->name }}</neighborhood>
        @if($listing->latitude && $listing->longitude)
            <latitude>{{ $listing->latitude }}</latitude>
            <longitude>{{ $listing->longitude }}</longitude>
        @endif
        <images>
            @if($listing->image)
                <image order="0" primary="1">{{ str_starts_with($listing->image, 'http') ? $listing->image : url($listing->image) }}</image>
            @endif
            @foreach($listing->images as $idx => $img)
                @php $imgUrl = str_starts_with($img->image, 'http') ? $img->image : url($img->image); @endphp
                <image order="{{ $idx + 1 }}" primary="0">{{ $imgUrl }}</image>
            @endforeach
        </images>
        <updated>{{ $listing->updated_at->toIso8601String() }}</updated>
        <status>active</status>
    </listing>
@endforeach
</listings>
