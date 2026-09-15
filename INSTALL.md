# Kurulum Rehberi — Real Estate Corporate Website & CMS

Açık kaynak, çok dilli (TR/EN), tek-ofis odaklı gayrimenkul CMS'inin sıfırdan kurulum kılavuzu. Tahmini süre: **15–20 dakika** (local), **45 dakika** (production).

---

## 📋 Sistem Gereksinimleri

| Bileşen | Minimum | Önerilen |
|---|---|---|
| PHP | 8.2 | 8.3 |
| Composer | 2.5+ | latest |
| Node.js | 20.19 veya 22.12+ | 24 |
| npm | 9 | latest |
| MySQL | 8.0 | 8.0+ |
| MariaDB | 10.6 | 10.11+ |
| PostgreSQL | 15 | 16+ |
| Web Server | nginx 1.20+ / Apache 2.4+ | nginx |
| Disk | 500 MB | 2 GB+ |
| RAM | 1 GB | 2 GB+ |

### PHP Extensions

`php -m` çıktısında şu extension'lar **mutlaka** olmalı:

```
bcmath  ctype  curl  dom  fileinfo  filter  gd  hash
mbstring  mysqlnd  openssl  pcre  pdo  pdo_mysql
session  tokenizer  xml  zip  intl  exif
```

Eksik olanları yükleyin:

```bash
# Ubuntu / Debian
sudo apt install php8.2-{bcmath,curl,gd,intl,mbstring,mysql,xml,zip,exif}

# macOS (Homebrew)
brew install php@8.2
brew install --cask xampp  # alternatif: tek pakette PHP + MySQL + Apache

# Windows
# XAMPP indirin: https://www.apachefriends.org
```

---

## 🚀 Adım 1 — Kodu İndir

```bash
git clone https://github.com/TheOrient/laravel-real-estate-cms.git
cd laravel-real-estate-cms
```

Veya zip indirip açın:

```bash
unzip laravel-real-estate-cms-main.zip
cd laravel-real-estate-cms-main
```

---

## 📦 Adım 2 — Bağımlılıkları Yükle

### Composer (PHP packages)

```bash
composer install --no-dev --optimize-autoloader  # production
# veya
composer install                                  # development
```

### NPM (frontend asset'leri)

```bash
npm install
npm run build              # production
# veya
npm run dev                # development — auto-rebuild
```

---

## ⚙️ Adım 3 — Environment Ayarları

```bash
cp .env.example .env
php artisan key:generate
```

`.env` dosyasını açın ve **en az** şu alanları doldurun:

```env
# === Temel ===
APP_NAME="Open-Source Real Estate CMS"
APP_ENV=local                          # production'da: production
APP_DEBUG=true                         # production'da: false
APP_URL=http://localhost:8000          # production: https://your-domain.com
SESSION_SECURE_COOKIE=false            # HTTPS production'da: true

# === Database ===
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=real_estate_cms
DB_USERNAME=root
DB_PASSWORD=

# === Locale ===
APP_LOCALE=tr
APP_FALLBACK_LOCALE=tr

# === Cache & Session ===
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database

# === AI / Chatbot (opsiyonel — yoksa AI özellikleri pasif) ===
GROQ_API_KEY=
GROQ_MODEL=openai/gpt-oss-20b

# === Mail (iletişim formu için) ===
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS="no-reply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

> AI özellikleri isteğe bağlıdır. Groq anahtarı eklenmezse uygulamanın temel CMS işlevleri çalışmaya devam eder.

---

## 🗄 Adım 4 — Veritabanı

### Database oluştur

```sql
CREATE DATABASE real_estate_cms
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

Veya MySQL CLI:

