# Emlak Portalları XML Feed Entegrasyonu

Bu doküman **Sahibinden**, **Emlakjet**, **Hurriyet Emlak**, **Zingat** ve **Hepsihome** gibi Türk emlak portallarına ilanları otomatik dağıtma yöntemini açıklar.

---

## 🎯 Nasıl Çalışır?

Emlak portalları kurumsal müşterilerine iki entegrasyon modeli sunar:

| Model | Nasıl İşler | Bu Sitede Kullanım |
|---|---|---|
| **XML Feed (pull)** | Portal, sizin verdiğiniz URL'i periyodik olarak (genelde 1-4 saatte bir) tarar ve değişen ilanları kendi tarafına çeker | ✅ **Bu proje bunu sağlıyor** — hazır 4 endpoint var |
| **REST API (push)** | Siz her ilan değişiminde portal'ın API'sine POST atarsınız | ❌ Portal-özel, sözleşme sonrası ayrı bir modül yazılır |

**Feed modeli** hem sözleşme hem geliştirme açısından daha kolay: portal panelinden URL'i kaydeder, geri kalan iş biter.

---

## 🔗 Hazır Feed URL'leri

Site canlıya çıktığında her portal için ayrı bir URL vardır:

| Portal | Feed URL | Şema |
|---|---|---|
| **Emlakjet** | `https://example.test/feed/emlakjet.xml` | Emlakjet flavour |
| **Sahibinden** | `https://example.test/feed/sahibinden.xml` | Sahibinden `<Ads>` şeması |
| **Hurriyet Emlak** | `https://example.test/feed/hurriyet.xml` | `<realEstateExport>` |
| **Zingat / Hepsihome / diğer** | `https://example.test/feed/generic.xml` | RETS-benzeri fallback |

Feed'de sadece **aktif + onaylı + süresi geçmemiş** ilanlar yer alır. Kişisel bilgi veya iç not paylaşılmaz.

---

## 📋 Portal-Bazlı Kurulum Adımları

### 1. Sahibinden.com Kurumsal Üyelik

1. https://kurumsal.sahibinden.com adresinden başvuru yapın (aylık paket, min. 3 ay taahhüt)
2. Sözleşme sonrası hesap yöneticiniz size bir "Referans XML" gönderir
3. Bu belgeye göre `resources/views/feeds/sahibinden.blade.php` içindeki alan adlarını doğrulayın (çoğu %90 uyumlu — kalan %10 portal-özeldir)
4. Kurumsal panelde **"XML Kaynağı Ekle"** sekmesinde `https://example.test/feed/sahibinden.xml` URL'ini kaydedin
5. Test taramasını tetikleyin; hata varsa platformun kurumsal destek kanalıyla iletişime geçin

### 2. Emlakjet

1. https://kurumsal.emlakjet.com üzerinden başvuru — Emlakjet paketleri Sahibinden'e göre daha esnek (single-office ofisler için uygun paketleri var)
2. Onay sonrası panelinizden **"XML Feed"** seçeneği aktifleşir
3. `https://example.test/feed/emlakjet.xml` URL'ini yapıştırın, kaydet
4. İlk import 15-60 dk içinde tamamlanır; portal panelinden hata log'unu takip edin
5. Görsel sıra ve boyut kontrolü Emlakjet tarafında ortalama 1 saat

### 3. Hurriyet Emlak

1. https://kurumsal.hurriyet.com.tr → Emlak kurumsal başvuru
2. Sözleşme sonrası **ExportInterface** paneli açılır
3. `https://example.test/feed/hurriyet.xml` URL'ini "Kaynak URL" alanına girin
4. `coordinates` bloğu opsiyonel — ancak Hurriyet Emlak'ta harita gösterimi için önerilir (bizim feed'de zaten var)

### 4. Zingat, Hepsihome, Endeksa (Generic)

Bu üç portal RETS/1.7.2 benzeri standart şema kabul eder. `https://example.test/feed/generic.xml` her üçüne de gönderilebilir. Portal-özel bir farklılık gelirse `resources/views/feeds/` altına yeni bir `.blade.php` template ve `PortalFeedController`'a yeni bir method eklemek yeterli.

