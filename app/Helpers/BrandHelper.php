<?php

namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * BrandHelper
 *
 * Replaces dynamic brand tokens in content with values from the
 * settings table. Allows page content / emails / meta tags to use
 * placeholders like {site_name} or {contact_email} that resolve at
 * render time. This is how the CMS stays vendor-neutral after a
 * fresh installation: tokens are written to the DB once, then the
 * site owner controls the actual values via the admin settings panel.
 *
 * Supported tokens:
 *   {site_name}       → settings.site_title
 *   {site_url}        → settings.site_url (falls back to config('app.url'))
 *   {contact_email}   → settings.contact_email
 *   {contact_phone}   → settings.contact_phone
 *   {contact_address} → settings.contact_address
 *
 * Unknown tokens are left untouched, so future tokens can be added
 * without breaking existing content.
 */
class BrandHelper
{
    /**
     * Token → setting key map. Keep this in one place so it is easy
     * to extend without hunting through views.
     */
    protected const TOKEN_MAP = [
        '{site_name}'       => 'site_title',
        '{site_url}'        => 'site_url',
        '{contact_email}'   => 'contact_email',
        '{contact_phone}'   => 'contact_phone',
        '{contact_address}' => 'contact_address',
    ];

    /**
     * Replace all known brand tokens inside the given text with their
     * current setting values. Safe to call on plain text or HTML — it
     * does a straight string replace, no HTML parsing required.
     *
     * @param  string|null  $text
     * @return string
     */
    public static function render(?string $text): string
    {
        if ($text === null || $text === '') {
            return (string) $text;
        }

        // Bail out fast when there is no token at all.
        if (! str_contains($text, '{')) {
            return $text;
        }

        $replacements = self::resolveReplacements();

        return strtr($text, $replacements);
    }

    /**
     * Build the token → value lookup. Cached per-request to avoid
     * hitting the settings table multiple times in one response.
     *
     * @return array<string, string>
     */
    protected static function resolveReplacements(): array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $replacements = [];

        foreach (self::TOKEN_MAP as $token => $settingKey) {
            $replacements[$token] = (string) self::settingValue($settingKey);
        }

        // {site_url} should fall back to APP_URL if the setting is empty.
        if ($replacements['{site_url}'] === '') {
            $replacements['{site_url}'] = (string) config('app.url');
        }

        return $cache = $replacements;
    }

    /**
     * Read a single setting value, guarding against missing tables
     * (e.g. during fresh installation before migrations have run).
     *
     * @param  string  $key
     * @return string|null
     */
    protected static function settingValue(string $key): ?string
    {
        try {
            return Cache::rememberForever(
                'brand_token.' . $key,
                fn () => Setting::where('key', $key)->value('value')
            );
        } catch (\Throwable $e) {
            // Settings table might not exist yet during initial migrate.
            return null;
        }
    }

    /**
     * Clear the cached token values. Call this from the settings
     * controller after a successful update so the new brand info
     * shows up immediately.
     */
    public static function flushCache(): void
    {
        foreach (array_values(self::TOKEN_MAP) as $key) {
            Cache::forget('brand_token.' . $key);
        }
    }
}
