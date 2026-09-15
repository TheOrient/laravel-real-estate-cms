<?php

namespace Database\Seeders;

use App\Helpers\CategoryHelper;
use App\Models\Blog;
use App\Models\BlogDescription;
use App\Models\Category;
use App\Models\City;
use App\Models\District;
use App\Models\Language;
use App\Models\Listing;
use App\Models\ListingDescription;
use App\Models\ListingImage;
use App\Models\Neighborhood;
use App\Models\Page;
use App\Models\PageDescription;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Fictional demo content for a fresh installation.
 *
 * First-install sample content seeder. Refuses existing listings + blog posts,
 * inserts a curated Kuşadası catalog, fills missing CMS page bodies
 * and rewrites brand-facing settings (site title, contact, footer).
 *
 *   php artisan db:seed --class=SampleContentSeeder
 *
 * Never run this as a deployment/update step.
 */
class SampleContentSeeder extends Seeder
{
    /** Anchor city/district names used everywhere downstream. */
    protected const CITY_NAME = 'Aydın';

    protected const DISTRICT_NAME = 'Kuşadası';

    public function run(bool $preserveExistingBlogs = false): void
    {
        $hasBlogs = Blog::withTrashed()->exists();
        if (Listing::withTrashed()->exists() || ($hasBlogs && ! $preserveExistingBlogs)) {
            throw new RuntimeException(__('installation.existing_database'));
        }

        $this->command->info('Fictional demo seed starting…');

        $defaultLanguage = Language::where('is_default', true)->first()
            ?? Language::orderBy('id')->first();
        if (! $defaultLanguage) {
            $this->command->error('No language row — run LanguageSeeder first.');

            return;
        }

        $admin = User::where('user_role', 'admin')->first() ?? User::first();
        if (! $admin) {
            $this->command->error('No user found to author content.');

            return;
        }

        $this->ensureKusadasiTaxonomy();

        $this->seedListings($admin, $defaultLanguage);
        if (! $hasBlogs) {
            $this->seedBlogs($admin, $defaultLanguage);
        }
        $this->seedBrandSettings();
        $this->seedPageBodies($defaultLanguage);
        $this->refreshCategoryCounts();

        $this->command->info('Done. Refresh the home page.');
    }

    /**
     * After listings change, recompute the cached `listings_count`
     * column on every category so the admin dashboard widget shows
     * truthful numbers instead of inherited marketplace placeholders.
     */
    protected function refreshCategoryCounts(): void
    {
        if (! class_exists(CategoryHelper::class)) {
            return;
        }

        foreach (Category::all() as $category) {
            try {
                CategoryHelper::updateCategoryCount($category->id);
            } catch (\Throwable $e) {
                // best-effort; don't fail the whole seed for one row
            }
        }
        $this->command->info('Category listing counts refreshed.');
    }

    /* ============================================================ */
    /*  Kuşadası taxonomy */
    /* ============================================================ */

    protected function ensureKusadasiTaxonomy(): void
    {
        $city = City::firstOrCreate(
            ['name' => self::CITY_NAME],
            ['slug' => Str::slug(self::CITY_NAME), 'is_active' => true]
        );

        $district = District::firstOrCreate(
            ['city_id' => $city->id, 'name' => self::DISTRICT_NAME],
            ['slug' => Str::slug(self::DISTRICT_NAME), 'is_active' => true]
        );

        // Office service areas shown in location filters and listing forms.
        $hoods = ['Türkmen', 'Hacıfeyzullah', 'Kadınlar Denizi', 'Davutlar', 'Güzelçamlı', 'Karaova', 'Soğucak'];
        foreach ($hoods as $h) {
            Neighborhood::firstOrCreate(
                ['district_id' => $district->id, 'name' => $h],
                ['slug' => Str::slug($h), 'is_active' => true]
            );
        }

        $this->command->info("Ensured city='{$city->name}', district='{$district->name}', {$district->neighborhoods()->count()} mahalle.");
    }

    /* ============================================================ */
    /*  Listings */
    /* ============================================================ */

