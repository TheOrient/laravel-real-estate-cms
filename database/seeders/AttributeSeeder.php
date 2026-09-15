<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeDescription;
use App\Models\AttributeValue;
use App\Models\AttributeValueDescription;
use App\Models\Category;
use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating attributes...');

        // Ücretsiz sürüm: özellik adları/değerleri yalnızca varsayılan dilde
        $defaultLanguage = Language::where('is_default', true)->first()
            ?? Language::where('code', config('app.default_language', 'tr'))->first();
        if (! $defaultLanguage) {
            throw new \RuntimeException('Default language not found. Run LanguageSeeder first.');
        }

        // Emlak (Gayrimenkul) özellikleri
        $realEstateAttributes = [
            // Temel Özellikler
            [
                'name_tr' => 'Oda Sayısı',
                'name_en' => 'Number of Rooms',
                'display_type_search' => 'select',
                'display_type_user_panel' => 'select',
                'is_filterable' => true,
                'show_in_listing' => true,
                'is_required' => true,
                'allow_multiple' => false,
                'values' => [
                    ['tr' => '1+0 (Stüdyo)', 'en' => '1+0 (Studio)'],
                    ['tr' => '1+1', 'en' => '1+1'],
                    ['tr' => '2+1', 'en' => '2+1'],
                    ['tr' => '3+1', 'en' => '3+1'],
                    ['tr' => '4+1', 'en' => '4+1'],
                    ['tr' => '5+1', 'en' => '5+1'],
                    ['tr' => '6+1', 'en' => '6+1'],
                    ['tr' => '7+1 ve üzeri', 'en' => '7+1 and above'],
                ]
            ],
            [
                'name_tr' => 'Brüt Metrekare',
                'name_en' => 'Gross Square Meters',
                'display_type_search' => 'number',
                'display_type_user_panel' => 'number',
                'is_filterable' => true,
                'show_in_listing' => true,
                'is_required' => true,
                'allow_multiple' => false,
                'min_value' => 20,
                'max_value' => 10000,
                'input_placeholder_tr' => 'Brüt m²',
                'input_placeholder_en' => 'Gross m²',
            ],
            [
                'name_tr' => 'Net Metrekare',
                'name_en' => 'Net Square Meters',
                'display_type_search' => 'number',
                'display_type_user_panel' => 'number',
                'is_filterable' => true,
                'show_in_listing' => true,
                'is_required' => false,
                'allow_multiple' => false,
                'min_value' => 15,
                'max_value' => 9000,
                'input_placeholder_tr' => 'Net m²',
                'input_placeholder_en' => 'Net m²',
            ],
            [
                'name_tr' => 'Banyo Sayısı',
                'name_en' => 'Number of Bathrooms',
                'display_type_search' => 'select',
                'display_type_user_panel' => 'select',
                'is_filterable' => true,
                'show_in_listing' => true,
                'is_required' => false,
                'allow_multiple' => false,
                'values' => [
                    ['tr' => '1', 'en' => '1'],
                    ['tr' => '2', 'en' => '2'],
                    ['tr' => '3', 'en' => '3'],
                    ['tr' => '4', 'en' => '4'],
                    ['tr' => '5+', 'en' => '5+'],
                ]
            ],
            [
                'name_tr' => 'Bina Yaşı',
                'name_en' => 'Building Age',
                'display_type_search' => 'select',
                'display_type_user_panel' => 'select',
                'is_filterable' => true,
                'show_in_listing' => true,
                'is_required' => false,
                'allow_multiple' => false,
                'values' => [
                    ['tr' => '0 (Yeni)', 'en' => '0 (New)'],
                    ['tr' => '1-5 Yıl', 'en' => '1-5 Years'],
                    ['tr' => '6-10 Yıl', 'en' => '6-10 Years'],
                    ['tr' => '11-15 Yıl', 'en' => '11-15 Years'],
                    ['tr' => '16-20 Yıl', 'en' => '16-20 Years'],
                    ['tr' => '21-25 Yıl', 'en' => '21-25 Years'],
                    ['tr' => '26-30 Yıl', 'en' => '26-30 Years'],
                    ['tr' => '31+ Yıl', 'en' => '31+ Years'],
                ]
            ],
            [
                'name_tr' => 'Bulunduğu Kat',
                'name_en' => 'Floor',
                'display_type_search' => 'select',
                'display_type_user_panel' => 'select',
                'is_filterable' => true,
                'show_in_listing' => true,
                'is_required' => false,
                'allow_multiple' => false,
                'values' => [
                    ['tr' => 'Bodrum Kat', 'en' => 'Basement'],
                    ['tr' => 'Zemin Kat', 'en' => 'Ground Floor'],
                    ['tr' => 'Bahçe Katı', 'en' => 'Garden Floor'],
                    ['tr' => 'Giriş Katı', 'en' => 'Entrance Floor'],
                    ['tr' => '1. Kat', 'en' => '1st Floor'],
                    ['tr' => '2. Kat', 'en' => '2nd Floor'],
                    ['tr' => '3. Kat', 'en' => '3rd Floor'],
                    ['tr' => '4. Kat', 'en' => '4th Floor'],
                    ['tr' => '5. Kat', 'en' => '5th Floor'],
                    ['tr' => '6. Kat', 'en' => '6th Floor'],
                    ['tr' => '7. Kat', 'en' => '7th Floor'],
                    ['tr' => '8. Kat', 'en' => '8th Floor'],
                    ['tr' => '9. Kat', 'en' => '9th Floor'],
                    ['tr' => '10. Kat', 'en' => '10th Floor'],
                    ['tr' => '10+ Kat', 'en' => '10+ Floor'],
                    ['tr' => 'Çatı Katı', 'en' => 'Attic Floor'],
                    ['tr' => 'Villa Tipi', 'en' => 'Villa Type'],
                    ['tr' => 'Müstakil', 'en' => 'Detached'],
                ]
            ],
            [
                'name_tr' => 'Kat Sayısı',
                'name_en' => 'Total Floors',
                'display_type_search' => 'select',
                'display_type_user_panel' => 'select',
                'is_filterable' => true,
                'show_in_listing' => true,
                'is_required' => false,
                'allow_multiple' => false,
                'values' => [
                    ['tr' => '1', 'en' => '1'],
                    ['tr' => '2', 'en' => '2'],
                    ['tr' => '3', 'en' => '3'],
                    ['tr' => '4', 'en' => '4'],
                    ['tr' => '5', 'en' => '5'],
                    ['tr' => '6-10', 'en' => '6-10'],
                    ['tr' => '11-15', 'en' => '11-15'],
                    ['tr' => '16-20', 'en' => '16-20'],
                    ['tr' => '20+', 'en' => '20+'],
                ]
            ],

            // Isıtma ve Yakıt
            [
                'name_tr' => 'Isıtma Tipi',
                'name_en' => 'Heating Type',
                'display_type_search' => 'select',
                'display_type_user_panel' => 'select',
                'is_filterable' => true,
                'show_in_listing' => true,
                'is_required' => false,
                'allow_multiple' => false,
                'values' => [
                    ['tr' => 'Yok', 'en' => 'None'],
                    ['tr' => 'Soba', 'en' => 'Stove'],
                    ['tr' => 'Doğalgaz Sobası', 'en' => 'Natural Gas Stove'],
                    ['tr' => 'Kat Kaloriferi', 'en' => 'Floor Heating'],
                    ['tr' => 'Merkezi Sistem', 'en' => 'Central System'],
                    ['tr' => 'Kombi (Doğalgaz)', 'en' => 'Boiler (Natural Gas)'],
                    ['tr' => 'Kombi (Elektrik)', 'en' => 'Boiler (Electric)'],
                    ['tr' => 'Yerden Isıtma', 'en' => 'Underfloor Heating'],
                    ['tr' => 'Klima', 'en' => 'Air Conditioning'],
                    ['tr' => 'Fancoil Ünitesi', 'en' => 'Fancoil Unit'],
                    ['tr' => 'Güneş Enerjisi', 'en' => 'Solar Energy'],
                    ['tr' => 'Jeotermal', 'en' => 'Geothermal'],
                    ['tr' => 'Şömine', 'en' => 'Fireplace'],
                ]
            ],

            // Cephe ve Görünüm
            [
                'name_tr' => 'Cephe',
                'name_en' => 'Facade',
                'display_type_search' => 'checkbox',
                'display_type_user_panel' => 'checkbox',
                'is_filterable' => true,
                'show_in_listing' => true,
                'is_required' => false,
                'allow_multiple' => true,
                'values' => [
                    ['tr' => 'Kuzey', 'en' => 'North'],
                    ['tr' => 'Güney', 'en' => 'South'],
                    ['tr' => 'Doğu', 'en' => 'East'],
                    ['tr' => 'Batı', 'en' => 'West'],
                ]
            ],
            [
                'name_tr' => 'Manzara',
                'name_en' => 'View',
                'display_type_search' => 'checkbox',
                'display_type_user_panel' => 'checkbox',
                'is_filterable' => true,
                'show_in_listing' => true,
                'is_required' => false,
                'allow_multiple' => true,
                'values' => [
                    ['tr' => 'Deniz', 'en' => 'Sea'],
                    ['tr' => 'Göl', 'en' => 'Lake'],
                    ['tr' => 'Doğa', 'en' => 'Nature'],
                    ['tr' => 'Şehir', 'en' => 'City'],
                    ['tr' => 'Boğaz', 'en' => 'Bosphorus'],
                    ['tr' => 'Park & Yeşil Alan', 'en' => 'Park & Green Area'],
                    ['tr' => 'Havuz', 'en' => 'Pool'],
                ]
            ],

            // İç Özellikler
            [
                'name_tr' => 'İç Özellikler',
                'name_en' => 'Interior Features',
                'display_type_search' => 'checkbox',
                'display_type_user_panel' => 'checkbox',
                'is_filterable' => true,
                'show_in_listing' => true,
                'is_required' => false,
                'allow_multiple' => true,
                'values' => [
                    ['tr' => 'ADSL', 'en' => 'ADSL'],
                    ['tr' => 'Alarm (Hırsız)', 'en' => 'Alarm (Burglar)'],
                    ['tr' => 'Alarm (Yangın)', 'en' => 'Alarm (Fire)'],
                    ['tr' => 'Amerikan Kapı', 'en' => 'American Door'],
                    ['tr' => 'Amerikan Mutfak', 'en' => 'American Kitchen'],
                    ['tr' => 'Ankastre Fırın', 'en' => 'Built-in Oven'],
                    ['tr' => 'Asma Tavan', 'en' => 'False Ceiling'],
                    ['tr' => 'Beyaz Eşya', 'en' => 'White Goods'],
                    ['tr' => 'Bulaşık Makinesi', 'en' => 'Dishwasher'],
                    ['tr' => 'Buzdolabı', 'en' => 'Refrigerator'],
                    ['tr' => 'Çamaşır Makinesi', 'en' => 'Washing Machine'],
                    ['tr' => 'Çamaşır Odası', 'en' => 'Laundry Room'],
                    ['tr' => 'Çelik Kapı', 'en' => 'Steel Door'],
                    ['tr' => 'Duşakabin', 'en' => 'Shower Cabin'],
                    ['tr' => 'Ebeveyn Banyosu', 'en' => 'Master Bathroom'],
                    ['tr' => 'Fiber İnternet', 'en' => 'Fiber Internet'],
                    ['tr' => 'Giyinme Odası', 'en' => 'Dressing Room'],
                    ['tr' => 'Görüntülü Diafon', 'en' => 'Video Intercom'],
                    ['tr' => 'Gömme Dolap', 'en' => 'Built-in Wardrobe'],
                    ['tr' => 'Hilton Banyo', 'en' => 'Hilton Bathroom'],
                    ['tr' => 'Jakuzi', 'en' => 'Jacuzzi'],
                    ['tr' => 'Klima', 'en' => 'Air Conditioning'],
                    ['tr' => 'Laminat Zemin', 'en' => 'Laminate Floor'],
                    ['tr' => 'Marley Zemin', 'en' => 'Marble Floor'],
                    ['tr' => 'Mobilyalı', 'en' => 'Furnished'],
                    ['tr' => 'Mutfak (Ankastre)', 'en' => 'Kitchen (Built-in)'],
                    ['tr' => 'Mutfak (Laminat)', 'en' => 'Kitchen (Laminate)'],
                    ['tr' => 'Panjur', 'en' => 'Shutter'],
                    ['tr' => 'Parke Zemin', 'en' => 'Parquet Floor'],
                    ['tr' => 'PVC Doğrama', 'en' => 'PVC Window'],
                    ['tr' => 'Seramik Zemin', 'en' => 'Ceramic Floor'],
                    ['tr' => 'Spot Aydınlatma', 'en' => 'Spot Lighting'],
                    ['tr' => 'Şömine', 'en' => 'Fireplace'],
                    ['tr' => 'Vestiyer', 'en' => 'Cloakroom'],
                    ['tr' => 'Wi-Fi', 'en' => 'Wi-Fi'],
                ]
            ],

            // Dış Özellikler
            [
                'name_tr' => 'Dış Özellikler',
                'name_en' => 'Exterior Features',
                'display_type_search' => 'checkbox',
                'display_type_user_panel' => 'checkbox',
                'is_filterable' => true,
                'show_in_listing' => true,
                'is_required' => false,
                'allow_multiple' => true,
                'values' => [
                    ['tr' => 'Balkon', 'en' => 'Balcony'],
                    ['tr' => 'Bahçe', 'en' => 'Garden'],
                    ['tr' => 'Bahçe (Özel)', 'en' => 'Garden (Private)'],
                    ['tr' => 'Bahçe (Ortak)', 'en' => 'Garden (Shared)'],
                    ['tr' => 'Garaj', 'en' => 'Garage'],
                    ['tr' => 'Otopark (Açık)', 'en' => 'Parking (Open)'],
                    ['tr' => 'Otopark (Kapalı)', 'en' => 'Parking (Covered)'],
                    ['tr' => 'Teras', 'en' => 'Terrace'],
                    ['tr' => 'Çatı Katı', 'en' => 'Attic'],
                    ['tr' => 'Veranda', 'en' => 'Veranda'],
                ]
            ],

            // Site İçi Özellikler
            [
                'name_tr' => 'Site İçi Özellikler',
                'name_en' => 'Complex Features',
                'display_type_search' => 'checkbox',
                'display_type_user_panel' => 'checkbox',
                'is_filterable' => true,
                'show_in_listing' => true,
                'is_required' => false,
                'allow_multiple' => true,
                'values' => [
                    ['tr' => 'Açık Yüzme Havuzu', 'en' => 'Outdoor Pool'],
                    ['tr' => 'Kapalı Yüzme Havuzu', 'en' => 'Indoor Pool'],
                    ['tr' => 'Çocuk Oyun Parkı', 'en' => 'Children\'s Playground'],
                    ['tr' => 'Spor Alanı', 'en' => 'Sports Area'],
                    ['tr' => 'Fitness Center', 'en' => 'Fitness Center'],
                    ['tr' => 'Sauna', 'en' => 'Sauna'],
                    ['tr' => 'Hamam', 'en' => 'Turkish Bath'],
                    ['tr' => 'Buhar Odası', 'en' => 'Steam Room'],
                    ['tr' => 'Güvenlik', 'en' => 'Security'],
                    ['tr' => '7/24 Güvenlik', 'en' => '24/7 Security'],
                    ['tr' => 'Kamera Sistemi', 'en' => 'Camera System'],
                    ['tr' => 'Site İçerisinde', 'en' => 'Within Complex'],
                    ['tr' => 'Jeneratör', 'en' => 'Generator'],
                    ['tr' => 'Asansör', 'en' => 'Elevator'],
                    ['tr' => 'Uydu', 'en' => 'Satellite'],
                    ['tr' => 'Kreş', 'en' => 'Nursery'],
                    ['tr' => 'Sosyal Tesis', 'en' => 'Social Facility'],
                    ['tr' => 'Tenis Kortu', 'en' => 'Tennis Court'],
                    ['tr' => 'Basketbol Sahası', 'en' => 'Basketball Court'],
                    ['tr' => 'Yürüyüş Yolu', 'en' => 'Walking Path'],
                    ['tr' => 'Bisiklet Yolu', 'en' => 'Bicycle Path'],
                    ['tr' => 'Peyzaj', 'en' => 'Landscaping'],
                    ['tr' => 'Çocuk Havuzu', 'en' => 'Children\'s Pool'],
                    ['tr' => 'Kamelya', 'en' => 'Gazebo'],
                    ['tr' => 'Barbekü Alanı', 'en' => 'BBQ Area'],
                ]
            ],

            // Ulaşım
            [
                'name_tr' => 'Ulaşım',
                'name_en' => 'Transportation',
                'display_type_search' => 'checkbox',
                'display_type_user_panel' => 'checkbox',
                'is_filterable' => true,
                'show_in_listing' => true,
                'is_required' => false,
                'allow_multiple' => true,
                'values' => [
                    ['tr' => 'Anayola Cepheli', 'en' => 'Main Road Front'],
                    ['tr' => 'Anayola Yakın', 'en' => 'Near Main Road'],
                    ['tr' => 'Cadde Üzeri', 'en' => 'On Avenue'],
                    ['tr' => 'Denize Sıfır', 'en' => 'Beachfront'],
                    ['tr' => 'Denize Yakın', 'en' => 'Near Beach'],
                    ['tr' => 'Dolmuş/Minibüs Durağına Yakın', 'en' => 'Near Minibus Stop'],
                    ['tr' => 'Havaalanına Yakın', 'en' => 'Near Airport'],
                    ['tr' => 'Hastaneye Yakın', 'en' => 'Near Hospital'],
                    ['tr' => 'Merkezi', 'en' => 'Central'],
                    ['tr' => 'Metro/Tramvay Yakın', 'en' => 'Near Metro/Tram'],
                    ['tr' => 'Okula Yakın', 'en' => 'Near School'],
                    ['tr' => 'Şehir Merkezine Yakın', 'en' => 'Near City Center'],
                ]
            ],

            // Tapu Durumu
            [
                'name_tr' => 'Tapu Durumu',
                'name_en' => 'Title Deed Status',
                'display_type_search' => 'select',
                'display_type_user_panel' => 'select',
                'is_filterable' => true,
                'show_in_listing' => true,
                'is_required' => false,
                'allow_multiple' => false,
                'values' => [
                    ['tr' => 'Kat Mülkiyetli', 'en' => 'Condominium'],
                    ['tr' => 'Kat İrtifaklı', 'en' => 'Easement'],
                    ['tr' => 'Arsa Tapulu', 'en' => 'Land Title'],
                    ['tr' => 'Hisseli Tapulu', 'en' => 'Shared Title'],
                    ['tr' => 'Müstakil Tapulu', 'en' => 'Independent Title'],
                    ['tr' => 'Tahsisli', 'en' => 'Allocated'],
                ]
            ],

            // Kullanım Durumu
            [
                'name_tr' => 'Kullanım Durumu',
                'name_en' => 'Usage Status',
                'display_type_search' => 'select',
                'display_type_user_panel' => 'select',
                'is_filterable' => true,
                'show_in_listing' => true,
                'is_required' => false,
                'allow_multiple' => false,
                'values' => [
                    ['tr' => 'Boş', 'en' => 'Empty'],
                    ['tr' => 'Dolu', 'en' => 'Occupied'],
                    ['tr' => 'Kiracılı', 'en' => 'Tenanted'],
                    ['tr' => 'Mülk Sahibi', 'en' => 'Owner Occupied'],
                ]
            ],

            // Krediye Uygunluk
            [
                'name_tr' => 'Krediye Uygunluk',
                'name_en' => 'Loan Eligibility',
                'display_type_search' => 'select',
                'display_type_user_panel' => 'select',
                'is_filterable' => true,
                'show_in_listing' => true,
                'is_required' => false,
                'allow_multiple' => false,
                'values' => [
                    ['tr' => 'Evet', 'en' => 'Yes'],
                    ['tr' => 'Hayır', 'en' => 'No'],
                ]
            ],

            // Takas
            [
                'name_tr' => 'Takas',
                'name_en' => 'Exchange',
                'display_type_search' => 'select',
                'display_type_user_panel' => 'select',
                'is_filterable' => true,
                'show_in_listing' => false,
                'is_required' => false,
                'allow_multiple' => false,
                'values' => [
                    ['tr' => 'Evet', 'en' => 'Yes'],
                    ['tr' => 'Hayır', 'en' => 'No'],
                ]
            ],

            // Birim (Unit)
            [
                'name_tr' => 'Birim',
                'name_en' => 'Unit',
                'display_type_search' => 'select',
                'display_type_user_panel' => 'select',
                'is_filterable' => false,
                'show_in_listing' => true,
                'is_required' => false,
                'allow_multiple' => false,
                'values' => [
                    ['tr' => 'm²', 'en' => 'm²'],
                    ['tr' => 'Dönüm', 'en' => 'Acre'],
                    ['tr' => 'Hektar', 'en' => 'Hectare'],
                    ['tr' => 'Daire', 'en' => 'Apartment'],
                    ['tr' => 'Oda', 'en' => 'Room'],
                ]
            ],
        ];

        // Function to create attributes and their values
        $createAttributes = function ($attributes, $language) {
            foreach ($attributes as $attributeData) {
                $values = $attributeData['values'] ?? null;
                $nameTr = $attributeData['name_tr'];
                $inputPlaceholderTr = $attributeData['input_placeholder_tr'] ?? null;

                unset(
                    $attributeData['name_tr'],
                    $attributeData['name_en'],
                    $attributeData['input_placeholder_tr'],
                    $attributeData['input_placeholder_en'],
                    $attributeData['values']
                );

                $attribute = Attribute::create($attributeData);

                AttributeDescription::create([
                    'attribute_id' => $attribute->id,
                    'language_id' => $language->id,
                    'name' => $nameTr,
                    'input_placeholder' => $inputPlaceholderTr,
                ]);

                if (in_array($attribute->display_type_user_panel, ['select', 'radio', 'checkbox']) && $values) {
                    foreach ($values as $index => $valueData) {
                        $attributeValue = AttributeValue::create([
                            'attribute_id' => $attribute->id,
                            'order' => $index,
                        ]);

                        AttributeValueDescription::create([
                            'attribute_value_id' => $attributeValue->id,
                            'language_id' => $language->id,
                            'value' => $valueData['tr'],
                        ]);
                    }
                }
            }
        };

        $createAttributes($realEstateAttributes, $defaultLanguage);

        // Assign attributes to categories
        $this->assignAttributesToCategories();

        $this->command->info('Attributes created successfully!');
    }

    /**
     * Assign attributes to categories.
     */
    private function assignAttributesToCategories(): void
    {
        // 1. Find main categories and their subcategories
        $categories = Category::all();
        $defaultLanguage = Language::where('is_default', true)->first()
            ?? Language::where('code', config('app.default_language', 'tr'))->first();

        $realEstateCategory = $categories->filter(function ($category) use ($defaultLanguage) {
            $category->load('descriptions');
            $description = $category->descriptions->where('language_id', $defaultLanguage->id)->first();
            return $description && $description->name === 'Emlak';
        })->first();

        if (!$realEstateCategory) {
            $this->command->error('Emlak ana kategorisi bulunamadı. Lütfen önce CategorySeeder çalıştırın.');
            return;
        }

        // 2. Get all attributes
        $allAttributes = Attribute::with('descriptions')->get();

        // 3. Konut kategorilerine özel attribute'ler
        $residentialAttributeNames = [
            'Oda Sayısı', 'Brüt Metrekare', 'Net Metrekare', 'Banyo Sayısı',
            'Bina Yaşı', 'Bulunduğu Kat', 'Kat Sayısı', 'Isıtma Tipi',
            'Cephe', 'Manzara', 'İç Özellikler', 'Dış Özellikler',
            'Site İçi Özellikler', 'Ulaşım', 'Tapu Durumu', 'Kullanım Durumu',
            'Krediye Uygunluk', 'Takas'
        ];

        $residentialAttributes = $allAttributes->filter(function ($attr) use ($defaultLanguage, $residentialAttributeNames) {
            $desc = $attr->descriptions->where('language_id', $defaultLanguage->id)->first();
            return $desc && in_array($desc->name, $residentialAttributeNames);
        });

        // 4. İş yeri kategorilerine özel attribute'ler
        $commercialAttributeNames = [
            'Brüt Metrekare', 'Net Metrekare', 'Bina Yaşı', 'Bulunduğu Kat',
            'Kat Sayısı', 'Isıtma Tipi', 'İç Özellikler', 'Ulaşım',
            'Tapu Durumu', 'Kullanım Durumu', 'Krediye Uygunluk', 'Takas'
        ];

        $commercialAttributes = $allAttributes->filter(function ($attr) use ($defaultLanguage, $commercialAttributeNames) {
            $desc = $attr->descriptions->where('language_id', $defaultLanguage->id)->first();
            return $desc && in_array($desc->name, $commercialAttributeNames);
        });

        // 5. Arsa/Arazi kategorilerine özel attribute'ler
        $landAttributeNames = [
            'Brüt Metrekare', 'Cephe', 'Manzara', 'Ulaşım',
            'Tapu Durumu', 'Krediye Uygunluk', 'Takas'
        ];

        $landAttributes = $allAttributes->filter(function ($attr) use ($defaultLanguage, $landAttributeNames) {
            $desc = $attr->descriptions->where('language_id', $defaultLanguage->id)->first();
            return $desc && in_array($desc->name, $landAttributeNames);
        });

        // 6. Kategorilere attribute'leri ata
        $this->assignToCategory($realEstateCategory, 'Konut', $residentialAttributes);
        $this->assignToCategory($realEstateCategory, 'İş Yeri', $commercialAttributes);
        $this->assignToCategory($realEstateCategory, 'Arsa', $landAttributes);
        $this->assignToCategory($realEstateCategory, 'Arazi', $landAttributes);
        $this->assignToCategory($realEstateCategory, 'Turizm', $residentialAttributes);
        $this->assignToCategory($realEstateCategory, 'Bina', $commercialAttributes);

        $this->command->info('Attributes assigned to categories successfully!');
    }

    /**
     * Assign attributes to a specific category and its children
     */
    private function assignToCategory($parentCategory, $categoryName, $attributes): void
    {
        $defaultLanguage = Language::where('is_default', true)->first()
            ?? Language::where('code', config('app.default_language', 'tr'))->first();

        $category = $parentCategory->children->filter(function ($cat) use ($defaultLanguage, $categoryName) {
            $cat->load('descriptions');
            $desc = $cat->descriptions->where('language_id', $defaultLanguage->id)->first();
            return $desc && $desc->name === $categoryName;
        })->first();

        if (!$category) {
            return;
        }

        // Assign to this category and all its children recursively
        $this->assignAttributesRecursively($category, $attributes);
    }

    /**
     * Recursively assign attributes to a category and its children
     */
    private function assignAttributesRecursively(Category $category, $attributes): void
    {
        // Load descriptions for attributes
        $attributes->load('descriptions');

        // Assign attributes to this category
        foreach ($attributes as $index => $attribute) {
            // Check if attribute is already assigned to avoid duplicates
            if (!$category->attributes()->where('attributes.id', $attribute->id)->exists()) {
                // Determine position based on attribute importance
                $position = $this->getAttributePosition($attribute);

                $category->attributes()->attach($attribute->id, [
                    'order' => $index,
                    'property_detail_position' => $position
                ]);
            }
        }

        // Assign to children categories
        foreach ($category->children as $childCategory) {
            if ($childCategory->is_filterable) {
                $this->assignAttributesRecursively($childCategory, $attributes);
            }
        }
    }

    /**
     * Determine the position of an attribute in the listing detail page
     */
    private function getAttributePosition(Attribute $attribute): string
    {
        $defaultLanguage = Language::where('is_default', true)->first()
            ?? Language::where('code', config('app.default_language', 'tr'))->first();
        if (! $attribute->relationLoaded('descriptions')) {
            $attribute->load('descriptions');
        }
        $description = $attribute->descriptions->where('language_id', $defaultLanguage->id)->first();
        $attributeName = $description ? $description->name : '';

        // Top position attributes (important specs shown in main table)
        $topAttributes = [
            'Oda Sayısı', 'Brüt Metrekare', 'Net Metrekare', 'Banyo Sayısı',
            'Bina Yaşı', 'Bulunduğu Kat', 'Kat Sayısı', 'Isıtma Tipi',
            'Tapu Durumu', 'Kullanım Durumu'
        ];

        // Bottom position attributes (detailed features shown in properties section)
        $bottomAttributes = [
            'Cephe', 'Manzara', 'İç Özellikler', 'Dış Özellikler',
            'Site İçi Özellikler', 'Ulaşım', 'Krediye Uygunluk', 'Takas'
        ];

        if (in_array($attributeName, $topAttributes)) {
            return 'top';
        } elseif (in_array($attributeName, $bottomAttributes)) {
            return 'bottom';
        }

        // Default to bottom for unknown attributes
        return 'bottom';
    }
}
