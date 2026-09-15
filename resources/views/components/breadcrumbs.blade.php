@props(['items' => []])

{{-- Görünen breadcrumb — SEO açısından hem visible content hem
     BreadcrumbList schema (JSON-LD zaten ilgili sayfada push edilir)
     iki koldan sinyal verir. UX açısından derin sayfalardan geri
     dönüşü kolaylaştırır.

     Kullanım:
     <x-breadcrumbs :items="[
        ['name' => 'Ana Sayfa', 'url' => route('home')],
        ['name' => 'Satılık Daire', 'url' => route('categories.show', 'satilik-daire')],
        ['name' => 'Türkmen Mahallesi Tam Deniz Manzaralı 3+1 Lüks Daire'],
     ]" />

     Son eleman URL'siz bırakılırsa "current page" olarak render edilir.
--}}
@php
    /** @var array<int, array{name: string, url?: string}> $items */
    $items = collect($items)->filter(fn($i) => ! empty($i['name']))->values();
@endphp

@if($items->count() > 0)
    <nav aria-label="{{ __('general.breadcrumb') }}" class="breadcrumb-nav"
         style="padding: 0.75rem 0; font-size: 0.875rem;">
        <ol style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.35rem; list-style: none; margin: 0; padding: 0;">
            @foreach($items as $i => $item)
                @php $isLast = $i === $items->count() - 1; @endphp
                <li style="display: inline-flex; align-items: center; gap: 0.35rem;">
                    @if(! $isLast && ! empty($item['url']))
                        <a href="{{ $item['url'] }}"
                           style="color: #1E6F5C; text-decoration: none; font-weight: 600; transition: color .2s;"
                           onmouseover="this.style.color='#155946'" onmouseout="this.style.color='#1E6F5C'">
                            {{ $item['name'] }}
                        </a>
                    @else
                        <span aria-current="page" style="color: #6B7280; font-weight: 500;">
                            {{ \Illuminate\Support\Str::limit($item['name'], 60) }}
                        </span>
                    @endif
                    @if(! $isLast)
                        <i class="ri-arrow-right-s-line" style="color: #9CA3AF; font-size: 1rem; line-height: 1;" aria-hidden="true"></i>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