```bash
mysql -u root -p -e "CREATE DATABASE real_estate_cms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Migrate + Seed

Bu bölüm **yalnızca boş veritabanına ilk kurulum** içindir. Var olan siteyi taşırken veritabanı yedeğini geri yükleyin ve sadece gerekli migration dosyalarını uygulayın. `db:seed` bir güncelleme komutu değildir; mevcut kullanıcı/içerik varsa güvenlik amacıyla durur. `migrate:fresh` mevcut verileri siler ve canlı sitede kullanılmaz.

```bash
php artisan migrate
php artisan db:seed
```

Seed komutu şunları kurar:

- ✅ `.env` içindeki `ADMIN_EMAIL` / `ADMIN_PASSWORD` ile tek yönetici hesabı
- ✅ TR + EN diller
- ✅ Örnek konum ağacı (şehir → ilçe → mahalle)
- ✅ Emlak kategori ağacı (Satılık/Kiralık Daire, Villa, Müstakil Ev, Arsa, vb.)
- ✅ 5 örnek gayrimenkul ilanı (her birinde 5–7 görsel)
- ✅ 6 örnek blog yazısı (gayrimenkul rehberi içerikleri)
- ✅ CMS sayfaları (Hakkımızda, İletişim, Gizlilik, Kullanım Koşulları, vb.)
- ✅ Varsayılan marka ayarları (`/admin/settings` üzerinden istediğiniz değerle değiştirin)

> ⚠️ **Production'da `php artisan db:seed` çalıştırmadan önce** `.env` içinde en az 12 karakterli, benzersiz `ADMIN_PASSWORD` tanımlayın. Mevcut şifreyi `/panel/profile` üzerinden veya tinker ile değiştirebilirsiniz:
> ```bash
> php artisan tinker
> User::where('user_role', 'admin')->first()->update(['password' => Hash::make('YENİ_GÜÇLÜ_ŞİFRE')]);
> ```

Gerçek ilanların fotoğraflarıyla güvenli girişi için [ilan aktarma rehberini](docs/LISTING_IMPORT.md) kullanın. Örnek içerik yükleyicisini tekrar çalıştırmayın.

---

## 🔗 Adım 5 — Storage Link

Public uploads için symlink oluştur:

```bash
php artisan storage:link
```

Bu, `public/storage` → `storage/app/public` linki kurar.

---

## 🎬 Adım 6 — Çalıştır

### Development

```bash
php artisan serve
```

Tarayıcıdan açın: **http://localhost:8000**

Worker memory için ek bağımsız işlemler isteniyorsa:

```bash
PHP_CLI_SERVER_WORKERS=4 php artisan serve
```

### Production (nginx + PHP-FPM)

Örnek nginx config:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/real-estate-cms/public;

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Dosya izinleri:

```bash
sudo chown -R www-data:www-data /var/www/real-estate-cms
sudo chmod -R 755 /var/www/real-estate-cms
sudo chmod -R 775 storage bootstrap/cache
```

HTTPS için **Let's Encrypt** önerilir:

```bash
sudo certbot --nginx -d your-domain.com
```

---

## 🛠 Adım 7 — Production Optimizasyonu

Yayına almadan önce `.env` içinde `APP_ENV=production`, `APP_DEBUG=false`,
gerçek HTTPS `APP_URL`, `SESSION_SECURE_COOKIE=true` ve çalışan SMTP bilgilerini
kullandığınızdan emin olun. Varsayılan yönetici parolasını mutlaka değiştirin.

Mevcut kurulumun `APP_KEY` değerini taşıma/güncellemede koruyun; yeniden üretmeyin. Hem dosyalardaki `APP_URL` hem yönetim panelindeki `site_url` gerçek HTTPS alan adını göstermelidir. Veritabanını ve `public/uploads` klasörünü birlikte yedekleyin. SQL dökümleri ve `storage/app/private` içeriği web kökünün dışında kalmalıdır.

```bash
# Cache config + routes + views
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Composer autoload optimize
composer install --no-dev --optimize-autoloader

