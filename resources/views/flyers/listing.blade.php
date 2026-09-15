<!DOCTYPE html>
@php
    /** @var \App\Models\Listing $listing */
    /** @var array{primary:string,dark:string,accent:string} $colors */
    $title = $listing->title ?? ('Listing #' . $listing->id);
    $price = $listing->price ? number_format($listing->price, 0, ',', '.') . ' ₺' : '';
    $loc = collect([$listing->city?->name, $listing->district?->name, $listing->neighborhood?->name])
        ->filter()->implode(', ');
    $desc = trim(strip_tags((string) ($listing->description ?? '')));
    $coverPath = $listing->images?->first()?->image ?? $listing->image;
    $coverUrl = $coverPath ? asset('uploads/listings/' . ltrim(str_replace('uploads/listings/', '', $coverPath), '/')) : null;
    if ($coverPath && str_contains($coverPath, 'uploads/')) {
        // Some installs already store the full prefix.
        $coverUrl = asset(ltrim($coverPath, '/'));
    }

    $isLandscape = $orientation === 'landscape';
@endphp
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }} — flyer</title>
    <style>
        @page { margin: 18mm 14mm; }
        body {
            font-family: DejaVu Sans, sans-serif; /* dompdf-friendly */
            color: #1a1a1a;
            margin: 0;
        }

        .hdr {
            background: {{ $colors['primary'] }};
            color: white;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 16px;
            overflow: hidden;
        }
        .hdr .brand { font-size: 22px; font-weight: bold; float: left; }
        .hdr .price { font-size: 20px; font-weight: bold; float: right; }

        .cover {
            width: 100%;
            border-radius: 10px;
            margin-bottom: 14px;
            border: 1px solid {{ $colors['accent'] }};
        }
        .cover img { width: 100%; height: auto; display: block; border-radius: 10px; }

        h1.title {
            font-size: 22px;
            color: {{ $colors['dark'] }};
            margin: 0 0 6px 0;
        }
        .loc {
            color: #555;
            font-size: 12px;
            margin-bottom: 12px;
        }

        .desc {
            font-size: 12px;
            line-height: 1.55;
            color: #333;
            margin-bottom: 14px;
            text-align: justify;
        }

        .grid {
            width: 100%;
            border-collapse: collapse;
        }
        .grid td {
            vertical-align: top;
            padding: 6px;
        }

        .pill {
            display: inline-block;
            background: {{ $colors['accent'] }};
            color: {{ $colors['dark'] }};
            border-radius: 999px;
            padding: 3px 10px;
            margin: 0 4px 4px 0;
            font-size: 11px;
        }

        .qr-box {
            text-align: center;
            border: 2px solid {{ $colors['primary'] }};
            border-radius: 10px;
            padding: 10px;
            width: 160px;
        }
        .qr-box img { width: 130px; height: 130px; }
        .qr-box .lbl { font-size: 10px; color: #555; margin-top: 6px; }

        .ftr {
            margin-top: 18px;
            padding-top: 10px;
            border-top: 2px solid {{ $colors['primary'] }};
            font-size: 11px;
            color: #444;
            overflow: hidden;
        }
        .ftr .lft  { float: left; }
        .ftr .rht  { float: right; }
        .strap { color: {{ $colors['primary'] }}; font-weight: bold; }

        .row { display: table; width: 100%; }
        .col-2-3 { display: table-cell; width: 64%; padding-right: 12px; }
        .col-1-3 { display: table-cell; width: 36%; vertical-align: top; }
    </style>
</head>
<body>
    <div class="hdr">
        <div class="brand">{{ $siteName ?: 'Real Estate' }}</div>
        @if($price !== '')
            <div class="price">{{ $price }}</div>
        @endif
        <div style="clear:both"></div>
    </div>

    @if($isLandscape)
        {{-- ============ Landscape layout: two columns ============ --}}
        <div class="row">
            <div class="col-2-3">
                @if($coverUrl)
                    <div class="cover"><img src="{{ $coverUrl }}" alt=""></div>
                @endif
                <h1 class="title">{{ $title }}</h1>
                <div class="loc">📍 {{ $loc }}</div>
                @if($desc !== '')
                    <div class="desc">{{ \Illuminate\Support\Str::limit($desc, 600) }}</div>
                @endif
            </div>
            <div class="col-1-3">
                <div class="qr-box">
                    <img src="{{ $qrUrl }}" alt="QR">
                    <div class="lbl">Scan for full listing</div>
                </div>
                <table class="grid" style="margin-top:10px">
                    @if($listing->category)
                        <tr><td><span class="pill">{{ optional($listing->category->getCurrentDescription())->name ?? '#' . $listing->category_id }}</span></td></tr>
                    @endif
                </table>
            </div>
        </div>
    @else
        {{-- ============ Portrait layout (default) ============ --}}
        @if($coverUrl)
            <div class="cover"><img src="{{ $coverUrl }}" alt=""></div>
        @endif

        <h1 class="title">{{ $title }}</h1>
        <div class="loc">📍 {{ $loc }}</div>
        @if($desc !== '')
            <div class="desc">{{ \Illuminate\Support\Str::limit($desc, 700) }}</div>
        @endif

        <table class="grid">
            <tr>
                <td style="width:65%">
                    @if($listing->category)
                        <span class="pill">{{ optional($listing->category->getCurrentDescription())->name ?? '#' . $listing->category_id }}</span>
                    @endif
                    {{-- Print up to 8 attribute values as pills. --}}
                    @foreach(($listing->attributeValues ?? collect())->take(8) as $av)
                        <span class="pill">{{ $av->value ?? '' }}</span>
                    @endforeach
                </td>
                <td style="width:35%; text-align:right">
                    <div class="qr-box" style="display:inline-block;">
                        <img src="{{ $qrUrl }}" alt="QR">
                        <div class="lbl">Scan for details</div>
                    </div>
                </td>
            </tr>
        </table>
    @endif

    <div class="ftr">
        <div class="lft">
            <span class="strap">{{ $siteName }}</span>
            @if($contactPhone) · ☎ {{ $contactPhone }} @endif
            @if($contactEmail) · ✉ {{ $contactEmail }} @endif
        </div>
        <div class="rht">{{ now()->format('d.m.Y') }}</div>
        <div style="clear:both"></div>
    </div>
</body>
</html>
