@php echo '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' . "\n"; @endphp
{{-- Generic RETS-benzeri fallback şema — Hepsihome, Zingat, İhale,
     Endeksa gibi diğer platformlar için başlangıç noktası. Portal'a
     özel bir şema gelirse bunun benzerini /resources/views/feeds/
     içine ekleyip PortalFeedController'a bir method + route ekleyin. --}}
<rets version="RETS/1.7.2">
    <count records="{{ $listings->count() }}"/>
    <data>
        @foreach($listings as $listing)
            @php
                $categoryName = optional(optional($listing->category)?->getCurrentDescription())->name;
            @endphp
            <property>
                <id>DEMO-{{ $listing->id }}</id>
                <title>{!! htmlspecialchars($listing->title, ENT_XML1 | ENT_QUOTES, 'UTF-8') !!}</title>
                <description><![CDATA[{{ strip_tags((string) $listing->description) }}]]></description>
                <url>{{ route('listings.show', $listing->slug) }}</url>
                <category>{{ $categoryName }}</category>
                <price currency="TRY">{{ (int) $listing->price }}</price>
                <location>
                    <city>{{ optional($listing->city)->name }}</city>
                    <district>{{ optional($listing->district)->name }}</district>
                    <neighborhood>{{ optional($listing->neighborhood)->name }}</neighborhood>
                    @if($listing->latitude && $listing->longitude)
                        <lat>{{ $listing->latitude }}</lat>
                        <lng>{{ $listing->longitude }}</lng>
                    @endif
                </location>
                <images>
                    @if($listing->image)
                        <image primary="true">{{ str_starts_with($listing->image, 'http') ? $listing->image : url($listing->image) }}</image>
                    @endif
                    @foreach($listing->images as $img)
                        @php $imgUrl = str_starts_with($img->image, 'http') ? $img->image : url($img->image); @endphp
                        <image primary="false">{{ $imgUrl }}</image>
                    @endforeach
                </images>
                <updated>{{ $listing->updated_at->toIso8601String() }}</updated>
            </property>
        @endforeach
    </data>
</rets>