# Frontend asset minification
npm run build
```

Cache temizlemek (deploy sonrası):

```bash
php artisan optimize:clear
```

---

## 🧹 Adım 8 — Cron / Schedule (opsiyonel)

Laravel scheduler için crontab ekleyin:

```bash
crontab -e
```

Şu satırı ekleyin:

```cron
* * * * * cd /var/www/real-estate-cms && php artisan schedule:run >> /dev/null 2>&1
```

---

## ✅ Doğrulama Kontrol Listesi

Kurulumdan sonra şunları test edin:

- [ ] Anasayfa açılıyor: **http://localhost:8000**
- [ ] TR/EN dil geçişi çalışıyor (sağ üst pill)
- [ ] İlanlar görünüyor: **/all**
- [ ] Bir ilan detayı açılıyor, galeri swipe ediliyor
- [ ] Harita render oluyor (Leaflet)
- [ ] Blog index açılıyor: **/blog**
- [ ] Bir blog yazısı detayı açılıyor
- [ ] İletişim formu açılıyor: **/page/iletisim**
- [ ] Admin giriş: **/login** → `.env` içindeki yönetici bilgileri
- [ ] Admin dashboard: **/admin** → 5 ilan + 6 blog görünüyor
- [ ] Sitemap.xml açılıyor: **/sitemap.xml**
- [ ] Robots.txt açılıyor: **/robots.txt**
- [ ] (Groq anahtarı varsa) EN dilinde başlıklar otomatik çevriliyor
- [ ] (Groq anahtarı varsa) Sağ alt chatbot widget cevap veriyor

---

## 🆘 Sorun Giderme

### "Class 'X' not found" hatası

```bash
composer dump-autoload
php artisan optimize:clear
```

### Storage izin hatası

```bash
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache  # production
```

### MySQL "Connection refused"

- MySQL servisinin çalıştığından emin olun: `sudo service mysql status`
- XAMPP kullanıyorsanız Control Panel'den MySQL'i başlatın
- `.env` içindeki `DB_HOST` ve `DB_PORT` değerlerini kontrol edin

### EN sayfası "no content" gösteriyor

- Cache'i temizleyin: `php artisan cache:clear`
- Groq API anahtarınız olduğundan emin olun (`.env` → `GROQ_API_KEY`)
- AutoTranslator cache key versiyonunu görmek için `app/Services/AutoTranslator.php` → `auto_translate.v3.*` satırına bakın

### "419 Page Expired" hatası

Form submit'lerinde CSRF token expire olmuş demektir. Session ayarlarını kontrol edin:

```bash
php artisan cache:clear
php artisan config:cache
```

Tarayıcı cookie'lerini de temizleyip yeniden deneyin.

### Görseller görünmüyor

```bash
php artisan storage:link
```

### Modeli güncelledikten sonra eski içerik geliyor

```bash
php artisan optimize:clear
# Veya direkt:
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

PHP 8.2+ `php artisan serve` workers cache'leyebilir, server'ı restart edin (CTRL+C, sonra tekrar `php artisan serve`).

---

## 🌍 Localization — Yeni Dil Ekleme

EN ve TR dışında yeni dil eklemek için:

1. **Dil dosyaları oluşturun:**
   ```bash
   cp -r lang/en lang/de    # Almanca örneği
   ```

2. **`config/app.php` içine ekleyin:**
   ```php
   'available_locales' => ['tr', 'en', 'de'],
   ```

3. **`database/seeders/LanguageSeeder.php` dosyasına satır ekleyin:**
   ```php
   ['code' => 'de', 'name' => 'Deutsch', 'is_active' => true, 'is_default' => false],
   ```

4. **Seed çalıştırın:**
   ```bash
   php artisan db:seed --class=LanguageSeeder
   ```

5. **AutoTranslator desteği:** `app/Services/AutoTranslator.php` içindeki `in_array($target, ['en'], true)` satırına `'de'` ekleyin.

---

## 🔄 Güncelleme

Repo'dan yeni sürüm almak için:

```bash
git pull
composer install
npm install && npm run build
php artisan migrate
php artisan optimize:clear
```

---

Sorularınız için: **GitHub Issues** üzerinden bizimle iletişime geçin.

Mutlu kullanımlar!
