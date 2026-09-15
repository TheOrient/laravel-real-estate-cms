@php echo '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' . "\n"; @endphp
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
@foreach($urls as $url)
    <url>
        <loc>{{ $url['loc'] }}</loc>
        <lastmod>{{ $url['lastmod'] }}</lastmod>
        <changefreq>{{ $url['changefreq'] }}</changefreq>
        <priority>{{ $url['priority'] }}</priority>
        {{-- Image sitemap extension.
             images[] varsa çoklu; yoksa geriye dönük tekli image alanı
             kullanılır. Google Image Search burada bildirilen her
             URL'i indexler. --}}
        @if(!empty($url['images']) && is_array($url['images']))
            @foreach($url['images'] as $img)
        <image:image>
            <image:loc>{{ $img['url'] }}</image:loc>
            @if(!empty($img['title']))<image:title>{{ $img['title'] }}</image:title>@endif
            @if(!empty($img['caption']))<image:caption>{{ $img['caption'] }}</image:caption>@endif
        </image:image>
            @endforeach
        @elseif(isset($url['image']) && $url['image'])
        <image:image>
            <image:loc>{{ $url['image'] }}</image:loc>
            @if(isset($url['title']))<image:title>{{ $url['title'] }}</image:title>@endif
            @if(isset($url['caption']))<image:caption>{{ $url['caption'] }}</image:caption>@endif
        </image:image>
        @endif
    </url>
@endforeach
</urlset>
