<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Throwable;

/**
 * Sıfır kurulumdan sonra içerik migration'larını yeniden uygular.
 *
 * NEDEN GEREKLİ
 * -------------
 * Laravel boş bir veritabanında önce TÜM migration'ları, sonra seeder'ları
 * çalıştırır. Bizim içerik migration'larımız (demo ilanları silme, kategori
 * sadeleştirme, marka güncelleme, yasal sayfa, İzmir konumları…) o sırada
 * boş bir veritabanı görür ve yapacak iş bulamaz. Ardından seeder'lar
 * kurulum paketiyle gelen demo ilanları ve 60 kalemlik kategori ağacını
 * yeniden oluşturur.
 *
 * Bu komut, seeder'lardan SONRA içerik migration'larını tarih sırasıyla bir
 * kez daha çalıştırarak siteyi olması gereken hâle getirir. Şema (tablo
 * oluşturan/değiştiren) migration'larına dokunmaz.
 *
 * Migration'ların hepsi idempotent yazıldığı için komut tekrar
 * çalıştırılabilir; ancak panelden elle yapılmış bazı ayar değişikliklerini
 * (ör. iletişim e-postası) varsayılana döndürebilir. Bu yüzden kurulum
 * betiği bunu yalnızca ilk kurulumda otomatik çağırır.
 */
class PostInstallContent extends Command
{
    protected $signature = 'site:kurulum {--dry-run : Sadece hangi migration\'ların çalışacağını listele}';

    protected $description = 'Sıfır kurulumdan sonra içerik migration\'larını yeniden uygular (demo temizliği, kategoriler, marka, yasal sayfa).';

    public function handle(): int
    {
        $files = $this->contentMigrations();

        if ($files === []) {
            $this->warn('İçerik migration\'ı bulunamadı.');

            return self::SUCCESS;
        }

        $this->line('Uygulanacak içerik migration sayısı: '.count($files));

        if ($this->option('dry-run')) {
            foreach ($files as $file) {
                $this->line('  · '.basename($file));
            }

            return self::SUCCESS;
        }

        $ok = 0;
        $failed = [];

        foreach ($files as $file) {
            $name = basename($file, '.php');

            try {
                $migration = require $file;

                if (! is_object($migration) || ! method_exists($migration, 'up')) {
                    continue;
                }

                $migration->up();
                $ok++;
                $this->line('  <fg=green>✔</> '.$name);
            } catch (Throwable $exception) {
                $failed[$name] = $exception->getMessage();
                $this->line('  <fg=red>✖</> '.$name.' — '.$exception->getMessage());
            }
        }

        $this->newLine();
        $this->info("Tamamlandı — başarılı: {$ok}, hatalı: ".count($failed));

        if ($failed !== []) {
            $this->warn('Hatalı olanlar site çalışmasını engellemez ama içerik eksik kalmış olabilir.');
        }

        return self::SUCCESS;
    }

    /**
     * Şema değiştirmeyen (yalnız veri yazan) migration dosyaları, tarih sırasıyla.
     *
     * @return array<int, string>
     */
    private function contentMigrations(): array
    {
        $files = [];

        foreach (File::files(database_path('migrations')) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $body = (string) File::get($file->getPathname());

            // Tablo oluşturan/değiştiren migration'lar zaten uygulandı; atla.
            if (str_contains($body, 'Schema::create(') || str_contains($body, 'Schema::table(')) {
                continue;
            }

            $files[] = $file->getPathname();
        }

        sort($files);

        return $files;
    }
}
