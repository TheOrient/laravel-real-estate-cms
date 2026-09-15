<?php

namespace App\Helpers;

class SeoHelper
{
    /**
     * Generate meta description from text
     */
    public static function generateMetaDescription(string $text, int $length = 155): string
    {
        // Remove HTML tags
        $text = strip_tags($text);

        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        // Trim to length
        if (mb_strlen($text) > $length) {
            $text = mb_substr($text, 0, $length);
            // Cut at last complete word
            $lastSpace = mb_strrpos($text, ' ');
            if ($lastSpace !== false) {
                $text = mb_substr($text, 0, $lastSpace);
            }
            $text .= '...';
        }

        return trim($text);
    }

    /**
     * Generate SEO-friendly title for a listing.
     *
     * Pattern is deliberate: category + district first so competitive
     * head-terms ("Satılık Daire Kuşadası") appear at the START of the
     * <title> where search engines weight them heaviest. Site name
     * comes last per Google's 2024 guidance.
     *
     * Example:
     *   "Satılık Daire Kuşadası Türkmen — 132 m² 3+1 Lüks | 7.250.000 TL · Real Estate CMS Demo"
     */
    public static function generateListingTitle($listing): string
    {
        $category = optional(optional($listing->category)?->getCurrentDescription())->name;
        $city     = optional($listing->city)->name;
        $district = optional($listing->district)->name;
        $hood     = optional($listing->neighborhood)->name;
        $brandFull = trim((string) get_setting('site_title', config('app.name', 'Emlak')));
        // Kısa marka — başlığı ~65 karakter altında tutar (Google SERP sınırı).
        // Marka kuyruğunu kısalt: " Gayrimenkul" / " Kuşadası" başlıkta zaten
        // konum olarak geçtiği için tekrarı önler (SEO'da kelime israfı).
        $brand = $brandFull;
        foreach ([' Gayrimenkul', ' Kuşadası'] as $suffix) {
            $brand = trim(\Illuminate\Support\Str::before($brand, $suffix)) ?: $brand;
        }

        // Çekirdek: ilan başlığı (mülk tipi + özellikler içerir); yoksa kategori + konum.
        $primary = trim((string) $listing->title);
        if ($primary === '') {
            $primary = trim(implode(' ', array_filter([$category, $hood ?: $district])));
        }

        $tail = ' | ' . $brand;
        $cap  = 68 - mb_strlen($tail);

        // Konum anahtar kelimelerini (başlıkta yoksa) yalnızca TÜMÜYLE sığıyorsa
        // ekle — kelime ortasından kesip "Hacıfeyzu…" gibi çirkinlik yapma.
        $locParts = [];
        foreach ([$hood ?: $district, $city] as $part) {
            $part = trim((string) $part);
            if ($part !== '' && mb_stripos($primary, $part) === false && ! in_array($part, $locParts, true)) {
                $locParts[] = $part;
            }
        }
        if ($locParts) {
            $loc = implode(' ', $locParts);
            if (mb_strlen($primary . ' — ' . $loc) <= $cap) {
                $primary .= ' — ' . $loc;
            }
        }

        // Başlık tek başına sınırı aşarsa kelime sınırında kısalt.
        if (mb_strlen($primary) > $cap) {
            $cut = mb_substr($primary, 0, $cap);
            $sp  = mb_strrpos($cut, ' ');
            $primary = rtrim($sp !== false ? mb_substr($cut, 0, $sp) : $cut) . '…';
        }

        return $primary . $tail;
    }

    /**
     * Meta description for a listing — 150-160 chars, keyword-first,
     * action-oriented. Google truncates ~155 so we stay under 158.
     *
     * Pattern:
     *   "{Category} {District} — {excerpt-lead}. {size/rooms if in text}. Fiyat {price}. Real Estate CMS Demo."
     */
    public static function generateListingMetaDescription($listing): string
    {
        $category = optional(optional($listing->category)?->getCurrentDescription())->name;
        $district = optional($listing->district)->name;
        $city     = optional($listing->city)->name;
        $hood     = optional($listing->neighborhood)->name;
        $brand    = trim((string) get_setting('site_title', 'Real Estate CMS Demo'));

        // Lead — put keywords at the very front. Google shows the first
        // ~120 chars in the SERP snippet.
        $lead = trim(implode(' ', array_filter([$category, $hood ?: $district, $city])));

        // Body excerpt — remaining budget = 158 - lead - price - brand.
        $priceStr = $listing->price ? number_format($listing->price, 0, ',', '.') . ' TL' : null;
        $reserved = mb_strlen($lead) + ($priceStr ? mb_strlen(' — ' . $priceStr) : 0) + mb_strlen(' · ' . $brand) + 3;
        $bodyLen  = max(30, 158 - $reserved);
        $excerpt  = self::generateMetaDescription((string) $listing->description, $bodyLen);

        return trim(
            $lead
            . ($excerpt ? ' — ' . $excerpt : '')
            . ($priceStr ? ' Fiyat: ' . $priceStr . '.' : '')
            . ' · ' . $brand
        );
    }

