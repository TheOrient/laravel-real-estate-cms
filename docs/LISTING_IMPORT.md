# İlan ve fotoğraf içe aktarma

Bu özellik mevcut veritabanı, danışman paneli ve ilan galerisiyle çalışır. Yeni bir veritabanı, ücretli API veya internete açık yazma uç noktası gerekmez. Komutlara yalnızca sunucu/proje erişimi olan güvenilir operatörler erişir; `--agent` bir giriş anahtarı değil, ilanın atanacağı danışmandır.

## Sohbetten ilan ekleme

İşletme sahibi bilgileri ve kullanma hakkına sahip olduğu gerçek fotoğrafları gönderir. Operatör/yardımcı aşağıdaki sırayla çalışır:

1. Satılık/kiralık, taşınmaz türü, fiyat, ilçe/mahalle, oda sayısı, net/brüt alan ve verilen diğer özellikleri bir pakete dönüştürür. Bilinmeyen özellikleri, adresi veya koordinatları tahmin etmez.
2. Mevcut kategori, mahalle ve danışman kimliklerini katalogdan seçer. Satılık/kiralık seçimi en alt kategoriyle belirlenir.
3. İlk fotoğrafı kapak, kalanları gönderilen sırada galeri olarak ayarlar.
4. Önce kayıt yapmadan kontrol eder, sonra taslak kaydeder. Yayın için açık talimat varsa `--publish` kullanır.
5. İlan numarası, taslak/yayın durumu ve uygun panel/site bağlantısını bildirir.

Bu bir mesajları arka planda izleyen bot değildir. Yardımcının o oturumda proje/sunucuya ve gönderilen dosyalara erişimi gerekir. Canlı site başka sunucuya taşındığında komut da o sunucuda, onun veritabanı ayarlarıyla çalıştırılır. MySQL'i internete açmayın, root parolasını veya API anahtarlarını GitHub'a koymayın.

## Bir defalık kurulum

Önce veritabanı yedeği alın. Mevcut verileri silmeyen bu migration, mükerrer aktarımı önlemek için iki boş alan ve benzersiz referans indeksi ekler:

```bash
php artisan migrate --path=database/migrations/2026_09_11_120000_add_import_reference_to_listings.php
```

Bu özellik için seed, `migrate:fresh` veya veritabanı sıfırlama **çalıştırılmaz**. GD/WebP desteği ve `public/uploads/listings` için yazma izni gerekir. Projedeki mevcut resim işleme bağımlılıkları kullanılır.

## Katalog ve paket

```bash
php artisan listings:import-catalog
php artisan listings:import-catalog --category=GERCEK_KATEGORI_ID
```

İlk komut aktif danışmanları, alt kategorileri ve konumları gösterir. İkincisi seçilen kategorinin miras alınan özelliklerini, zorunlu alanlarını ve seçenek kimliklerini de verir. Örnek dosyadaki kimlikler **yer tutucudur**; katalogdaki gerçek değerlerle değiştirin.

Paketleri web kökünün dışında, örneğin Git tarafından dışlanan `storage/app/private/listing-imports/portfoy-001/` altında tutun:

```text
portfoy-001/
  listing.json
  salon.jpg
  balkon.jpg
```

Başlangıç şablonu: [listing-import.example.json](examples/listing-import.example.json).

- `reference`: Her ilan için kalıcı, benzersiz küçük harf/rakam/tire/alt çizgi, en çok 80 karakter.
- `title`, `description`: Kaynak dilinde düz metin; HTML kabul edilmez. Başlık en çok 200, açıklama 20–20.000 karakter.
- `price`: Sitenin mevcut para biriminde, binlik ayracı olmadan: `4250000` veya `4250000.00`. Yeni para birimi eklemez.
- `category_id`, `city_id`, `district_id`: Zorunlu; mahalle isteğe bağlıdır. Konum hiyerarşisi ve aktiflik doğrulanır.
- `attributes`: Anahtar özellik kimliğidir. Sayısal/metinsel alanlara değer; seçim alanlarına seçenek kimliği veya seçenek kimlikleri dizisi verilir. Yayında kategorinin zorunlu özellikleri aranır.
- `latitude`, `longitude`: İkisi birlikte verilir veya ikisi de boş bırakılır. Kesin konum bilinmiyorsa uydurmayın.
- `photos`: Paket içindeki göreli dosya yolları. İlk fotoğraf kapaktır. Yayında en az bir fotoğraf gerekir; taslak fotoğrafsız olabilir.
- `video_url`: İsteğe bağlı HTTPS YouTube/Vimeo bağlantısı; bu komut video dosyası yüklemez.
- `allow_whatsapp`: İsteğe bağlı `true`/`false`, varsayılan `false`.

