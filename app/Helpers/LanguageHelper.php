<?php

namespace App\Helpers;

use App\Models\Language;

class LanguageHelper
{
    public static $active_languages = null;
    public static function getActiveLanguages()
    {
        if (self::$active_languages) {
            return self::$active_languages;
        }
        // Ücretsiz sürüm: yalnızca varsayılan dil
        self::$active_languages = Language::runtimeList()
            ->where('is_active', true)
            ->sortBy(fn (Language $language) => sprintf('%010d|%s', $language->sort_order, $language->title))
            ->values();
        $language_config = config('app.languages', []);

        foreach (self::$active_languages as $language) {
            $language->hreflang = $language_config[$language->code]['hreflang'] ?? $language->code;
        }

        return self::$active_languages;
    }

    /**
     * Every active language with its switch-URL and hreflang value
     * ready. Used by the language-switcher UI and by the <head>
     * alternate-language link tags — so the hreflang string MUST be
     * populated here or crawlers see empty `hreflang=""` attributes.
     */
    public static function generateLanguageLinks()
    {
        $languages = Language::runtimeList()
            ->where('is_active', true)
            ->sortBy(fn (Language $language) => sprintf('%010d|%s', $language->sort_order, $language->title))
            ->values();
        $langConfig = config('app.languages', []);

        foreach ($languages as $lang) {
            try {
                $lang->url = route('locale.switch', ['code' => $lang->code]);
            } catch (\Throwable $e) {
                $lang->url = '#';
            }

            // Prefer config-declared hreflang (e.g. 'tr-TR') → fall
            // back to the bare language code so the attribute never
            // renders empty.
            $lang->hreflang = $langConfig[$lang->code]['hreflang']
                ?? $lang->hreflang
                ?? $lang->code;
        }

        return $languages;
    }
}