---

## 🛠 Kod İnceleme / Özelleştirme

**Ana dosyalar:**

```
app/Http/Controllers/PortalFeedController.php   # 4 method: emlakjet, sahibinden, hurriyet, generic
resources/views/feeds/emlakjet.blade.php        # Emlakjet XML şablonu
resources/views/feeds/sahibinden.blade.php      # Sahibinden şablonu
resources/views/feeds/hurriyet.blade.php        # Hurriyet Emlak şablonu
resources/views/feeds/generic.blade.php         # RETS-benzeri fallback
routes/web.php (satır ~52)                      # 4 route kaydı
```

**Yeni portal eklemek için:**

1. `resources/views/feeds/yeni_portal.blade.php` — portal'ın istediği XML şemasını yaz
2. `PortalFeedController` içine `public function yeni_portal(): Response` metodu ekle
3. `routes/web.php` içine `Route::get('/feed/yeni_portal.xml', ...)` ekle

---

## ⚙️ Hangi İlanlar Feed'e Girer?

`publishableListings()` filtresi:

- `status = 'active'`
- `is_approved = true`
- `is_active = true`
- `expires_at IS NULL OR expires_at > now()`
- Son 50.000 ilan (portal timeout'una karşı sınır)

Portal tarama sırasında ilan pasifleştirilir/silinirse **bir sonraki taramada** portal tarafında da düşürülür — genelde 1-4 saatlik gecikme normaldir.

---

## 🔒 Güvenlik

- Feed'ler **anonim**: portal indexer'ları kimlik doğrulaması olmadan crawler olarak çeker (standart yaklaşım)
- Personel notu, iç yorum, alıcı iletişimi feed'de **yer almaz**
- Rate-limit yok — portal'lar günde ~24 kez tarayacaktır (Nginx / Cloudflare üzerinden ekstra sınırlama koyabilirsiniz)

---

## 💰 Portal Maliyetleri (2026 Q3 Referans)

Bu ücretler ortalamadır, sözleşmede güncel değer geçerlidir:

| Portal | Kurumsal Aylık | Ekstra İlan | Alt Sözleşme |
|---|---|---|---|
| Sahibinden | ₺15.000+ | Pakete göre | Aylık, min 3 ay |
| Emlakjet | ₺3.500+ | Kredi bazlı | Aylık, esnek |
| Hurriyet Emlak | ₺5.000+ | 500 ilan üstü ek | 6 ay taahhüt |
| Zingat | ₺2.500+ | Genişleyebilir | Aylık |
| Hepsihome | ₺2.000+ | Ekstra: 25 ₺/ilan | Aylık |

Tek-ofis stratejisinde önerim: **Sahibinden + Emlakjet** ikilisiyle başlayın. Bu ikisi Kuşadası pazarında talebin %75'ini karşılar.

---

## ❓ SSS

**S: Portal API'si direkt POST etmek daha hızlı olmaz mı?**
C: Teknik olarak evet ama her portal farklı API + auth + rate-limit gerektirir. XML feed 1 kez yaz, N portal'a ver — bu proje için doğru trade-off.

**S: İlan sayım 100'ü aştığında feed yavaşlar mı?**
C: 5.000 ilana kadar sorunsuz; 5-50.000 arasında `queue` üzerinden cached feed generation önerilir (`php artisan schedule` içine bir job ekleyin).

**S: Feed'de sadece belirli kategorileri paylaşmak istersem?**
C: `PortalFeedController::publishableListings()` içindeki query'ye `->whereHas('category', ...)` filtresi ekleyin.

**S: Facebook / Instagram otomatik paylaşımı gibi bir şey de yapabilir mi?**
C: Evet — Meta Graph API tarafı `.env`'de `META_PAGE_ACCESS_TOKEN` yapılandırıldığında yeni ilan yayınlanınca otomatik post atacak modül `Yol Haritası`'nda planlı.

---

**Sorular için:** GitHub üzerinde bir issue açın.
