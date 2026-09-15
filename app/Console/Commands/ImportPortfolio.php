<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\City;
use App\Models\District;
use App\Models\Neighborhood;
use App\Models\User;
use App\Services\ListingImport\ListingImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Klasör klasör portföy aktarımı — kimlik numarası ezberlemeden.
 *
 * Her ilan klasöründe bir "ilan.json" bulunur ve içinde kategori, şehir,
 * ilçe, mahalle gibi alanlar KİMLİK yerine ADLARIYLA yazılır. Bu komut
 * adları veritabanındaki kimliklere çevirir, mevcut ListingImporter'ın
 * beklediği listing.json'u üretir ve aktarımı ona devreder (doğrulama,
 * tek transaction, hata geri alma, mükerrer koruma, fotoğraf işleme
 * hep orada).
 *
 * Kullanım:
 *   php artisan portfoy:katalog                     # geçerli adları listeler
 *   php artisan portfoy:aktar "/yol/klasor" --dry-run
 *   php artisan portfoy:aktar "/yol/klasor"         # taslak olarak ekler
 *   php artisan portfoy:aktar "/yol/klasor" --publish
 */
class ImportPortfolio extends Command
{
    protected $signature = 'portfoy:aktar
        {klasor : İçinde ilan klasörleri (veya tek ilan) bulunan dizin}
        {--publish : Taslak yerine doğrudan yayına al}
        {--dry-run : Hiçbir kayıt yapma, yalnızca doğrula}
        {--katalog : Aktarma; ilan.json\'lardaki kategorilerin özellik adlarını ve seçeneklerini listele}';

    protected $description = 'İlan klasörlerini ilan.json dosyalarına göre siteye aktarır.';

    public function handle(ListingImporter $importer): int
    {
        $root = rtrim($this->argument('klasor'), '/');

        if (! is_dir($root)) {
            $this->error("Klasör bulunamadı: {$root}");

            return self::FAILURE;
        }

        $bundles = is_file($root.'/ilan.json')
            ? [$root]
            : array_values(array_filter(
                array_map(fn ($d) => rtrim($d, '/'), File::directories($root)),
                fn ($d) => is_file($d.'/ilan.json')
            ));

        if ($bundles === []) {
            $this->error('Hiçbir alt klasörde ilan.json bulunamadı.');

            return self::FAILURE;
        }

        if ($this->option('katalog')) {
            foreach ($bundles as $bundle) {
                $this->printCatalog($bundle);
            }

            return self::SUCCESS;
        }

        $ok = 0;
        $failed = 0;

        foreach ($bundles as $bundle) {
            $this->newLine();
            $this->line('── '.basename($bundle));

            try {
                $result = $this->importOne($bundle, $importer);
                $this->info('   ✔ '.$result);
                $ok++;
            } catch (ValidationException $exception) {
                $failed++;
                foreach ($exception->errors() as $field => $messages) {
                    $this->error('   ✖ '.$field.': '.implode(' ', (array) $messages));
                }
            } catch (Throwable $exception) {
                $failed++;
                $this->error('   ✖ '.$exception->getMessage());
            }
        }

        $this->newLine();
        $this->line("Tamamlandı — başarılı: {$ok}, hatalı: {$failed}");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Bir paketin kategorisindeki özellikleri, tam adlarıyla ve seçilebilir
     * değerleriyle yazar — ilan.json'daki "ozellikler" bloğunu buna göre
     * doldurursunuz.
     */
    private function printCatalog(string $bundle): void
    {
        $this->newLine();
        $this->line('── '.basename($bundle));

        try {
            $raw = json_decode((string) File::get($bundle.'/ilan.json'), true, 512, JSON_THROW_ON_ERROR);
            $category = $this->resolveCategory((string) ($raw['kategori'] ?? ''));
        } catch (Throwable $exception) {
            $this->error('   '.$exception->getMessage());

            return;
        }

        $this->line('   kategori: '.$category->name.' (#'.$category->id.')');

        foreach ($category->getAllAttributes(['values']) as $attribute) {
            $flag = $attribute->is_required ? ' [ZORUNLU]' : '';
            $this->line('   • '.$attribute->name.$flag.'  (#'.$attribute->id.', '.$attribute->display_type_user_panel.')');

            $values = $attribute->values->pluck('value')->filter()->values();
            if ($values->isNotEmpty()) {
                $this->line('       seçenekler: '.$values->implode(' | '));
            }
        }
    }

    /* ------------------------------------------------------------------ */

    private function importOne(string $bundle, ListingImporter $importer): string
    {
        $raw = json_decode((string) File::get($bundle.'/ilan.json'), true, 512, JSON_THROW_ON_ERROR);

        $agent = $this->resolveAgent($raw['danisman'] ?? null);
        $category = $this->resolveCategory((string) ($raw['kategori'] ?? ''));
        [$cityId, $districtId, $neighborhoodId] = $this->resolveLocation(
            (string) ($raw['sehir'] ?? ''),
            (string) ($raw['ilce'] ?? ''),
            $raw['mahalle'] ?? null
        );

        $manifest = [
            'reference' => $raw['reference'] ?? null,
            'title' => $raw['baslik'] ?? null,
            'description' => $raw['aciklama'] ?? null,
            'price' => $raw['fiyat'] ?? null,
            'category_id' => $category->id,
            'city_id' => $cityId,
            'district_id' => $districtId,
            'neighborhood_id' => $neighborhoodId,
            'allow_whatsapp' => (bool) ($raw['whatsapp'] ?? true),
            'photos' => array_values($raw['fotograflar'] ?? []),
            'attributes' => $this->resolveAttributes($category, $raw['ozellikler'] ?? []),
        ];

        if (! empty($raw['video_url'])) {
            $manifest['video_url'] = $raw['video_url'];
        }
        if (isset($raw['enlem'], $raw['boylam'])) {
            $manifest['latitude'] = $raw['enlem'];
            $manifest['longitude'] = $raw['boylam'];
        }

        $manifestPath = $bundle.'/listing.json';
        File::put($manifestPath, json_encode(
            $manifest,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
        ));

        $result = $importer->import(
            $manifestPath,
            $agent->id,
            (bool) $this->option('publish'),
            (bool) $this->option('dry-run')
        );

        $listing = $result['listing'];

        return sprintf(
            '%s — %s (%d foto)%s',
            $result['action'],
            $manifest['title'],
            $result['photo_count'],
            $listing ? ' · #'.$listing->id : ''
        );
    }

    /* ------------------------------------------------------------------ */

    private function resolveAgent(?string $name): User
    {
        $query = User::query();

        if ($name) {
            $user = (clone $query)->where('email', $name)->first()
                ?? (clone $query)->where('name', $name)->first();

            if ($user) {
                return $user;
            }

            throw new \RuntimeException("Danışman bulunamadı: {$name}");
        }

        $user = $query->orderBy('id')->first();

        if (! $user) {
            throw new \RuntimeException('Sistemde hiç kullanıcı yok.');
        }

        $this->line('   danışman: '.$user->name.' (#'.$user->id.')');

        return $user;
    }

    private function resolveCategory(string $name): Category
    {
        $needle = $this->fold($name);

        $match = Category::with('descriptions')->where('is_active', true)->get()
            ->first(fn ($c) => $c->descriptions->contains(fn ($d) => $this->fold($d->name) === $needle));

        if (! $match) {
            throw new \RuntimeException("Kategori bulunamadı: {$name} — 'php artisan portfoy:katalog' ile geçerli adlara bakın.");
        }

        return $match;
    }

    private function resolveLocation(string $city, string $district, ?string $neighborhood): array
    {
        $cityModel = City::all()->first(fn ($c) => $this->fold($c->name) === $this->fold($city));
        if (! $cityModel) {
            throw new \RuntimeException("Şehir bulunamadı: {$city}");
        }

        $districtModel = District::where('city_id', $cityModel->id)->get()
            ->first(fn ($d) => $this->fold($d->name) === $this->fold($district));
        if (! $districtModel) {
            throw new \RuntimeException("İlçe bulunamadı: {$district}");
        }

        $neighborhoodId = null;
        if ($neighborhood) {
            $model = Neighborhood::where('district_id', $districtModel->id)->get()
                ->first(fn ($n) => $this->fold($n->name) === $this->fold($neighborhood));

            if (! $model) {
                $this->warn("   ! Mahalle bulunamadı, boş bırakıldı: {$neighborhood}");
            } else {
                $neighborhoodId = $model->id;
            }
        }

        return [$cityModel->id, $districtModel->id, $neighborhoodId];
    }

    /**
     * Özellik adlarını ve seçenek metinlerini kimliklere çevirir.
     * Eşleşmeyen alanlar atlanır ve ekrana yazılır — ilan yine de açılır,
     * eksik alan panelden tamamlanır.
     */
    private function resolveAttributes(Category $category, array $input): array
    {
        if ($input === []) {
            return [];
        }

        $available = $category->getAllAttributes(['values']);
        $out = [];

        foreach ($input as $label => $value) {
            $attribute = $available->first(fn ($a) => $this->fold($a->name) === $this->fold((string) $label));

            if (! $attribute) {
                $this->warn("   ! Özellik bulunamadı, atlandı: {$label}");

                continue;
            }

            $options = $attribute->values;

            if ($options->isEmpty()) {
                $out[$attribute->id] = $value;

                continue;
            }

            $wanted = is_array($value) ? $value : [$value];
            $ids = [];

            foreach ($wanted as $one) {
                $option = $options->first(fn ($o) => $this->fold((string) $o->value) === $this->fold((string) $one));

                if (! $option) {
                    $this->warn("   ! '{$label}' için seçenek bulunamadı, atlandı: {$one}");

                    continue;
                }

                $ids[] = $option->id;
            }

            if ($ids !== []) {
                $out[$attribute->id] = count($ids) === 1 ? $ids[0] : $ids;
            }
        }

        return $out;
    }

    /** Türkçe karakterlere ve büyük/küçük harfe duyarsız karşılaştırma. */
    private function fold(string $value): string
    {
        $map = ['İ' => 'i', 'I' => 'i', 'ı' => 'i', 'Ş' => 's', 'ş' => 's', 'Ğ' => 'g', 'ğ' => 'g',
                'Ü' => 'u', 'ü' => 'u', 'Ö' => 'o', 'ö' => 'o', 'Ç' => 'c', 'ç' => 'c'];

        return preg_replace('/\s+/u', ' ', trim(mb_strtolower(strtr($value, $map), 'UTF-8')));
    }
}
