<?php

namespace Database\Seeders;

use App\Helpers\CategoryHelper;
use App\Models\Category;
use App\Models\CategoryDescription;
use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Emlak odaklı detaylı kategori yapısı
        $categories = [
            'Emlak' => [
                'icon' => 'ri-home-line',
                'is_filterable' => true,
                'subcategories' => [
                    'Konut' => [
                        'icon' => 'ri-building-line',
                        'is_filterable' => true,
                        'subcategories' => [
                            'Satılık Daire' => ['icon' => 'ri-building-2-line', 'is_filterable' => true],
                            'Kiralık Daire' => ['icon' => 'ri-building-2-line', 'is_filterable' => true],
                            'Satılık Villa' => ['icon' => 'ri-home-8-line', 'is_filterable' => true],
                            'Kiralık Villa' => ['icon' => 'ri-home-8-line', 'is_filterable' => true],
                            'Satılık Müstakil Ev' => ['icon' => 'ri-home-4-line', 'is_filterable' => true],
                            'Kiralık Müstakil Ev' => ['icon' => 'ri-home-4-line', 'is_filterable' => true],
                            'Rezidans' => ['icon' => 'ri-community-line', 'is_filterable' => true],
                            'Dublex' => ['icon' => 'ri-building-3-line', 'is_filterable' => true],
                            'Triplex' => ['icon' => 'ri-building-4-line', 'is_filterable' => true],
                            'Çiftlik Evi' => ['icon' => 'ri-home-5-line', 'is_filterable' => true],
                            'Prefabrik Ev' => ['icon' => 'ri-home-6-line', 'is_filterable' => true],
                            'Yazlık' => ['icon' => 'ri-sun-line', 'is_filterable' => true],
                            'Dağ Evi' => ['icon' => 'ri-mountain-line', 'is_filterable' => true],
                        ],
                    ],
                    'İş Yeri' => [
                        'icon' => 'ri-store-line',
                        'is_filterable' => true,
                        'subcategories' => [
                            'Satılık Ofis' => ['icon' => 'ri-briefcase-4-line', 'is_filterable' => true],
                            'Kiralık Ofis' => ['icon' => 'ri-briefcase-4-line', 'is_filterable' => true],
                            'Satılık Dükkan' => ['icon' => 'ri-store-3-line', 'is_filterable' => true],
                            'Kiralık Dükkan' => ['icon' => 'ri-store-3-line', 'is_filterable' => true],
                            'Satılık Mağaza' => ['icon' => 'ri-shopping-bag-line', 'is_filterable' => true],
                            'Kiralık Mağaza' => ['icon' => 'ri-shopping-bag-line', 'is_filterable' => true],
                            'Satılık İşyeri' => ['icon' => 'ri-building-line', 'is_filterable' => true],
                            'Kiralık İşyeri' => ['icon' => 'ri-building-line', 'is_filterable' => true],
                            'Satılık Depo' => ['icon' => 'ri-archive-line', 'is_filterable' => true],
                            'Kiralık Depo' => ['icon' => 'ri-archive-line', 'is_filterable' => true],
                            'Satılık Fabrika' => ['icon' => 'ri-factory-line', 'is_filterable' => true],
                            'Kiralık Fabrika' => ['icon' => 'ri-factory-line', 'is_filterable' => true],
                            'Satılık Atölye' => ['icon' => 'ri-tools-line', 'is_filterable' => true],
                            'Kiralık Atölye' => ['icon' => 'ri-tools-line', 'is_filterable' => true],
                            'Satılık Büfe' => ['icon' => 'ri-store-2-line', 'is_filterable' => true],
                            'Kiralık Büfe' => ['icon' => 'ri-store-2-line', 'is_filterable' => true],
                        ],
                    ],
                    'Arsa' => [
                        'icon' => 'ri-landscape-line',
                        'is_filterable' => true,
                        'subcategories' => [
                            'Satılık Arsa' => ['icon' => 'ri-landscape-line', 'is_filterable' => true],
                            'Kiralık Arsa' => ['icon' => 'ri-landscape-line', 'is_filterable' => true],
                            'İmarlı Arsa' => ['icon' => 'ri-map-pin-line', 'is_filterable' => true],
                            'İmarsız Arsa' => ['icon' => 'ri-map-pin-2-line', 'is_filterable' => true],
                        ],
                    ],
                    'Arazi' => [
                        'icon' => 'ri-plant-line',
                        'is_filterable' => true,
                        'subcategories' => [
                            'Satılık Tarla' => ['icon' => 'ri-plant-line', 'is_filterable' => true],
                            'Kiralık Tarla' => ['icon' => 'ri-plant-line', 'is_filterable' => true],
                            'Satılık Bağ' => ['icon' => 'ri-leaf-line', 'is_filterable' => true],
                            'Kiralık Bağ' => ['icon' => 'ri-leaf-line', 'is_filterable' => true],
                            'Satılık Bahçe' => ['icon' => 'ri-seedling-line', 'is_filterable' => true],
                            'Kiralık Bahçe' => ['icon' => 'ri-seedling-line', 'is_filterable' => true],
                            'Satılık Zeytinlik' => ['icon' => 'ri-plant-fill', 'is_filterable' => true],
                            'Kiralık Zeytinlik' => ['icon' => 'ri-plant-fill', 'is_filterable' => true],
                        ],
                    ],
                    'Turizm' => [
                        'icon' => 'ri-hotel-line',
                        'is_filterable' => true,
                        'subcategories' => [
                            'Satılık Otel' => ['icon' => 'ri-hotel-line', 'is_filterable' => true],
                            'Kiralık Otel' => ['icon' => 'ri-hotel-line', 'is_filterable' => true],
                            'Satılık Pansiyon' => ['icon' => 'ri-home-heart-line', 'is_filterable' => true],
                            'Kiralık Pansiyon' => ['icon' => 'ri-home-heart-line', 'is_filterable' => true],
                            'Satılık Apart' => ['icon' => 'ri-building-2-line', 'is_filterable' => true],
                            'Kiralık Apart' => ['icon' => 'ri-building-2-line', 'is_filterable' => true],
                            'Satılık Motel' => ['icon' => 'ri-hotel-bed-line', 'is_filterable' => true],
                            'Kiralık Motel' => ['icon' => 'ri-hotel-bed-line', 'is_filterable' => true],
                        ],
                    ],
                    'Bina' => [
                        'icon' => 'ri-building-4-line',
                        'is_filterable' => true,
                        'subcategories' => [
                            'Satılık Bina' => ['icon' => 'ri-building-4-line', 'is_filterable' => true],
                            'Kiralık Bina' => ['icon' => 'ri-building-4-line', 'is_filterable' => true],
                            'Satılık Apartman' => ['icon' => 'ri-community-line', 'is_filterable' => true],
                            'Kiralık Apartman' => ['icon' => 'ri-community-line', 'is_filterable' => true],
                        ],
                    ],
                ],
            ],
        ];

        // Create the categories
        $this->createCategories($categories);
    }

    /**
     * Recursively create categories and their subcategories
     */
    private function createCategories(array $categories, ?int $parentId = null): void
    {
        // Ücretsiz sürüm: yalnızca varsayılan dil için kategori açıklaması
        $defaultLanguage = Language::where('is_default', true)->first()
            ?? Language::where('code', config('app.default_language', 'tr'))->first();
        if (! $defaultLanguage) {
            throw new \RuntimeException('Default language not found. Run LanguageSeeder first.');
        }

        foreach ($categories as $name => $data) {
            // Create category without translatable fields
            $category = Category::create([
                'icon' => $data['icon'] ?? null,
                'parent_id' => $parentId,
                'is_active' => true,
                'is_filterable' => $data['is_filterable'] ?? false,
                'listings_count' => 0, // Will be updated after listings are created
            ]);

            CategoryDescription::create([
                'category_id' => $category->id,
                'language_id' => $defaultLanguage->id,
                'name' => $name,
                'slug' => Str::slug($name),
            ]);

            $category->load(['descriptions', 'parent.descriptions']);
            $fullSlug = $this->getFullCategorySlugPath($category, $defaultLanguage->id);
            $desc = $category->descriptions()->where('language_id', $defaultLanguage->id)->first();
            if ($desc) {
                $desc->slug = $fullSlug;
                $desc->save();
            }

            // If this category has subcategories, create them too
            if (isset($data['subcategories'])) {
                $this->createCategories($data['subcategories'], $category->id);
            }
        }
    }

    /**
     * Get full category slug path including parent path for a specific language
     */
    private function getFullCategorySlugPath(Category $category, int $languageId): string
    {
        $slugParts = [];
        $current = $category;

        // Build slug path from current to root
        while ($current) {
            // Reload descriptions if not loaded
            if (!$current->relationLoaded('descriptions')) {
                $current->load('descriptions');
            }

            $description = $current->descriptions->where('language_id', $languageId)->first();
            if ($description && $description->name) {
                // Generate slug from name
                $slugParts[] = Str::slug($description->name);
            } else {
                // Fallback: use category ID if no description found
                $slugParts[] = 'category-' . $current->id;
            }

            // Load parent if exists
            if ($current->parent_id) {
                $current = Category::with('descriptions')->find($current->parent_id);
            } else {
                $current = null;
            }
        }

        // Reverse to get root to current order
        $slugParts = array_reverse($slugParts);

        return implode('/', $slugParts);
    }
}
