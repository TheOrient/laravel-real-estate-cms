# Real Estate Corporate Website & CMS Listing System

Laravel 12 ile geliştirilmiş, modern, çok dilli (TR/EN) ve tek-ofis odaklı açık kaynak kurumsal gayrimenkul sitesi ve ilan yönetim sistemi.

[![Application checks](https://github.com/TheOrient/laravel-real-estate-cms/actions/workflows/checks.yml/badge.svg)](https://github.com/TheOrient/laravel-real-estate-cms/actions/workflows/checks.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Laravel 12](https://img.shields.io/badge/Laravel-12-ff2d20.svg)](https://laravel.com)

> Depodaki ilanlar, fiyatlar, kişiler, kurum bilgileri ve iletişim verileri yalnızca demo amaçlı, kurgusal örneklerdir. Gerçek müşteri veya portföy verisi içermez.

---

## ✨ Öne Çıkan Özellikler

| Kategori | Özellik |
|---|---|
| **Listings** | Çoklu görsel galerisi (5–7 fotoğraf/ilan), kategori + alt-kategori taksonomisi, fiyat/lokasyon/oda filtreleri, haritada konum |
| **Multilingual (TR/EN)** | Header'da TR/EN switcher, cookie-bazlı locale, çeviri tabloları, slug kaynak dilinde sabit |
| **AI Çeviri** | Yapılandırılabilir Groq uyumlu model ile cache'li çeviri — başlık, açıklama ve blog içeriği otomatik EN'e dönüşür |
| **Chatbot** | Site ziyaretçisi asistanı — Groq destekli, kart tabanlı widget |
| **Maps** | Leaflet + OpenStreetMap/Carto varsayılanı ve isteğe bağlı Google Maps desteği |
| **Blog** | Kapaklı blog index, featured post, slug routing, AI ile çevrilen başlık/içerik |
| **Modern UI** | Oval pill search bar, gradient hero, section eyebrows, glassmorphic price tags, smooth hover animations |
| **SEO** | Dinamik meta tags, OG/Twitter card, sitemap.xml, robots.txt, JSON-LD BlogPosting |
| **Admin Panel** | AdminLTE 3, brand-aware logo + tab title, dashboard widgets, listing & blog yönetimi |
| **Settings** | DB-backed dinamik ayarlar — site title, contact, social, footer copyright |
| **Single-Agency Mode** | Marketplace yerine tek-ofis odaklı; halka açık "ilan ver" akışı yok, sadece admin ilan ekler |

---

## 🏗 Teknoloji Stack

- **Backend:** PHP 8.2+ · Laravel 12 (yeni `bootstrap/app.php` middleware yapısı)
- **Frontend:** Blade templates · Tailwind CSS (compiled) · vanilla JS
- **Database:** MySQL 8 / MariaDB 10.6+ / PostgreSQL 15+
- **Maps:** Leaflet 1.9 + Carto Voyager tiles
- **AI:** Groq uyumlu, yapılandırılabilir model
- **Image processing:** Glide
- **PDF:** barryvdh/laravel-dompdf ile ilan broşürü

---

## 📦 Hızlı Kurulum

Detaylı adım adım kurulum için **[INSTALL.md](INSTALL.md)** dosyasına bakın. Özet:

```bash
# 1. Repo'yu klonla
git clone https://github.com/TheOrient/laravel-real-estate-cms.git
cd laravel-real-estate-cms

# 2. Bağımlılıkları yükle
composer install
npm install && npm run build

# 3. .env ayarla
cp .env.example .env
php artisan key:generate
# .env içine DB bilgileri ile benzersiz ADMIN_EMAIL / ADMIN_PASSWORD yaz

# 4. Database
# Yalnızca boş veritabanına ilk kurulum; mevcut sitede db:seed çalıştırmayın.
php artisan migrate
php artisan db:seed

# 5. Storage link + sun
php artisan storage:link
php artisan serve
```

Tarayıcıdan **http://localhost:8000** açın. Yönetim girişi **/login** adresindedir; bilgiler `.env` içindeki `ADMIN_EMAIL` ve `ADMIN_PASSWORD` değerlerinden ilk kurulumda oluşturulur.

---

## 🌐 i18n Notları

- Varsayılan dil: **Turkish (TR)**. Aktif desteklenen: **English (EN)**.
- Statik UI metinleri `lang/tr/` ve `lang/en/` klasörlerinde.
- Dinamik içerik (ilan başlık/açıklama, blog body, sayfa içeriği) **kaynak dilinde girilir**, hedef dil ziyaretçi için `AutoTranslator` cache'li çeviri sağlar.
- Yeni dil eklemek için: `lang/<code>/` klasörü oluşturun, `LanguageSeeder`'a satır ekleyin, `php artisan db:seed --class=LanguageSeeder` çalıştırın.

---

## İlan ve fotoğraf içe aktarma

Yerel JSON + fotoğraf paketinden mevcut veritabanına ilan eklenebilir. Varsayılan kayıt taslaktır; açık yayın talimatı gerekir. Kategori/konum/özellik doğrulaması, sıralı WebP galeri ve mükerrer kayıt koruması içerir. Ek API veya ücretli servis gerektirmez.

Kurulum, güvenlik, örnek paket ve komutlar: **[İlan aktarma rehberi](docs/LISTING_IMPORT.md)**.

---

## 🤖 AI / Chatbot

Groq uyumlu API yapılandırması kullanır:

```env
GROQ_API_KEY=<your-groq-api-key>
GROQ_MODEL=openai/gpt-oss-20b
```

Anahtar olmadan da site çalışır; AI çeviri ve chatbot işlevleri devre dışı kalır, içerik kendi diliyle gösterilir.

---

## 🗺 Free Maps

Varsayılan harita sağlayıcısı: **OpenStreetMap + Carto Voyager**. Google Maps kullanmak için `.env` içine:

```env
MAPS_PROVIDER=google
GOOGLE_MAPS_API_KEY=<your-google-maps-key>
```

---

## 📁 Önemli Dizinler

```
app/
├── Helpers/                 BrandHelper, listing_image_url, get_setting
├── Http/Controllers/        Admin/ + Public controllers
├── Models/                  Category, Listing, Blog, Page (multi-locale + AutoTranslate)
└── Services/
    ├── AIService.php             Groq API client
    └── AutoTranslator.php        Cache'li çeviri + proper-noun guard

resources/views/
├── admin/                   AdminLTE-based admin panel
├── components/              chatbot, map-display, map-picker (Leaflet)
├── home.blade.php           Oval pill search hero
├── blog/                    index + show + partials
└── listings/                index + show + partials (gallery card)

database/seeders/
├── DatabaseSeeder.php       Tüm seed'lerin orkestratörü
└── SampleContentSeeder.php  Örnek ilanlar + blog + brand settings
```

---

## 🎨 Markalama / Whitelabel

Sitedeki tüm brand-facing alanlar **DB ayarları** üzerinden okunur — kod düzenlemesi gerekmez:

| Setting Key | Görünüm |
|---|---|
| `site_title` | Header logo metni, tab title, footer copyright |
| `site_description` | Anasayfa meta description |
| `contact_email`, `contact_phone`, `contact_address` | Footer + İletişim sayfası |
| `social_facebook_url`, `social_instagram_url` | Footer sosyal medya butonları |
| `home_hero_image` | Anasayfa hero arkaplan görseli |

`/admin/settings` arayüzünden veya `Setting::updateOrCreate(...)` ile değiştirin.

---

## 🛡 Lisans

Proje **MIT License** ile yayımlanır. Kullanım koşulları için **[LICENSE](LICENSE)** dosyasına bakın.

---

## 🤝 Katkıda Bulunma

PR'lara açığız. Lütfen önce bir issue açın ve davranış değişikliği yapıyorsanız ekran görüntüsü/test ekleyin. Code style: Laravel Pint (`./vendor/bin/pint`).

---

## 🙏 Teşekkürler

- [Laravel](https://laravel.com) ekibi — temiz framework için
- [Groq](https://groq.com) — AI inference altyapısı için
- [OpenStreetMap](https://www.openstreetmap.org) & [Carto](https://carto.com) — açık harita tile'ları için
- [Unsplash](https://unsplash.com) — sample içerik görselleri için
- [AdminLTE](https://adminlte.io) — admin paneli base'i için