    protected function seedListings(User $admin, Language $lang): void
    {
        $city = City::where('name', self::CITY_NAME)->first();
        $district = $city ? District::where('city_id', $city->id)->where('name', self::DISTRICT_NAME)->first() : null;

        if (! $city || ! $district) {
            $this->command->error('Kuşadası taxonomy is missing. Aborting listings.');

            return;
        }

        $hoods = Neighborhood::where('district_id', $district->id)->get()->keyBy('name');

        // Image bank — broken into themed groups so each listing can
        // pull a coherent gallery (cover + interior + bathroom + view +
        // detail shots) instead of a single random photo. All Unsplash
        // royalty-free URLs; the listing_image_url helper bypasses
        // Glide for external URLs so they load directly.
        $imgLuxuryApartment = [
            'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=1600&q=80', // modern living room
            'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1600&q=80', // bright apartment exterior
            'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?auto=format&fit=crop&w=1600&q=80', // open kitchen
            'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=1600&q=80', // dining area
            'https://images.unsplash.com/photo-1616594039964-ae9021a400a0?auto=format&fit=crop&w=1600&q=80', // bedroom
            'https://images.unsplash.com/photo-1552321554-5fefe8c9ef14?auto=format&fit=crop&w=1600&q=80', // bathroom
            'https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=1600&q=80', // balcony sea view
        ];

        $imgVilla = [
            'https://images.unsplash.com/photo-1613977257363-707ba9348227?auto=format&fit=crop&w=1600&q=80', // villa exterior
            'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1600&q=80', // pool side
            'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=1600&q=80', // garden
            'https://images.unsplash.com/photo-1600210491892-03d54c0aaf87?auto=format&fit=crop&w=1600&q=80', // luxury kitchen
            'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&w=1600&q=80', // master bedroom
            'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&w=1600&q=80', // outdoor terrace
            'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1600&q=80', // bathroom marble
        ];

        $imgRentalCompact = [
            'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=1600&q=80', // cozy living
            'https://images.unsplash.com/photo-1493809842364-78817add7ffb?auto=format&fit=crop&w=1600&q=80', // compact apartment
            'https://images.unsplash.com/photo-1554995207-c18c203602cb?auto=format&fit=crop&w=1600&q=80', // small kitchen
            'https://images.unsplash.com/photo-1540518614846-7eded433c457?auto=format&fit=crop&w=1600&q=80', // bedroom corner
            'https://images.unsplash.com/photo-1604014237800-1c9102c219da?auto=format&fit=crop&w=1600&q=80', // bathroom small
            'https://images.unsplash.com/photo-1560185007-c5ca9d2c014d?auto=format&fit=crop&w=1600&q=80', // entry hall
        ];

        $imgNewBuild = [
            'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1600&q=80', // new build facade
            'https://images.unsplash.com/photo-1600573472556-e636c2acda88?auto=format&fit=crop&w=1600&q=80', // empty modern living
            'https://images.unsplash.com/photo-1600121848594-d8644e57abab?auto=format&fit=crop&w=1600&q=80', // fitted kitchen
            'https://images.unsplash.com/photo-1631679706909-1844bbd07221?auto=format&fit=crop&w=1600&q=80', // bedroom
            'https://images.unsplash.com/photo-1620626011761-996317b8d101?auto=format&fit=crop&w=1600&q=80', // balcony
            'https://images.unsplash.com/photo-1564540583246-934409427776?auto=format&fit=crop&w=1600&q=80', // bathroom
        ];

        $imgDetachedHouse = [
            'https://images.unsplash.com/photo-1568605114967-8130f3a36994?auto=format&fit=crop&w=1600&q=80', // detached stone house
            'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&w=1600&q=80', // detached house exterior
            'https://images.unsplash.com/photo-1600585154363-67eb9e2e2099?auto=format&fit=crop&w=1600&q=80', // rustic kitchen
            'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1600&q=80', // house terrace
            'https://images.unsplash.com/photo-1502005229762-cf1b2da7c5d6?auto=format&fit=crop&w=1600&q=80', // wood interior
            'https://images.unsplash.com/photo-1583847268964-b28dc8f51f92?auto=format&fit=crop&w=1600&q=80', // bedroom rustic
        ];

        $samples = [
            [
                'title' => 'Türkmen Mahallesi Tam Deniz Manzaralı 3+1 Lüks Daire',
                'category' => 'Satılık Daire',
                'hood' => 'Türkmen',
                'price' => 7250000,
                'featured' => true,
                'lat' => 37.8579, 'lng' => 27.2610,
                'gallery' => $imgLuxuryApartment,
                'desc' => "Kuşadası Türkmen Mahallesinde, Kadınlar Denizi mevkiine 700 metre mesafede, tam deniz manzaralı 3+1 (155 m² brüt / 132 m² net) lüks daire. Geniş salondan birinci derecede deniz ve marina manzarası, çift balkon, ebeveyn banyolu yatak odası, ankastre Bosch mutfak ve ısıcamlı doğrama.\n\nSite içinde açık ve kapalı yüzme havuzu, fitness salonu, çocuk oyun parkı ve 7/24 özel güvenlik bulunmaktadır. Otopark hakkı 1 araç + 1 motosiklet. Kuşadası AVM ve Adnan Menderes Bulvarı yürüme mesafesinde, Kuşadası Marina'ya 2 km, Aydın şehir merkezine 80 km, İzmir Adnan Menderes Havalimanı'na 65 km mesafededir.\n\nDaire 8 yıl önce inşa edilmiş, son 2 yılda komple boya–badana ve mutfak yenilemesi yapılmıştır. Tapu hazır, kat irtifaklı, krediye uygundur. Aidat 2.500 TL, doğalgaz mevcuttur. Yatırım amaçlı alıcılar için yaz aylarında yüksek kira getirisi, oturum amaçlı aileler için ideal bir lokasyon.",
            ],
            [
                'title' => 'Güzelçamlı\'da Bahçeli Müstakil Yazlık Villa',
                'category' => 'Satılık Villa',
                'hood' => 'Güzelçamlı',
                'price' => 13900000,
                'featured' => true,
                'lat' => 37.7144, 'lng' => 27.1842,
                'gallery' => $imgVilla,
                'desc' => "Dilek Yarımadası Milli Parkı'na 1,5 km, Güzelçamlı sahiline 600 metre mesafede 820 m² müstakil arsa üzerinde 290 m² oturma alanlı iki katlı villa. Özel yüzme havuzu, taş ocaktan beslenen şömine, narenciye ve zeytin bahçesi ile huzurlu bir yaşam alanı.\n\n4 yatak odası (1 master + walk-in closet), 3 banyo, ada tipi mutfak, doğal taş zemin. Sondaj suyu, hibrit güneş enerjisi sistemi, akıllı ev altyapısı ve kapalı 2 araçlık garaj mevcuttur. Yapı 2018'de tamamlanmış, ruhsat ve iskan eksiksizdir.\n\nYıllık site aidatı oldukça düşüktür. Yatırım için yaz aylarında Airbnb / haftalık kiralama getirisi yüksek seviyededir. Türk vatandaşları ve yabancı uyruklu alıcılara açık. Tapu hazır, devir süreci 7 gün içinde tamamlanabilmektedir.",
            ],
            [
                'title' => 'Marina Yakını 1+1 Kiralık Eşyalı Eko Daire',
                'category' => 'Kiralık Daire',
                'hood' => 'Hacıfeyzullah',
                'price' => 32000,
                'featured' => false,
                'lat' => 37.8607, 'lng' => 27.2576,
                'gallery' => $imgRentalCompact,
                'desc' => "Hacıfeyzullah Mahallesinde, Kuşadası Marina'ya 5 dakika yürüme mesafesinde tamamen yenilenmiş 1+1 eşyalı daire. 68 m² brüt, ankastre buzdolabı, bulaşık makinesi, fırın, ocak ve kombi dahildir. Yatak odası ve salon kaliteli ahşap mobilyalarla dekore edilmiştir.\n\nBina asansörlü, kapıcılı ve doğalgaz kombili. Aidat 1.800 TL'dir. Depozito 1 aylık kira tutarı, sözleşme süresi minimum 1 yıldır. Güvercinada, ESBAŞ, gece pazarı ve şehir merkezi yürüme mesafesindedir.\n\nÇalışan profesyoneller, çiftler veya uzun süreli ziyaretçi öğrenciler için uygundur. Evcil hayvan kabul edilmemektedir. Anında giriş mümkündür. Klima, internet ve uydu altyapısı hazırdır.",
            ],
            [
                'title' => 'Davutlar\'da Yeni Yapı 2+1 Daire — Krediye Uygun',
                'category' => 'Satılık Daire',
                'hood' => 'Davutlar',
                'price' => 4350000,
                'featured' => false,
                'lat' => 37.7340, 'lng' => 27.2280,
                'gallery' => $imgNewBuild,
                'desc' => "Davutlar sahiline 850 m, market ve restoranlara yürüme mesafesinde 2+1 (108 m² brüt) yeni yapı daire. Geçen yıl tamamlanan modern site içinde, bina yaşı 1. Otomatik garaj kapısı, akıllı ev altyapısı, çift cepheli salon ve 10 m² balkon ile aydınlık bir yaşam alanı.\n\nSite içinde havuz, sauna, fitness salonu ve görevli bulunmaktadır. Site bir önceki yıl teslim edildiği için aidat seviyesi düşüktür (1.400 TL). Tüm bankalar için konut kredisine uygun, ekspertiz değeri satış fiyatının %95'i seviyesindedir.\n\n%35 peşinatla anında devir mümkündür. İleride aile büyümesine elverişli, ilkokul ve sağlık ocağına yürüme mesafesinde. Adnan Menderes Üniversitesi Söke Kampüsü 25 dakika uzaklıkta. İlk evini alacak aileler için ideal.",
            ],
            [
                'title' => 'Soğucak\'ta Doğa İçinde Bahçeli Müstakil Ev',
                'category' => 'Satılık Müstakil Ev',
                'hood' => 'Soğucak',
                'price' => 6800000,
                'featured' => false,
                'lat' => 37.8168, 'lng' => 27.2960,
                'gallery' => $imgDetachedHouse,
                'desc' => "Kuşadası'nın doğa cenneti Soğucak bölgesinde, 1.200 m² arazi içinde 175 m² oturma alanlı tek katlı taş ev. 3 yatak odası, geniş yaşam alanı, beton döşeme + ahşap dekorasyon. Bahçede üzüm asması, zeytin ve nar ağaçları, sebze bahçesi için ayrılmış alan.\n\nKuşadası şehir merkezine 12 km, Davutlar sahiline 9 km. Sondaj suyu, üç fazlı elektrik, doğalgaz altyapısı mevcut. Bölge yolu asfalttır, 4 mevsim ulaşılabilirdir. Yaz aylarında haftalık tatil kiralama (Airbnb), kış aylarında huzurlu köy yaşamı için ideal.\n\nEv 2015'te tamamlanmıştır. Tapu hazır, iskanlı, krediye uygundur. Hayvan dostu, geniş kapalı garaj ve depo bulunmaktadır. Doğa ile iç içe yaşam isteyen aileler veya emeklilik için yatırım yapan alıcılar için fırsat.",
            ],
        ];

        foreach ($samples as $s) {
            $category = Category::whereHas('descriptions', function ($q) use ($s, $lang) {
                $q->where('name', $s['category'])->where('language_id', $lang->id);
            })->first();
            if (! $category) {
                $this->command->warn("Skip — category '{$s['category']}' missing.");

                continue;
            }

            $hood = $hoods->get($s['hood']);

            // Cover image = first gallery photo, falls back to legacy
            // single-image field for compatibility with views that read
            // `$listing->image` directly.
            $gallery = $s['gallery'] ?? [];
            $cover = $gallery[0] ?? ($s['image'] ?? null);

            $listing = Listing::create([
                'price' => $s['price'],
                'category_id' => $category->id,
                'user_id' => $admin->id,
                'city_id' => $city->id,
                'district_id' => $district->id,
                'neighborhood_id' => $hood?->id,
                'latitude' => $s['lat'] ?? null,
                'longitude' => $s['lng'] ?? null,
                'status' => 'active',
                'is_active' => true,
                'is_approved' => true,
                'is_featured' => $s['featured'] ?? false,
                'approved_at' => now(),
                'image' => $cover,
                'expires_at' => now()->addDays(60),
            ]);

            ListingDescription::create([
                'listing_id' => $listing->id,
                'language_id' => $lang->id,
                'title' => '[DEMO] '.$s['title'],
                'slug' => Str::slug('demo-'.$s['title']).'-'.$listing->id,
                'description' => "DEMO KAYIT: Bu ilan, fiyatı, konumu ve tüm özellikleri tamamen kurgusaldır.\n\n".$s['desc'],
            ]);

            // Multi-image gallery — first photo flagged primary, sort
            // order preserved. The card gallery + detail page will
            // render these as a swipeable carousel.
            if (Schema::hasTable('listing_images') && ! empty($gallery)) {
                foreach ($gallery as $idx => $imgUrl) {
                    ListingImage::create([
                        'listing_id' => $listing->id,
                        'image' => $imgUrl,
                        'is_primary' => $idx === 0,
                        'sort_order' => $idx,
                        'is_active' => true,
                    ]);
                }
            }
        }

        $this->command->info('Inserted '.count($samples).' Kuşadası listings.');
    }