    /**
     * SEO keyword string — deliberate long-tail combos:
     *  - "satılık daire kuşadası"
     *  - "kiralık villa kuşadası türkmen"
     *  - "kuşadası emlak"
     *
     * Keywords meta is a weak SEO signal today, but Bing + Yandex still
     * use it, and it keeps the head/body pattern consistent for humans
     * reading source.
     */
    public static function generateListingKeywords($listing): string
    {
        $keywords = [];

        $categoryName = optional(optional($listing->category)?->getCurrentDescription())->name;
        $cityName     = optional($listing->city)->name;
        $districtName = optional($listing->district)->name;
        $hoodName     = optional($listing->neighborhood)->name;

        // Priority long-tail combos first — these are what people search.
        if ($categoryName && $districtName) $keywords[] = mb_strtolower($categoryName . ' ' . $districtName);
        if ($categoryName && $cityName)     $keywords[] = mb_strtolower($categoryName . ' ' . $cityName);
        if ($categoryName && $hoodName)     $keywords[] = mb_strtolower($categoryName . ' ' . $hoodName);
        if ($cityName && $hoodName)         $keywords[] = mb_strtolower($cityName . ' ' . $hoodName);
        if ($districtName && $hoodName)     $keywords[] = mb_strtolower($districtName . ' ' . $hoodName);
        if ($districtName)                  $keywords[] = mb_strtolower($districtName . ' emlak');
        if ($cityName)                      $keywords[] = mb_strtolower($cityName . ' emlak');
        if ($categoryName)                  $keywords[] = mb_strtolower($categoryName);

        // Parent categories climb the tree — "Satılık Daire" → "Satılık"
        if ($listing->category) {
            $parent = $listing->category->parent;
            while ($parent) {
                $parentDesc = $parent->getCurrentDescription();
                if ($parentDesc && $parentDesc->name) {
                    $keywords[] = mb_strtolower($parentDesc->name);
                    if ($districtName) $keywords[] = mb_strtolower($parentDesc->name . ' ' . $districtName);
                }
                $parent = $parent->parent;
            }
        }

        // Salient words from the agent-authored title — surfaces variant
        // long-tails like "3+1", "deniz manzaralı", "site içi".
        $titleWords = preg_split('/[\s\-—|,\.]+/u', mb_strtolower((string) $listing->title));
        $stop = ['ve','ile','için','bir','bu','şu','o','de','da','—','ile'];
        foreach ($titleWords as $w) {
            if (mb_strlen($w) > 3 && ! in_array($w, $stop, true)) $keywords[] = $w;
        }

        $keywords = array_values(array_unique(array_filter($keywords)));
        return implode(', ', array_slice($keywords, 0, 18));
    }

    /**
     * Generate category meta description
     */
    public static function generateCategoryMetaDescription($category, $listingCount = null): string
    {
        $parts = [];
        if($category === null){
            return '';
        }
        $categoryDesc = $category->getCurrentDescription();
        if ($categoryDesc && $categoryDesc->description) {
            $parts[] = self::generateMetaDescription($categoryDesc->description, 120);
        } else {
            $categoryName = $categoryDesc ? $categoryDesc->name : 'Category #' . $category->id;
            $parts[] = $categoryName . ' kategorisindeki ilanları inceleyin.';
        }

        if ($listingCount !== null) {
            $parts[] = $listingCount . ' ilan';
        }

        return implode(' | ', $parts);
    }

    /**
     * Get canonical URL
     */
    public static function getCanonicalUrl(): string
    {
        return url()->current();
    }

