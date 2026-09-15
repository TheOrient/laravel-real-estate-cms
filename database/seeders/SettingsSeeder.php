<?php

namespace Database\Seeders;

use App\Constants\SettingsConstants;
use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = SettingsConstants::getDefaultSettings();

        foreach ($settings as $settingData) {
            Setting::updateOrCreate(
                ['key' => $settingData['key']],
                [
                    'key' => $settingData['key'],
                    'value' => $settingData['value'] ?? null,
                    'sort_order' => $settingData['sort_order'] ?? 0,
                    'is_encrypted' => $settingData['is_encrypted'] ?? false,
                ]
            );
        }
    }
}