Kaynak dil `config/listing_import.php` içinden belirlenir; varsayılan TR'dir. Türkçe başlık URL'ye dönüştürülür, ilan kimliği eklenir. EN görünümü mevcut çeviri altyapısını kullanır; aktarım herhangi bir AI API çağrısı yapmaz.

## Kontrol, taslak, yayın

Aşağıdaki `DANISMAN_ID` yerine katalogdaki aktif danışman/yönetici kimliğini yazın:

```bash
php artisan listings:import storage/app/private/listing-imports/portfoy-001/listing.json --agent=DANISMAN_ID --dry-run
php artisan listings:import storage/app/private/listing-imports/portfoy-001/listing.json --agent=DANISMAN_ID
```

Yayın onayı verildiğinde aynı paketle:

```bash
php artisan listings:import storage/app/private/listing-imports/portfoy-001/listing.json --agent=DANISMAN_ID --publish --dry-run
php artisan listings:import storage/app/private/listing-imports/portfoy-001/listing.json --agent=DANISMAN_ID --publish
```

`--dry-run` verileri ve fotoğrafların açılabildiğini kontrol eder; veritabanına/dosya galerisine kayıt yapmaz. Diskte yer ve yazma izni gibi gerçek kayıt hataları ayrıca aktarım sırasında ele alınır. Çıktı JSON'dur, başarısız komut sıfırdan farklı çıkış kodu verir.

`--publish` olmadan ilan ziyaretçilere kapalıdır. `admin_url` ilanın filtrelenmiş yönetim listesini, `agent_edit_url` atandığı danışmanın düzenleme ekranını açar. `--publish`, aynı referans ve aynı paket için var olan ilanın güncel içeriklerini değiştirmeden yayına alır; panelde sonradan yapılan düzenlemeler korunur. Yayından kaldırılmış ilan da bu açık talimatla yeniden açılabilir. Sonradan panelde değişiklik yapıldıysa yayın öncesi paneldeki son halini de kontrol edin.

## Güvenlik ve tekrar deneme

- Aynı referans + aynı içerik + aynı danışman + aynı sıradaki fotoğraf baytları tekrar gönderilirse yeni ilan/fotoğraf oluşturulmaz. Taslak komutu yayındaki ilanı kapatmaz.
- Aynı referansa farklı içerik veya silinmiş ilan bağlıysa işlem reddedilir. Güncelleme için paneli kullanın; farklı referans vererek mükerrer ilan yaratmayın.
- Fotoğraflar JPG/PNG/WebP, en çok 20 adet, dosya başına 10 MB ve 24 milyon piksel olabilir. Sınırlar `config/listing_import.php` içindedir.
- Fotoğraflar yönleri düzeltilerek en çok 1920 × 1920 sınırına ölçeklenir ve WebP olarak yeniden kodlanır. Orijinaller değiştirilmez. Uzak URL'ler, klasör dışına taşan yollar/symlinkler ve SVG kabul edilmez.
- Veritabanı işlemi tek transaction içindedir. Yakalanan hata olursa yeni kayıtlar geri alınır ve yalnızca o denemenin oluşturduğu galeri klasörü temizlenir. İşlemin zorla öldürülmesi/güç kesilmesi dosya artıklarını bırakabilir; bu durumda yeniden denemeden önce günlük ve ilgili aktarım klasörü kontrol edilir.
- Mevcut ilanlar ve diğer galeriler silinmez. Sosyal medyada otomatik paylaşım tetiklenmez; yayın yalnızca bu sitede yapılır.
- İlan JSON'larını, fotoğrafları, kişisel bilgileri ve veritabanı yedeklerini GitHub'a eklemeyin.

## Test

```bash
php artisan test --filter=ListingImportTest
```

Testler gerçek MySQL'e erişmez; bellek içi SQLite ve sahte fotoğraf diski kullanır. Taslak/yayın, tekrar deneme, hatalı konum/özellik/dosya, hata anında geri alma ve katalog çıktısı kontrol edilir.