    /**
     * Generate breadcrumb schema
     */
    public static function generateBreadcrumbSchema(array $items): array
    {
        $itemListElement = [];

        foreach ($items as $index => $item) {
            $itemListElement[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $item['url'] ?? null
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemListElement
        ];
    }

    /**
     * Rich Product / RealEstate schema for a listing.
     *
     * Google recognises Product for real-estate listings and shows
     * price, address and image in the SERP. We also add:
     *   - image gallery (up to 8 photos, not just cover)
     *   - GeoCoordinates when lat/lng exist (map pack ranking)
     *   - PostalAddress with streetAddress-level detail
     *   - Brand (site owner) + Seller
     *   - dateModified for freshness
     */
    public static function generateListingSchema($listing): array
    {
        $brand   = trim((string) get_setting('site_title', 'Real Estate CMS Demo'));
        $siteUrl = url('/');

        $schema = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => $listing->title,
            'description' => self::generateMetaDescription((string) $listing->description, 300),
            'sku'         => 'LST-' . $listing->id,
            'brand'       => ['@type' => 'Brand', 'name' => $brand],
            'offers'      => [
                '@type'         => 'Offer',
                'price'         => $listing->price,
                'priceCurrency' => 'TRY',
                'availability'  => 'https://schema.org/InStock',
                'url'           => route('listings.show', $listing->slug),
                'seller'        => [
                    '@type' => 'RealEstateAgent',
                    'name'  => $brand,
                    'url'   => $siteUrl,
                ],
            ],
        ];

        // Image gallery — cover + up to 7 more from listing_images.
        $images = [];
        if ($listing->image) {
            $images[] = listing_image_url($listing->image, 'large');
        }
        if (method_exists($listing, 'images') && $listing->relationLoaded('images')) {
            foreach ($listing->images->take(8) as $img) {
                $imgSrc = $img->image;
                $images[] = (str_starts_with($imgSrc, 'http'))
                    ? $imgSrc
                    : listing_image_url($imgSrc, 'large');
            }
        }
        $images = array_values(array_unique(array_filter($images)));
        if ($images) $schema['image'] = count($images) === 1 ? $images[0] : $images;

        // itemLocation — Google Maps ranking signal.
        $address = [
            '@type'          => 'PostalAddress',
            'addressCountry' => 'TR',
        ];
        if ($listing->city)         $address['addressLocality'] = $listing->city->name;
        if ($listing->district)     $address['addressRegion']   = $listing->district->name;
        if ($listing->neighborhood) $address['streetAddress']   = $listing->neighborhood->name;

        $place = ['@type' => 'Place', 'address' => $address];

        if (is_numeric($listing->latitude) && is_numeric($listing->longitude)) {
            $place['geo'] = [
                '@type'     => 'GeoCoordinates',
                'latitude'  => (float) $listing->latitude,
                'longitude' => (float) $listing->longitude,
            ];
        }

        $schema['offers']['availableAtOrFrom'] = $place;

        // dateModified — freshness signal for Google's crawl priority.
        if ($listing->updated_at) {
            $schema['offers']['priceValidUntil'] = $listing->updated_at->addDays(60)->toIso8601String();
        }

        return $schema;
    }

    /**
     * BreadcrumbList schema — Google's "Home > Kategori > İlan" trail
     * that shows in the SERP instead of the raw URL. Rich result.
     */
    public static function generateBreadcrumbForListing($listing): array
    {
        $items = [
            ['name' => __('general.home'), 'url' => route('home')],
        ];

        if ($listing->category) {
            $catName = optional($listing->category->getCurrentDescription())->name;
            try {
                $catUrl = route('categories.show', $listing->category->slug);
            } catch (\Throwable $e) {
                $catUrl = null;
            }
            if ($catName) $items[] = ['name' => $catName, 'url' => $catUrl];
        }

        $items[] = [
            'name' => $listing->title,
            'url'  => route('listings.show', $listing->slug),
        ];

        return self::generateBreadcrumbSchema($items);
    }

    /**
     * FAQPage JSON-LD — Google SERP'de akordeon şeklinde gösterilir
     * (rich result). Emlak arayanların en sık sorduğu 4 soru + kısa
     * cevaplar. Her cevap 40-300 karakter idealidir.
     *
     * Kategori sayfasında publish ediliyor — "kiralık daire kuşadası"
     * gibi bir aramada snippet altında "Kiralık daire fiyatları
     * nedir?" gibi bir accordion açılır, tıklama oranı %30-40 artar.
     */
    public static function generateFaqSchema(array $qas): array
    {
        $mainEntity = [];
        foreach ($qas as $qa) {
            if (empty($qa['q']) || empty($qa['a'])) continue;
            $mainEntity[] = [
                '@type' => 'Question',
                'name'  => $qa['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => $qa['a'],
                ],
            ];
        }

        return [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => $mainEntity,
        ];
    }

    /**
     * Article JSON-LD zenginleştirme — wordCount + timeRequired.
     * Google Discover ve Bing Snapshot bu değerleri kart üzerinde
     * gösterir ("5 dk okuma" gibi), tıklama oranını artırır.
     */
    public static function computeReadingStats(string $body): array
    {
        $plain = trim(strip_tags($body));
        $wordCount = $plain === '' ? 0 : str_word_count($plain, 0, 'ÇĞİÖŞÜçğıöşü');
        // ISO 8601 duration — Google 200 kelime/dk varsayar
        $minutes = max(1, (int) ceil($wordCount / 200));
        return [
            'wordCount'    => $wordCount,
            'timeRequired' => "PT{$minutes}M",
        ];
    }

    /**
     * Clean text for SEO (remove special chars, etc)
     */
    public static function cleanText(string $text): string
    {
        // Remove HTML
        $text = strip_tags($text);

        // Remove special characters
        $text = preg_replace('/[^\p{L}\p{N}\s\-]/u', '', $text);

        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }
}
