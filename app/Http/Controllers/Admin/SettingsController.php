<?php

namespace App\Http\Controllers\Admin;

use App\Constants\SettingsConstants;
use App\Constants\SettingsTypeConstant;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\CacheService;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    protected CacheService $cacheService;
    protected ImageService $imageService;

    public function __construct(CacheService $cacheService, ImageService $imageService)
    {
        $this->cacheService = $cacheService;
        $this->imageService = $imageService;
    }

    /**
     * Display the settings page.
     */
    public function index()
    {
        // Get default settings from constants
        $defaultSettings = SettingsConstants::getDefaultSettings();

        // Ensure all settings from constants exist in database (only create if not exists, don't update existing)
        foreach ($defaultSettings as $settingData) {
            // Only save key, value, sort_order, is_encrypted to database
            Setting::firstOrCreate(
                ['key' => $settingData['key']],
                [
                    'key' => $settingData['key'],
                    'value' => $settingData['value'] ?? null,
                    'sort_order' => $settingData['sort_order'] ?? 0,
                    'is_encrypted' => $settingData['is_encrypted'] ?? false,
                ]
            );
        }

        // Get settings from database (with user-modified values)
        $settingsGrouped = collect();
        $defaultSettingsByGroup = collect($defaultSettings)->groupBy('group');

        foreach ($defaultSettingsByGroup as $group => $defaultGroupSettings) {
            $groupSettings = collect();

            foreach ($defaultGroupSettings as $defaultSetting) {
                $setting = Setting::where('key', $defaultSetting['key'])->first();
                if ($setting) {
                    $groupSettings->push($setting);
                }
            }

            // Sort by sort_order from constants
            $settingsGrouped->put($group, $groupSettings->sortBy(function ($setting) use ($defaultSettings) {
                foreach ($defaultSettings as $defaultSetting) {
                    if ($defaultSetting['key'] === $setting->key) {
                        return $defaultSetting['sort_order'] ?? 0;
                    }
                }
                return 0;
            })->values());
        }

        return view('admin.settings.index', compact('settingsGrouped'));
    }

    /**
     * Update the settings.
     */
    public function update(Request $request)
    {
        // Get default settings from constants to ensure all settings are validated
        $defaultSettings = SettingsConstants::getDefaultSettings();

        // Ensure all settings from constants exist in database (only create if not exists, don't update existing)
        foreach ($defaultSettings as $settingData) {
            // Only save key, value, sort_order, is_encrypted to database
            Setting::firstOrCreate(
                ['key' => $settingData['key']],
                [
                    'key' => $settingData['key'],
                    'value' => $settingData['value'] ?? null,
                    'sort_order' => $settingData['sort_order'] ?? 0,
                    'is_encrypted' => $settingData['is_encrypted'] ?? false,
                ]
            );
        }

        // Get all settings from database for validation and update
        $settings = Setting::all();
        $rules = [];
        $messages = [];

        // Build validation rules based on setting types from constants
        foreach ($settings as $setting) {
            $type = SettingsConstants::getType($setting->key);
            $description = SettingsConstants::getDescription($setting->key) ?? $setting->key;

            // if its a site url,
            if ($setting->key === 'site_url') {
                // trim the value and remove the trailing slash
                $value = trim($request->input($setting->key));
                $value = rtrim($value, '/');
                $request->merge([$setting->key => $value]);
                $rules[$setting->key] = 'required|url|max:255';
                continue;
            }

            switch ($type) {
                case SettingsTypeConstant::EMAIL:
                    $rules[$setting->key] = 'nullable|email|max:255';
                    break;
                case SettingsTypeConstant::IMAGE:
                    $rules[$setting->key] = 'nullable|image|max:2048'; // 2MB max
                    break;
                case SettingsTypeConstant::TEXT:
                    $rules[$setting->key] = 'nullable|string|max:255';
                    break;
                case SettingsTypeConstant::TEXTAREA:
                    $rules[$setting->key] = 'nullable|string|max:1000';
                    break;
                case SettingsTypeConstant::CODE:
                    $rules[$setting->key] = 'nullable|string|max:10000';
                    break;
                case SettingsTypeConstant::NUMBER:
                    $rules[$setting->key] = 'nullable|numeric';
                    break;
                case SettingsTypeConstant::PASSWORD:
                    $rules[$setting->key] = 'nullable|string|min:6|max:255';
                    break;
                case SettingsTypeConstant::BOOLEAN:
                    $rules[$setting->key] = 'nullable|boolean';
                    break;
                default:
                    $rules[$setting->key] = 'nullable|string|max:255';
            }

            $messages[$setting->key . '.email'] = $description . ' geçerli bir e-posta adresi olmalıdır.';
            $messages[$setting->key . '.image'] = $description . ' geçerli bir resim dosyası olmalıdır.';
            $messages[$setting->key . '.max'] = $description . ' çok uzun.';
        }

        $request->validate($rules, $messages);

        foreach ($settings as $setting) {
            $value = $request->input($setting->key);
            $type = SettingsConstants::getType($setting->key);

            // Handle file uploads for image type
            if ($type === SettingsTypeConstant::IMAGE && $request->hasFile($setting->key)) {
                // Special handling for home hero image: always overwrite the same file
                if ($setting->key === 'home_hero_image') {
                    $file = $request->file($setting->key);

                    // Sabit dosya adı: public/uploads/settings/home-hero.jpg
                    $directory = 'uploads/settings';
                    $filename = 'home-hero.jpg';
                    $relativePath = $directory . '/' . $filename;

                    // Klasör yoksa oluştur (doğrudan public/uploads altında)
                    $fullDirectory = public_path($directory);
                    if (!is_dir($fullDirectory)) {
                        mkdir($fullDirectory, 0755, true);
                    }

                    // Aynı isimle dosyayı overwrite et
                    $file->move($fullDirectory, $filename);

                    // Ayar değerini sabit path olarak tut
                    $value = $relativePath;

                    // Glide cache'ini bu görsel için temizle
                    try {
                        $this->imageService->clearImageCache($relativePath);
                    } catch (\Throwable $e) {
                        // Sessizce geç; cache temizlenemese bile upload başarılı olsun
                    }
                } else {
                    // Diğer tüm image ayarları için de public/uploads/settings altını kullan
                    $file = $request->file($setting->key);

                    $directory = 'uploads/settings';
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $relativePath = $directory . '/' . $filename;

                    // Klasör yoksa oluştur
                    $fullDirectory = public_path($directory);
                    if (!is_dir($fullDirectory)) {
                        mkdir($fullDirectory, 0755, true);
                    }

                    // Eski dosya varsa sil
                    if ($setting->value) {
                        $oldPath = public_path($setting->value);
                        if (is_file($oldPath)) {
                            @unlink($oldPath);
                        }
                    }

                    // Yeni dosyayı taşı
                    $file->move($fullDirectory, $filename);
                    $value = $relativePath;
                }
            } elseif ($type === SettingsTypeConstant::IMAGE && !$request->hasFile($setting->key)) {
                // Keep existing value if no new file uploaded
                continue;
            } elseif ($type === SettingsTypeConstant::BOOLEAN) {
                $value = $request->boolean($setting->key) ? '1' : '0';
            } elseif ($setting->is_encrypted && empty($value)) {
                // For encrypted fields: if empty, keep existing value (don't update)
                continue;
            } elseif (($type === SettingsTypeConstant::PASSWORD || $setting->is_encrypted) && $value === '••••••••••••••••') {
                // If value is the masked placeholder, skip updating
                continue;
            }

            // Update setting if value provided or if it's boolean (for unchecked checkboxes)
            if ($value !== null || $type === SettingsTypeConstant::BOOLEAN) {
                // Get is_encrypted from constants
                $settingData = SettingsConstants::getSettingByKey($setting->key);
                $isEncrypted = $settingData['is_encrypted'] ?? $setting->is_encrypted;

                // Use Setting::set which handles encryption automatically based on is_encrypted field
                Setting::set($setting->key, $value, $isEncrypted);
            }
        }

        // Flush dynamic brand tokens ({site_name}, {contact_email}, etc.)
        // so the new values surface immediately on every page.
        \App\Helpers\BrandHelper::flushCache();

        return redirect()->route('admin.settings.index')
            ->with('success', 'Ayarlar başarıyla güncellendi.');
    }

    /**
     * Delete uploaded image
     */
    public function deleteImage(Request $request)
    {
        $request->validate([
            'key' => 'required|string|exists:settings,key'
        ]);

        $setting = Setting::where('key', $request->key)->first();
        $type = SettingsConstants::getType($request->key);

        if ($setting && $type === SettingsTypeConstant::IMAGE && $setting->value) {
            // Delete file from appropriate storage (hero için fiziksel dosyayı silme)
            if ($setting->key === 'home_hero_image') {
                // Sadece ayar değerini sıfırla; varsayılan dosya kullanılmaya devam eder
                Setting::set($setting->key, null);

                // Hero görseli için Glide cache'ini de temizle
                try {
                    $this->imageService->clearImageCache($setting->value);
                } catch (\Throwable $e) {
                    // ignore
                }
                return response()->json(['success' => true]);
            } else {
                // Diğer image ayarları için public/uploads altından sil
                $fullPath = public_path($setting->value);
                if (is_file($fullPath)) {
                    @unlink($fullPath);
                }

                // Glide cache'ini de temizle
                try {
                    $this->imageService->clearImageCache($setting->value);
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            // Update setting value to null
            Setting::set($setting->key, null);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }

    /**
     * Clear all cache and config
     */
    public function clearCache(Request $request)
    {
        try {
            $results = $this->cacheService->clearAll();

            // Check if all operations were successful
            $allSuccess = !in_array(false, $results, true);

            if ($allSuccess) {
                return redirect()->back()->with('success', 'Tüm önbellek ve yapılandırma başarıyla temizlendi!');
            } else {
                return redirect()->back()->with('error', 'Bazı önbellekler temizlenirken sorun oluştu.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Önbellek temizlenirken hata oluştu: ' . $e->getMessage());
        }
    }
}
