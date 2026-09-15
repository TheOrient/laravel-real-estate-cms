<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;

class LanguageSeeder extends Seeder
{
    /**
     * Seed the languages table from config/app.php
     */
    public function run(): void
    {
        $configLanguages = Config::get('app.languages', []);
        $defaultCode = Config::get('app.default_language', 'tr');

        if (empty($configLanguages)) {
            $this->command?->warn('No languages defined in config/app.php');
            return;
        }

        $sortOrder = 0;

        $activeLanguages = array_keys($configLanguages);

        // Önce tüm dillerin is_default alanını sıfırla
        Language::query()->update(['is_default' => false]);

        foreach ($configLanguages as $code => $data) {
            $sortOrder++;

            Language::updateOrCreate(
                ['code' => $data['code']],
                [
                    'title' => $data['name'],
                    'sort_order' => $sortOrder,
                    'is_active' => in_array($code, $activeLanguages),
                    'is_default' => $code === $defaultCode,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command?->info('Languages seeded from config/app.php');
        $this->command?->info('Active languages: ' . implode(', ', $activeLanguages));
    }
}