    /* ============================================================ */
    /*  Blog posts */
    /* ============================================================ */

    protected function seedBlogs(User $admin, Language $lang): void
    {
        $posts = $this->blogPostsPayload();

        foreach ($posts as $p) {
            $blog = Blog::create([
                'user_id' => $admin->id,
                'image' => $p['cover'],
                'status' => 'published',
                'is_active' => true,
                'is_featured' => $p['featured'] ?? false,
                'view_count' => rand(180, 1450),
                'published_at' => now()->subDays($p['days_ago']),
            ]);

            BlogDescription::create([
                'blog_id' => $blog->id,
                'language_id' => $lang->id,
                'title' => '[DEMO] '.$p['title'],
                'slug' => Str::slug('demo-'.$p['title']).'-'.$blog->id,
                'excerpt' => 'Demo içeriktir; gerçek bir piyasa veya yatırım tavsiyesi değildir. '.$p['excerpt'],
                'body' => '<p><strong>Demo içerik:</strong> Bu yazı yalnızca CMS özelliklerini göstermek için hazırlanmış kurgusal örnek metindir.</p>'.$p['body'],
                'meta_title' => '[DEMO] '.$p['title'],
                'meta_description' => $p['excerpt'],
                'meta_keywords' => $p['keywords'] ?? null,
            ]);
        }
        $this->command->info('Inserted '.count($posts).' Kuşadası-themed blog posts.');
    }

