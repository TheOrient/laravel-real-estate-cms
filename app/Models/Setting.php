<?php

namespace App\Models;

use App\Constants\SettingsConstants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected static ?array $runtimeValues = null;

    protected $fillable = [
        'key',
        'value',
        'sort_order',
        'is_encrypted'
    ];

    /**
     * Get a setting value by key (auto-decrypt if encrypted)
     */
    public static function get(string $key, $default = null)
    {
        $values = static::runtimeValues();

        return array_key_exists($key, $values) ? $values[$key] : $default;
    }

    protected static function runtimeValues(): array
    {
        if (static::$runtimeValues !== null) {
            return static::$runtimeValues;
        }

        return static::$runtimeValues = Cache::remember('settings_runtime_values', 3600, function () {
            return static::query()->get(['key', 'value', 'is_encrypted'])
                ->mapWithKeys(function (self $setting) {
                    $value = $setting->value;
                    if ($setting->is_encrypted && filled($value)) {
                        try {
                            $value = decrypt($value);
                        } catch (\Throwable) {
                            $value = null;
                        }
                    }

                    return [$setting->key => $value];
                })
                ->all();
        });
    }

    /**
     * Set a setting value by key (auto-encrypt if setting is marked as encrypted)
     */
    public static function set(string $key, $value, ?bool $encrypt = null): void
    {
        $setting = static::where('key', $key)->first();

        // Get is_encrypted from constants if not provided
        if ($encrypt === null) {
            $settingData = SettingsConstants::getSettingByKey($key);
            $encrypt = $settingData['is_encrypted'] ?? ($setting && $setting->is_encrypted);
        }

        $shouldEncrypt = $encrypt && !empty($value);
        $data = [
            'value' => $shouldEncrypt ? encrypt($value) : $value,
            'is_encrypted' => $encrypt,
        ];

        static::updateOrCreate(['key' => $key], $data);

        // Clear cache
        Cache::forget('setting_' . $key);
        Cache::forget('all_settings');
        Cache::forget('settings_runtime_values');
        static::$runtimeValues = null;
    }

    /**
     * Get all settings as key-value array
     */
    public static function getAll(): array
    {
        return static::runtimeValues();
    }

    /**
     * Get settings grouped by group
     */
    public static function getByGroup(string $group): array
    {
        $cacheKey = 'settings_group_' . $group;

        return Cache::remember($cacheKey, 3600, function () use ($group) {
            $defaultSettings = SettingsConstants::getDefaultSettings();
            $groupSettings = [];

            foreach ($defaultSettings as $defaultSetting) {
                if ($defaultSetting['group'] === $group) {
                    $setting = static::where('key', $defaultSetting['key'])->first();
                    if ($setting) {
                        $groupSettings[] = $setting->toArray();
                    }
                }
            }

            // Sort by sort_order
            usort($groupSettings, function ($a, $b) use ($defaultSettings) {
                $aOrder = self::getSortOrder($a['key'], $defaultSettings);
                $bOrder = self::getSortOrder($b['key'], $defaultSettings);
                return $aOrder <=> $bOrder;
            });

            return $groupSettings;
        });
    }

    /**
     * Get sort order for a key from default settings
     */
    private static function getSortOrder(string $key, array $defaultSettings): int
    {
        foreach ($defaultSettings as $setting) {
            if ($setting['key'] === $key) {
                return $setting['sort_order'] ?? 0;
            }
        }
        return 0;
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        $settings = static::all();
        foreach ($settings as $setting) {
            Cache::forget('setting_' . $setting->key);
        }
        Cache::forget('all_settings');
        Cache::forget('settings_runtime_values');
        static::$runtimeValues = null;

        // Clear group caches
        $groups = SettingsConstants::getAllGroups();
        foreach ($groups as $group) {
            Cache::forget('settings_group_' . $group);
        }
    }

    /**
     * Boot method to clear cache when model is updated
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            static::clearCache();
        });

        static::deleted(function () {
            static::clearCache();
        });
    }

    /**
     * Get all available groups
     */
    public static function getGroups(): array
    {
        return SettingsConstants::getAllGroups();
    }

    /**
     * Get type from constants
     */
    public function getTypeAttribute(): ?string
    {
        return SettingsConstants::getType($this->key);
    }

    /**
     * Get group from constants
     */
    public function getGroupAttribute(): ?string
    {
        return SettingsConstants::getGroup($this->key);
    }

    /**
     * Get description from constants
     */
    public function getDescriptionAttribute(): ?string
    {
        return SettingsConstants::getDescription($this->key);
    }

    /**
     * Get options from constants
     */
    public function getOptionsAttribute(): ?string
    {
        return SettingsConstants::getOptions($this->key);
    }

    /**
     * Helper method to get site title
     */
    public static function getSiteTitle(): string
    {
        return static::get('site_title', 'İlan Sistemi');
    }

    /**
     * Helper method to get site description
     */
    public static function getSiteDescription(): string
    {
        return static::get('site_description', 'Modern ve kullanıcı dostu ilan sitesi');
    }

    /**
     * Helper method to get site logo
     */
    public static function getSiteLogo(): ?string
    {
        return static::get('site_logo');
    }

    /**
     * Get setting value without decryption (for admin display)
     */
    public function getRawValueAttribute()
    {
        return $this->attributes['value'] ?? null;
    }

    /**
     * Check if setting has a value (without decrypting)
     */
    public function hasValue(): bool
    {
        return !empty($this->attributes['value']);
    }
}