    /* ============================================================ */
    /*  Brand settings (the values shown in the navbar / footer) */
    /* ============================================================ */

    protected function seedBrandSettings(): void
    {
        // Only overwrite the brand-facing fields — leave AI keys etc.
        $brand = [
            'site_title' => 'Real Estate CMS Demo',
            'site_description' => 'Kurumsal gayrimenkul sitesi ve ilan yönetim sistemi için kurgusal demo kurulum.',
            'site_url' => env('APP_URL', 'http://localhost:8000'),
            'footer_copyright_text' => 'Tüm hakları saklıdır.',
            'contact_email' => 'demo@example.test',
            'contact_phone' => '',
            'contact_address' => 'Kuşadası / Aydın (Demo)',
            // Brand-facing social URLs left blank by default so a fresh
            // install doesn't link to placeholder accounts. Operators
            // fill them in via /admin/settings.
            'social_facebook_url' => '',
            'social_instagram_url' => '',
            'social_youtube_url' => '',
            'social_twitter_url' => '',
            'chatbot_enabled' => '1',
            // Legacy marketplace stat — irrelevant for a single
            // office. Cleared so admin/settings shows 0 instead of a
            // fake inflated count.
            'footer_active_listings_count' => '0',
        ];

        foreach ($brand as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        $this->command->info('Demo brand settings updated.');
    }

    /* ============================================================ */
    /*  CMS pages (about, contact, etc.) — fill brand bodies */
    /* ============================================================ */

    protected function seedPageBodies(Language $lang): void
    {
        $bodies = [
            'hakkimizda' => [
                'title' => 'Hakkımızda',
                'meta' => 'Real Estate CMS Demo — Kuşadası ve çevresinde yerel gayrimenkul danışmanlığı.',
                'body' => <<<'HTML'
<h2>Real Estate CMS Demo</h2>
<p><strong>Real Estate CMS Demo</strong>, Kuşadası ve çevresinde seçilmiş portföy, yerel piyasa bilgisi ve şeffaf iletişimle hizmet veren bir gayrimenkul ofisidir.</p>

<h3>Misyonumuz</h3>
<p>Kuşadası'nın hızla değişen gayrimenkul piyasasında alıcı ve satıcılara güvenle yön gösteren, ihtiyaca özel çözümler üreten bir köprü olmak.</p>

<h3>Vizyonumuz</h3>
<p>Ege kıyısında "akıllı yatırım" denilince akla gelen ilk gayrimenkul markası olmak, yerel sahipliği koruyarak büyümek.</p>

<h3>Neden Real Estate CMS Demo?</h3>
<ul>
    <li>Kuşadası mahalleleri ve yerel piyasa dinamikleri hakkında bölgesel bilgi</li>
    <li>Güzelçamlı'dan Davutlar'a, Türkmen ve Hacıfeyzullah'tan Soğucak ve Karaova'ya kadar bölge bazında uzmanlaşma</li>
    <li>Portföy seçimi, gösterim ve teklif sürecinde düzenli koordinasyon</li>
    <li>İlan bilgilerinin açık ve güncel biçimde sunulması</li>
    <li>Alım, satım ve kiralama süreçlerinde düzenli bilgilendirme</li>
</ul>
HTML,
            ],
            'iletisim' => [
                'title' => 'İletişim',
                'meta' => 'Real Estate CMS Demo iletişim bilgileri — Kuşadası ofisimize ulaşın.',
                'body' => <<<'HTML'
<h2>Bize Ulaşın</h2>
<p>Portföy bilgisi, yerinde gösterim veya değerleme talebiniz için aşağıdaki iletişim kanallarını kullanabilirsiniz.</p>
<ul>
    <li><strong>Hizmet bölgesi:</strong> Kuşadası / Aydın</li>
    <li><strong>E-posta:</strong> demo@example.test</li>
    <li><strong>Görüşme:</strong> Randevulu ofis ve yerinde portföy görüşmesi</li>
</ul>
HTML,
            ],
            'kariyer' => [
                'title' => 'Kariyer',
                'meta' => 'Real Estate CMS Demo Kuşadası ekibinde açık pozisyonlar ve uzun vadeli iş birliği fırsatları.',
                'body' => <<<'HTML'
<h2>Real Estate CMS Demo Ekibine Katılın</h2>
<p>Gayrimenkul sektöründe kariyer yapmak isteyen, Kuşadası'nı yakından tanıyan ve insan ilişkilerini seven profesyonelleri ekibimize katılmaya davet ediyoruz. Türkmen, Hacıfeyzullah, Güzelçamlı, Davutlar, Soğucak ve Karaova bölgelerini bilen adaylara öncelik verilir.</p>
<h3>Açık Pozisyonlar</h3>
<ul>
    <li><strong>Gayrimenkul Danışmanı</strong> — deneyimli / aday</li>
    <li><strong>Yabancı müşteri ilişkileri uzmanı</strong> — İngilizce / Rusça</li>
    <li><strong>Pazarlama uzmanı</strong> — sosyal medya, içerik üretimi</li>
</ul>
<p>CV'nizi <strong>demo@example.test</strong> adresine gönderebilir; veya İletişim sayfasındaki formu kullanabilirsiniz.</p>
HTML,
            ],
            // "basin" sayfası tek-ofis kimliğine uygun değil (kurumsal
            // basın bölümü büyük markalara özgü); kaldırıldı — footer
            // linki de PageSeeder ile birlikte temizlenmiş durumda.
            'guvenli-alisveris' => [
                'title' => 'Güvenli Emlak İşlemleri',
                'meta' => 'Kuşadası gayrimenkul alım-satım ve kiralama süreçlerinde güvenlik ipuçları.',
                'body' => <<<'HTML'
<h2>Güvenli Gayrimenkul Alım-Satım</h2>
<p>Kuşadası gibi yoğun talep gören bir bölgede güvenli işlem için aşağıdaki adımları ihmal etmeyin:</p>
<ul>
    <li>Tapu inceleme: ipotek, haciz veya şerh kaydı olup olmadığını kontrol edin</li>
    <li>İskan ve yapı kullanma izninin tam olduğundan emin olun</li>
    <li>Kapora veya elden ödeme yapmayın — banka transferi tercih edin</li>
    <li>Bir avukat veya sertifikalı emlak danışmanından destek alın</li>
    <li>Sözleşmeleri mutlaka noter onaylı imzalayın</li>
</ul>
<p>Şüpheli bir durumda işlemi durdurun; resmî kurumlar veya ilgili yetkili uzmanlardan doğrulama alın.</p>
HTML,
            ],
            'yardim-merkezi' => [
                'title' => 'Yardım Merkezi',
                'meta' => 'Real Estate CMS Demo yardım merkezi — sık sorulan sorular.',
                'body' => <<<'HTML'
<h2>Yardım Merkezi</h2>

<h3>İlan vermek ücretli mi?</h3>
<p>Hayır. Bireysel ilan vermek tamamen ücretsiz. Profesyonel danışmanlarımız size pazarlama önerileri sunar.</p>

<h3>İlan onayı ne kadar sürer?</h3>
<p>Ekibimiz ilanları aynı gün içinde kontrol eder ve uygunsa yayınlar.</p>

<h3>Yabancı uyruklu müşterilere hizmet veriyor musunuz?</h3>
<p>Evet — İngilizce ve Rusça konuşan danışmanlarımız mevcut, vize ve tercüme süreçlerinde de destek veriyoruz.</p>

<h3>Krediye uygun mülkleri nasıl filtreliyorum?</h3>
<p>Ana sayfadaki arama formunda kategori ve fiyat aralığı belirleyerek filtre uygulayabilir veya danışmanımızdan yönlendirme alabilirsiniz.</p>
HTML,
            ],
        ];

        foreach ($bodies as $slug => $b) {
            $description = PageDescription::where('slug', $slug)->where('language_id', $lang->id)->first();
            if (! $description) {
                $this->command->warn("Page '{$slug}' description row missing; skipping.");

                continue;
            }
            $description->update([
                'title' => $b['title'],
                'meta_description' => $b['meta'],
                'content' => '<p><strong>Demo içerik:</strong> Bu sayfadaki kurum ve iletişim bilgileri tamamen kurgusaldır.</p>'.$b['body'],
            ]);
        }
        $this->command->info('CMS pages updated with fictional demo content.');
    }

    /* ============================================================ */
    /*  Blog payload — 6 long-form posts */
    /* ============================================================ */

    protected function blogPostsPayload(): array
    {
        return [
            [
                'title' => 'Kuşadası\'nda Emlak Yatırımına Başlangıç: 2026 Rehberi',
                'cover' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=1600&q=80',
                'days_ago' => 1,
                'featured' => true,
                'keywords' => 'kuşadası emlak yatırımı, gayrimenkul rehberi 2026, aydın yatırım',
                'excerpt' => 'Kuşadası gayrimenkul piyasasında ilk adımını atacaklara fiyat, bölge ve hukuki süreç açısından pratik bir başlangıç rehberi.',
                'body' => <<<'HTML'
<p>Kuşadası, son on yılda Türkiye gayrimenkulünde "Ege'nin en kararlı yatırım kenti" olarak konumlandı. Yaz turistinin ve emekli alıcı kitlesinin çeşitliliği, fiyatları diğer kıyı bölgelerine göre daha az dalgalandırıyor. Üstelik İzmir-Aydın otoyolu, marina genişlemesi ve uluslararası kruvaziyer trafiği bu istikrarı destekliyor.</p>

<h2>1. Önce niyetinizi belirleyin</h2>
<p>Kuşadası'nda iki ana yatırım stratejisi var: <strong>yaz kira getirisi</strong> ve <strong>uzun vadeli sermaye artışı</strong>. Yaz kira getirisi için Türkmen, Hacıfeyzullah veya Güzelçamlı'da deniz manzaralı 2+1 / 3+1 daireler avantajlı. Sermaye artışı için Davutlar ve Soğucak gibi ulaşımı henüz tam olgunlaşmamış ama imarı net bölgeler önde geliyor.</p>

<h2>2. Bütçeyi gerçekçi kurun</h2>
<p>Konut kredisi kullanacaksanız, aylık taksit gelirinizin <strong>%40'ını</strong> geçmemeli. Tapu harcı, DASK, abonelik, vergi ve danışmanlık komisyonu için ek <strong>%6 - %8</strong> ayırın. Real Estate CMS Demo olarak müşterilerimize finansman planını ilk görüşmede şeffafça paylaşıyoruz.</p>

<h2>3. Bölge bazlı fiyat aralıkları</h2>
<ul>
    <li><strong>Türkmen / Hacıfeyzullah:</strong> 2+1 daire 4 - 6 milyon TL, 3+1 deniz manzaralı 6 - 9 milyon TL</li>
    <li><strong>Davutlar:</strong> Yeni yapı 2+1 3 - 5 milyon TL, müstakil 8 - 14 milyon TL</li>
    <li><strong>Güzelçamlı:</strong> Yazlık villa 9 - 18 milyon TL, müstakil ev 6 - 11 milyon TL</li>
    <li><strong>Soğucak / Karaova:</strong> Doğa içinde tek katlı ev 3 - 7 milyon TL, arsa 800 bin - 2 milyon TL / dönüm</li>
</ul>

<h2>4. Yasal ve vergi süreçleri</h2>
<p>Tapu harcı %4 (alıcı ve satıcı eşit pay), DASK zorunlu. Yabancı uyruklu alıcılar için <strong>askeri uygunluk yazısı</strong> 2 - 3 hafta sürebilir; başvuruyu erken yapın. Uzun vadede satış değer artış kazancı vergisi 5 yıl içinde satışta uygulanır.</p>

<h2>5. Pasif kira hedefi</h2>
<p>Kuşadası'nda yıllık kira getirisi <strong>%4 - %6</strong> bandında. Yaz aylarında 1 haftalık kiralama, bir aylık geleneksel kiranın 2 - 3 katını verebilir; yaz dönemi optimizasyonu pasif gelirde fark yaratıyor.</p>

<p>Sonuç olarak: Kuşadası emlak yatırımı, sabır ve doğru bölge seçimiyle hem getiri hem yaşam kalitesi sunan en istikrarlı senaryolardan biri. Yatırımdan önce mutlaka deneyimli bir danışmanla bölge yürüyüşü yapın.</p>
HTML,
            ],
            [
                'title' => 'Kuşadası Bölgeleri Rehberi: Hangi Mahallede Ev Alınır?',
                'cover' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1600&q=80',
                'days_ago' => 5,
                'featured' => true,
                'keywords' => 'kuşadası mahalleleri, türkmen, hacıfeyzullah, güzelçamlı, davutlar, soğucak, karaova, kadınlar denizi',
                'excerpt' => 'Türkmen, Hacıfeyzullah, Kadınlar Denizi, Güzelçamlı, Davutlar, Soğucak ve Karaova — Kuşadası\'nın en çok tercih edilen bölgelerinin yaşam ve yatırım açısından dürüst karşılaştırması.',
                'body' => <<<'HTML'
<p>Kuşadası tek bir noktadan ibaret değil: sahilden dağa, marinadan bağ-bahçe köylerine kadar uzanan geniş bir coğrafya. Yanlış bölgede alım yaptığınızda kışın "kimse yok" sorununa, doğru bölgede alım yaptığınızda uzun vadeli değer artışına ulaşırsınız. Aşağıda Kuşadası'nın en çok tercih edilen 7 bölgesini karakter, fiyat, hedef kitle üzerinden özetliyoruz.</p>

<h2>Türkmen Mahallesi — Şehir merkezi + deniz manzarası</h2>
<p>Kuşadası'nın en aktif mahallesi. Atatürk Bulvarı ekseninde, Ladies Beach (Kadınlar Denizi) girişinden başlayarak marina yönüne uzanır. 3+1 modern site projeleri baskın, dairelerin büyük çoğunluğu deniz manzaralı. Kışın da sakinliği kaybetmez; sürekli yaşam için ideal. Fiyatlar 5 - 15 milyon TL bandında.</p>

<h2>Kadınlar Denizi (Ladies Beach) mevkii — Turistik yoğunluk + yaz kirası</h2>
<p>Türkmen Mahallesi'nin sahil şeridi, "Kadınlar Denizi" olarak da bilinen ünlü koydur. Restoran, bar, otel yoğunluğu maksimum; yaz aylarında 1 haftalık kiralama 15 - 40 bin TL bandında getiri sağlar. Kışın turistik hizmetler kısıtlanır, sadece sabit market/eczane açık. Yatırım için mükemmel, sürekli yaşam için gürültülü olabilir.</p>

<h2>Hacıfeyzullah — Marina ve şehir kalbi</h2>
<p>Kuşadası Marina, Güvercinada ve liman bölgesi. "Kalbinde yaşamak" hissi veren mahalle. Yaz kira getirisi çok yüksek, kış nüfusu dengeli. Restoran ve gece hayatı yoğun; sakinlik arayanlar için Türkmen'in iç sokakları veya Soğucak yönü daha uygun. 1+1 ve 2+1 daireler öne çıkar.</p>

<h2>Güzelçamlı — Doğa içinde sahil beldesi</h2>
<p>Dilek Yarımadası Milli Parkı'na 1-2 km mesafede, doğa ile iç içe bir belde. Bahçeli müstakil evler ve butik villalar burada. Yaz nüfusu kışın 3-4 katına çıkar. Sezonluk yatırım için ideal, sürekli yaşam için sosyal altyapı sınırlıdır (market, sağlık ocağı var; büyük AVM yok). Milli Park girişine yakınlık, mülklere ciddi bir prim ekliyor.</p>

<h2>Davutlar — Genişleyen yeni yapı hattı</h2>
<p>Kuşadası merkezine 12 km, kendi beldesi olan Davutlar sahil hattı boyunca yeni yapı stoğuyla en hızlı büyüyen bölge. Krediye uygun 2+1 daireler bol; ilk konut alacak aileler ve emeklilikte deniz kenarına yerleşmek isteyenler için tipik giriş noktası. Ulaşım için araç şart. Fiyatlar Kuşadası merkezine göre %25-40 daha uygun.</p>

<h2>Soğucak — Yamaç köyü, doğa evi arayanlara</h2>
<p>Kuşadası'nın kuzey-doğusunda, çamlıklar arasında tepelik bir bölge. Merkeze 12 km, Davutlar sahiline 9 km. Bağ-bahçe içinde tek katlı taş evler ve villalar tipiktir. Yaz sıcağında bile 3-5 derece daha serindir. Kalıcı yaşam ve butik pansiyon işletmek isteyenlerin gözdesi. Doğalgaz + üç fazlı elektrik gibi altyapı detayları alım öncesi doğrulanmalı.</p>

<h2>Karaova — Tarım + yatırım arsası</h2>
<p>Soğucak ile Söke yolu arasındaki geniş tarım havzası. Zeytin bahçesi, üzüm bağı ve seracılık için ideal büyük parseller. Konut yerine <strong>arsa yatırımı</strong> için tercih edilir; imar durumu her parselde değişir, mutlaka Belediye İmar Müdürlüğü teyidi ile alım yapılmalıdır. 5-10 yıllık uzun vadeli bir yatırım hattı.</p>

<h2>Sonuç: Kısa karar rehberi</h2>
<ul>
    <li><strong>Sürekli yaşam, aile, çocuk okulu:</strong> Türkmen (iç sokaklar) veya Davutlar</li>
    <li><strong>Yaz kira getirisi maksimum:</strong> Kadınlar Denizi mevkii, Hacıfeyzullah</li>
    <li><strong>Doğa + huzurlu yaşam:</strong> Soğucak, Güzelçamlı</li>
    <li><strong>Uzun vadeli arsa yatırımı:</strong> Karaova, Soğucak sınırı</li>
    <li><strong>İlk konut, düşük bütçe:</strong> Davutlar yeni yapı hattı</li>
</ul>

<p>Karar vermeden önce bölgeyi <strong>iki farklı zamanda</strong> ziyaret etmenizi öneririz: yaz öğleden sonra (turist ve trafik yoğunluğu) ve kış sabahı (sakin nüfus, açık işletme sayısı). İki farklı Kuşadası fotoğrafı göreceksiniz. Real Estate CMS Demo olarak her bölgeye ait tur programımızı ücretsiz sunuyoruz — <strong>demo@example.test</strong> üzerinden randevu alabilirsiniz.</p>
HTML,
            ],
            [
                'title' => 'Yabancı Uyruklu Alıcılar İçin Kuşadası\'nda Mülk Edinme Süreci',
                // Notary / paperwork desk — better matches the "official process" topic.
                'cover' => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?auto=format&fit=crop&w=1600&q=80',
                'days_ago' => 12,
                'keywords' => 'yabancıya satış, askeri izin, tapu, kuşadası yabancı',
                'excerpt' => 'Kuşadası\'nda yabancı uyruklu alıcının baştan sona deneyimleyeceği 7 adımlı süreç ve dikkat edilmesi gereken hukuki ayrıntılar.',
                'body' => <<<'HTML'
<p>Kuşadası, yabancı yatırımcı ilgisi en yüksek 5 Türk ilçesinden biri. Almanya, İngiltere, Rusya, İran ve Suudi Arabistan kökenli alıcılar her yıl bölgede yüzlerce mülk ediniyor. Süreç doğru yönetildiğinde 30 - 45 günde tapu devri tamamlanır.</p>

<h2>1. Mülk seçimi ve rezervasyon</h2>
<p>Alıcı, danışmanla yapılan ön ziyaretler sonrası anlaşmaya vardığında <strong>rezervasyon protokolü</strong> imzalanır. Tipik rezervasyon bedeli mülk fiyatının %3 - %5'i, iade edilebilirlik sözleşmede belirtilir.</p>

<h2>2. Vergi kimlik numarası</h2>
<p>Yabancı alıcı, tapu işleminden önce Türkiye'de vergi kimlik numarası almalıdır. İşlem ücretsiz, vergi dairesinde aynı gün tamamlanır.</p>

<h2>3. Banka hesabı ve transfer</h2>
<p>Türk bankasında hesap açılır; ödemeler TL veya yabancı para olarak transfer edilebilir. <strong>Döviz alış belgesi</strong> önemli — Türkiye'den çıkış yaparken bu belge sermayenin geri çıkarılmasını kolaylaştırır.</p>

<h2>4. Askeri uygunluk yazısı</h2>
<p>Kuşadası ve çevresinde mülk satış işlemi öncesi askeri uygunluk yazısı zorunlu. Başvurudan 2 - 3 hafta sonra sonuçlanır; süreçte tapu işlemi beklemededir.</p>

<h2>5. Değer tespit raporu</h2>
<p>Yabancıya satışlarda SPK lisanslı eksper tarafından <strong>değer tespit raporu</strong> hazırlanmalı. Rapor tapu harcı hesabında esas alınır.</p>

<h2>6. Tapu devri</h2>
<p>Yetkili tapu müdürlüğünde alıcı, satıcı veya vekilleri huzurunda tapu devri yapılır. İmzalar atılır ve aynı gün tapu yabancı uyruklu sahibinin adına çıkar.</p>

<h2>7. Oturma izni başvurusu</h2>
<p>Mülk sahipliği oturma izni başvurusunu kolaylaştırır. Tapu çıktığı gün online başvuru sistemi açılır. Bu sürecin tamamı ortalama 30 - 45 gün sürer.</p>

<p>Yabancı uyruklu müşterilerimize Real Estate CMS Demo olarak Türkçe-İngilizce-Rusça tercüme dahil tek noktadan hizmet veriyoruz. Süreci hızlandıran tek şey doğru hazırlık.</p>
HTML,
            ],
            [
                'title' => 'Sezonluk Kiralama: Kuşadası\'nda Yaz Gelirini İkiye Katlamak',
                'cover' => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?auto=format&fit=crop&w=1600&q=80',
                'days_ago' => 18,
                'keywords' => 'kuşadası sezonluk kiralama, yazlık kira, airbnb, yatırım getirisi',
                'excerpt' => 'Geleneksel kira sözleşmesi mi, haftalık tatil kiralaması mı? Kuşadası\'nda mülkten en yüksek geliri çıkarmanın somut yolları.',
                'body' => <<<'HTML'
<p>Kuşadası'nda <strong>1 m²'lik yatak odası</strong> bile yaz aylarında ciddi gelir potansiyeli taşır. Doğru segmentasyon ve doluluk yönetimiyle yıllık gelir, geleneksel kiraya göre <strong>%80 - %150</strong> artırılabilir. Ancak iş yönetimi de farklı disiplin gerektirir.</p>

<h2>1. Yıl boyu doluluk planı</h2>
<p>Yıllık plan yapın: Haziran-Eylül haftalık turistik, Ekim-Mayıs aylık veya 3-6 aylık öğrenci/emekli/serbest çalışan kiralarına yönelin. <strong>Karma plan</strong> getiriyi en üst seviyeye çıkarır.</p>

<h2>2. Mülkün hazırlığı</h2>
<p>Tatil kiralaması müşterisi otelden gelir; otel standardı bekler. Yatak takımı (200×200), mutfak donanımı, klima, akıllı TV, kombi sıcak kahve seti — temel paket. Bunlar olmadan online platformlarda 3 yıldız üstüne çıkmazsınız.</p>

<h2>3. Profesyonel fotoğraf</h2>
<p>Cep telefonu fotoğrafı <strong>tıklanma oranını %35 düşürür</strong>. Geniş açı objektifi + doğal ışık + düzgün mobilya yerleşimi. Drone çekimi yaz villalarına için adeta zorunlu.</p>

<h2>4. Fiyat dinamikleri</h2>
<p>Yaz haftalık fiyat: 25.000 - 80.000 TL. Şehir dışına çıkan İstanbul ve Ankaralı müşteriler ön sıralarda. Sezon dışı 18.000 - 35.000 TL aylık geleneksel kira ile dengelenir.</p>

<h2>5. Yönetim ekibi</h2>
<p>Anahtar teslim, temizlik, çamaşır, küçük tamirat — bunları kendiniz yönetmek istemiyorsanız mülk yönetimi şirketi devreye alın. Komisyon %15 - %20 — getiriden alır ama hayatınızı geri verir.</p>

<p>Doğru mülk + profesyonel sunum + esnek fiyat stratejisi = Kuşadası'nda gerçek pasif gelir.</p>
HTML,
            ],
            [
                'title' => 'İlk Ev Alacaklara Kuşadası Tek Sayfada Kontrol Listesi',
                'cover' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1600&q=80',
                'days_ago' => 25,
                'keywords' => 'ilk ev rehberi, kuşadası konut kredisi, tapu kontrol',
                'excerpt' => 'Banka onayından tapu devrine kadar Kuşadası\'nda ilk ev alımında atlanmaması gereken 14 kritik adım.',
                'body' => <<<'HTML'
<p>İlk ev, çoğu insan için hayatın en büyük finansal kararı. Kuşadası gibi yoğun talep gören bir bölgede heyecan kadar dikkat de gerekiyor. Bu listeyi yanınızda taşıyın.</p>

<h2>Finansman</h2>
<ol>
    <li><strong>Net peşinat:</strong> Konut fiyatının %20'sinden az olmamalı.</li>
    <li><strong>Kredi ön onayı:</strong> 3 farklı bankadan en az 1 ay önceden alın.</li>
    <li><strong>Aylık taksit:</strong> Net gelirinizin %35'ini geçmesin.</li>
    <li><strong>Yan giderler:</strong> %6 - %8 ek bütçe (tapu harcı, DASK, kredi tahsis).</li>
</ol>

<h2>Lokasyon</h2>
<ol start="5">
    <li>Tatil mi, sürekli yaşam mı? — sezon dışı bölgenin ne kadar canlı olduğunu mutlaka inceleyin.</li>
    <li>Plaja veya merkeze yürüme mesafesi <strong>10 dakikanın</strong> içinde mi?</li>
    <li>Önümüzdeki 5 yıl içinde altyapı projesi (yeni yol, marina, AVM) var mı?</li>
</ol>

<h2>Yapı</h2>
<ol start="8">
    <li>Bina yaşı 2018 sonrası (yeni deprem yönetmeliği) tercih edin.</li>
    <li>Otopark hakkı var mı?</li>
    <li>Aidat yıllık 18.000 TL'yi aşıyorsa nedenini sorgulayın.</li>
</ol>

<h2>Tapu &amp; Hukuki</h2>
<ol start="11">
    <li><strong>Tapu sicili:</strong> İpotek, haciz veya şerh kaydı var mı?</li>
    <li><strong>İskan / yapı kullanma izni:</strong> Olmadan satın almayın.</li>
    <li><strong>DASK ve abonelikler:</strong> Devir öncesi sonuçlandırın.</li>
    <li><strong>Değer tespit raporu:</strong> Yatırım amaçlı alımlarda mutlaka talep edin.</li>
</ol>

<p>Bu 14 madde, evraktan emin eve geçişin minimum garantisidir. Real Estate CMS Demo danışmanlığında her birinin yanında ✅ koymadan satışa girmiyoruz.</p>
HTML,
            ],
            [
                'title' => 'Modern Daire Dekorasyonu: Az ile Çok',
                'cover' => 'https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=1600&q=80',
                'days_ago' => 30,
                'keywords' => 'dekorasyon, daire iç tasarım, sahil evi',
                'excerpt' => 'Küçük bütçelerle dairenizin değerini ve günlük konforunu artıracak 7 dekorasyon prensibi — sahil evine uyarlı.',
                'body' => <<<'HTML'
<p>İç dekorasyon büyük bütçeler istemez. Özellikle sahil dairelerinde "havasını" değiştiren küçük müdahaleler hem satış-kiralama hızını hem de günlük yaşam kalitesini yükseltir.</p>
<ol>
    <li><strong>Doğal renk paleti.</strong> Duvarları kırık beyaz, açık bej ya da çok soft gri tonunda sabit tutun. Renk vurgusu mobilya ve tekstilden gelsin.</li>
    <li><strong>Doğal ışık önceliği.</strong> Sahil dairelerinde ışık güçlüdür — perdeleri tüllü ve aydınlatma renkli tutun.</li>
    <li><strong>Yumuşak aydınlatma katmanları.</strong> Ana tavan + 2 lokal ışık (lambader + masa lambası) mekânı bambaşka gösterir.</li>
    <li><strong>Düşey çizgi.</strong> Dar mekânlarda tavandan tabana inen kitaplık ya da boy aynası.</li>
    <li><strong>Akıllı depolama.</strong> Yatak altı, banyo arkası, koridor üstü — kullanılmamış her dik yüzey bir depolama fırsatı.</li>
    <li><strong>Tek karakter parçası.</strong> Vintage bir koltuk, El Sanatları'ndan bir halı — mekâna kişilik veren tek vurgu, on adetten daha güçlü.</li>
    <li><strong>Bitkilerle hayat.</strong> 3 farklı boyutta canlı bitki, oda atmosferini anında ısıtır. Sahil evinde palmiye ve kaktüs ideal.</li>
</ol>
<p>Dekorasyon, evi sevdirmenin sessiz dili. Doğru uygulanan minimal müdahaleler, hem kiracıya hem alıcıya "burada yaşarım" hissi verir.</p>
HTML,
            ],
        ];
    }
}
