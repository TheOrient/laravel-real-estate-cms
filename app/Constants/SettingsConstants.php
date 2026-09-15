<?php

namespace App\Constants;

class SettingsConstants
{
    /**
     * Get all default settings data
     */
    public static function getDefaultSettings(): array
    {
        return [
            // General Settings
            [
                'key' => 'site_title',
                'value' => 'Open-Source Real Estate CMS',
                'type' => SettingsTypeConstant::TEXT,
                'group' => SettingsGroupConstant::GENERAL,
                'description' => 'Site Başlığı',
                'sort_order' => 1,
            ],
            [
                'key' => 'site_url',
                'value' => '',
                'type' => SettingsTypeConstant::TEXT,
                'group' => SettingsGroupConstant::GENERAL,
                'description' => 'Site URL',
                'sort_order' => 2,
            ],
            [
                'key' => 'site_description',
                'value' => 'Gayrimenkul portföyü ve kurumsal web sitesi',
                'type' => SettingsTypeConstant::TEXTAREA,
                'group' => SettingsGroupConstant::GENERAL,
                'description' => 'Site Açıklaması',
                'sort_order' => 2,
            ],
            [
                'key' => 'site_logo',
                'value' => null,
                'type' => SettingsTypeConstant::IMAGE,
                'group' => SettingsGroupConstant::GENERAL,
                'description' => 'Site Logosu',
                'sort_order' => 3,
            ],

            // Home Hero Settings
            [
                'key' => 'home_hero_image',
                // Varsayılan olarak public/uploads/settings/home-hero.jpg kullanılır
                'value' => 'uploads/settings/home-hero.jpg',
                'type' => SettingsTypeConstant::IMAGE,
                'group' => SettingsGroupConstant::GENERAL,
                'description' => 'Ana sayfa hero arkaplan görseli',
                'sort_order' => 4,
            ],

            // Language Switcher Settings
            [
                'key' => 'language_switcher_display',
                'value' => 'inline',
                'type' => SettingsTypeConstant::SELECT,
                'group' => SettingsGroupConstant::GENERAL,
                'description' => 'Dil seçici görünümü (yan yana veya açılır menü)',
                'options' => 'inline,dropdown',
                'sort_order' => 5,
            ],

            // Footer Settings
            [
                'key' => 'footer_copyright_text',
                'value' => 'Tüm hakları saklıdır.',
                'type' => SettingsTypeConstant::TEXT,
                'group' => SettingsGroupConstant::FOOTER,
                'description' => 'Footer Copyright Metni',
                'sort_order' => 10,
            ],
            // Social Media Links
            [
                'key' => 'social_facebook_url',
                'value' => '',
                'type' => SettingsTypeConstant::TEXT,
                'group' => SettingsGroupConstant::SOCIAL,
                'description' => 'Facebook Sayfası URL',
                'sort_order' => 20,
            ],
            [
                'key' => 'social_instagram_url',
                'value' => '',
                'type' => SettingsTypeConstant::TEXT,
                'group' => SettingsGroupConstant::SOCIAL,
                'description' => 'Instagram Profili URL',
                'sort_order' => 22,
            ],
            [
                'key' => 'social_youtube_url',
                'value' => '',
                'type' => SettingsTypeConstant::TEXT,
                'group' => SettingsGroupConstant::SOCIAL,
                'description' => 'YouTube Kanalı URL',
                'sort_order' => 23,
            ],

            // Contact Information
            [
                'key' => 'contact_email',
                'value' => '',
                'type' => SettingsTypeConstant::EMAIL,
                'group' => SettingsGroupConstant::CONTACT,
                'description' => 'İletişim E-posta',
                'sort_order' => 40,
            ],
            [
                'key' => 'contact_phone',
                'value' => '',
                'type' => SettingsTypeConstant::TEXT,
                'group' => SettingsGroupConstant::CONTACT,
                'description' => 'İletişim Telefonu',
                'sort_order' => 41,
            ],
            [
                'key' => 'contact_address',
                'value' => 'Kuşadası / Aydın (Demo)',
                'type' => SettingsTypeConstant::TEXTAREA,
                'group' => SettingsGroupConstant::CONTACT,
                'description' => 'İletişim Adresi',
                'sort_order' => 42,
            ],
            [
                'key' => 'notification_email',
                'value' => '',
                'type' => SettingsTypeConstant::EMAIL,
                'group' => SettingsGroupConstant::CONTACT,
                'description' => 'Bildirim E-postası (sitede görünmez)',
                'sort_order' => 43,
            ],
            [
                'key' => 'legal_company_title',
                'value' => '',
                'type' => SettingsTypeConstant::TEXT,
                'group' => SettingsGroupConstant::CONTACT,
                'description' => 'Yasal İşletme Unvanı',
                'sort_order' => 44,
            ],
            [
                'key' => 'legal_authorization_no',
                'value' => '',
                'type' => SettingsTypeConstant::TEXT,
                'group' => SettingsGroupConstant::CONTACT,
                'description' => 'Taşınmaz Ticareti Yetki Belgesi No',
                'sort_order' => 45,
            ],

            // System Settings
            [
                'key' => 'listing_approval_required',
                'value' => '0',
                'type' => SettingsTypeConstant::BOOLEAN,
                'group' => SettingsGroupConstant::SYSTEM,
                'description' => 'Danışman ilanları için yönetici onayı',
                'sort_order' => 60,
            ],

            // Security & CAPTCHA Settings
            [
                'key' => 'captcha_provider',
                'value' => 'none',
                'type' => SettingsTypeConstant::SELECT,
                'group' => SettingsGroupConstant::SECURITY,
                'description' => 'CAPTCHA Sağlayıcı',
                'options' => 'none,recaptcha_v2,recaptcha_v2_invisible,recaptcha_v3,turnstile',
                'sort_order' => 100,
            ],
            [
                'key' => 'captcha_enabled_contact',
                'value' => '1',
                'type' => SettingsTypeConstant::BOOLEAN,
                'group' => SettingsGroupConstant::SECURITY,
                'description' => 'İletişim Formunda CAPTCHA',
                'sort_order' => 101,
            ],
            [
                'key' => 'captcha_enabled_login',
                'value' => '0',
                'type' => SettingsTypeConstant::BOOLEAN,
                'group' => SettingsGroupConstant::SECURITY,
                'description' => 'Giriş Formunda CAPTCHA',
                'sort_order' => 103,
            ],
            [
                'key' => 'captcha_enabled_forgot_password',
                'value' => '0',
                'type' => SettingsTypeConstant::BOOLEAN,
                'group' => SettingsGroupConstant::SECURITY,
                'description' => 'Şifre Sıfırlama Formunda CAPTCHA',
                'sort_order' => 104,
            ],

            // reCAPTCHA v2 Settings
            [
                'key' => 'recaptcha_v2_site_key',
                'value' => '',
                'type' => SettingsTypeConstant::TEXT,
                'group' => SettingsGroupConstant::SECURITY,
                'description' => 'reCAPTCHA v2 Site Key',
                'sort_order' => 110,
            ],
            [
                'key' => 'recaptcha_v2_secret_key',
                'value' => '',
                'type' => SettingsTypeConstant::PASSWORD,
                'group' => SettingsGroupConstant::SECURITY,
                'description' => 'reCAPTCHA v2 Secret Key',
                'sort_order' => 111,
                'is_encrypted' => true,
            ],

            // reCAPTCHA v3 Settings
            [
                'key' => 'recaptcha_v3_site_key',
                'value' => '',
                'type' => SettingsTypeConstant::TEXT,
                'group' => SettingsGroupConstant::SECURITY,
                'description' => 'reCAPTCHA v3 Site Key',
                'sort_order' => 120,
            ],
            [
                'key' => 'recaptcha_v3_secret_key',
                'value' => '',
                'type' => SettingsTypeConstant::PASSWORD,
                'group' => SettingsGroupConstant::SECURITY,
                'description' => 'reCAPTCHA v3 Secret Key',
                'sort_order' => 121,
                'is_encrypted' => true,
            ],
            [
                'key' => 'recaptcha_v3_score_threshold',
                'value' => '0.5',
                'type' => SettingsTypeConstant::NUMBER,
                'group' => SettingsGroupConstant::SECURITY,
                'description' => 'reCAPTCHA v3 Skor Eşiği (0.0-1.0)',
                'sort_order' => 122,
            ],

            // Cloudflare Turnstile Settings
            [
                'key' => 'turnstile_site_key',
                'value' => '',
                'type' => SettingsTypeConstant::TEXT,
                'group' => SettingsGroupConstant::SECURITY,
                'description' => 'Cloudflare Turnstile Site Key',
                'sort_order' => 130,
            ],
            [
                'key' => 'turnstile_secret_key',
                'value' => '',
                'type' => SettingsTypeConstant::PASSWORD,
                'group' => SettingsGroupConstant::SECURITY,
                'description' => 'Cloudflare Turnstile Secret Key',
                'sort_order' => 131,
                'is_encrypted' => true,
            ],

            // Custom Scripts
            [
                'key' => 'head_scripts',
                'value' => null,
                'type' => SettingsTypeConstant::CODE,
                'group' => SettingsGroupConstant::SCRIPTS,
                'description' => 'Head Scriptleri (</head> etiketinden önce)',
                'sort_order' => 50,
            ],
            [
                'key' => 'footer_scripts',
                'value' => null,
                'type' => SettingsTypeConstant::CODE,
                'group' => SettingsGroupConstant::SCRIPTS,
                'description' => 'Footer Scriptleri (</body> etiketinden önce)',
                'sort_order' => 51,
            ],

            // SMTP / Email Settings
            [
                'key' => 'smtp_host',
                'value' => '',
                'type' => SettingsTypeConstant::TEXT,
                'group' => SettingsGroupConstant::EMAIL,
                'description' => 'SMTP Host (boşsa MAIL_HOST .env kullanılır)',
                'sort_order' => 200,
                'is_encrypted' => false,
            ],
            [
                'key' => 'smtp_port',
                'value' => '587',
                'type' => SettingsTypeConstant::NUMBER,
                'group' => SettingsGroupConstant::EMAIL,
                'description' => 'SMTP Port',
                'sort_order' => 201,
                'is_encrypted' => false,
            ],
            [
                'key' => 'smtp_username',
                'value' => '',
                'type' => SettingsTypeConstant::TEXT,
                'group' => SettingsGroupConstant::EMAIL,
                'description' => 'SMTP Kullanıcı Adı',
                'sort_order' => 202,
                'is_encrypted' => false,
            ],
            [
                'key' => 'smtp_password',
                'value' => '',
                'type' => SettingsTypeConstant::PASSWORD,
                'group' => SettingsGroupConstant::EMAIL,
                'description' => 'SMTP Şifre',
                'sort_order' => 203,
                'is_encrypted' => true,
            ],
            [
                'key' => 'smtp_encryption',
                'value' => 'tls',
                'type' => SettingsTypeConstant::SELECT,
                'group' => SettingsGroupConstant::EMAIL,
                'description' => 'SMTP Şifreleme',
                'options' => 'tls,ssl,none',
                'sort_order' => 204,
                'is_encrypted' => false,
            ],
            [
                'key' => 'smtp_from_address',
                'value' => '',
                'type' => SettingsTypeConstant::EMAIL,
                'group' => SettingsGroupConstant::EMAIL,
                'description' => 'Gönderen E-posta Adresi',
                'sort_order' => 205,
                'is_encrypted' => false,
            ],
            [
                'key' => 'smtp_from_name',
                'value' => '',
                'type' => SettingsTypeConstant::TEXT,
                'group' => SettingsGroupConstant::EMAIL,
                'description' => 'Gönderen İsmi',
                'sort_order' => 206,
                'is_encrypted' => false,
            ],

            // OpenAI Api key
            [
                'key' => 'openai_api_key',
                'value' => '',
                'type' => SettingsTypeConstant::TEXT,
                'group' => SettingsGroupConstant::OPENAI,
                'description' => 'OpenAI Api Key',
                'sort_order' => 207,
                'is_encrypted' => true,
            ],
            [
                'key' => 'openai_model',
                'value' => 'gpt-4o-mini',
                'type' => SettingsTypeConstant::TEXT,
                'group' => SettingsGroupConstant::OPENAI,
                'description' => 'OpenAI Model',
                'sort_order' => 208,
                'is_encrypted' => false,
            ],
            [
                'key' => 'translation_enabled',
                'value' => '1',
                'type' => SettingsTypeConstant::BOOLEAN,
                'group' => SettingsGroupConstant::OPENAI,
                'description' => 'Otomatik Çeviri Özelliği (Admin için)',
                'sort_order' => 209,
                'is_encrypted' => false,
            ],
            [
                'key' => 'translation_enabled_for_users',
                'value' => '0',
                'type' => SettingsTypeConstant::BOOLEAN,
                'group' => SettingsGroupConstant::OPENAI,
                'description' => 'Otomatik Çeviri Özelliği (Kullanıcılar için)',
                'sort_order' => 210,
                'is_encrypted' => false,
            ],

            // ---- AI provider selection (Task #9) ----
            [
                'key' => 'ai_provider',
                'value' => 'groq',
                'type' => SettingsTypeConstant::SELECT,
                'group' => SettingsGroupConstant::OPENAI,
                'description' => 'AI Sağlayıcı (groq/openai/anthropic)',
                'options' => 'groq,openai,anthropic',
                'sort_order' => 211,
                'is_encrypted' => false,
            ],
            [
                'key' => 'groq_api_key',
                'value' => '',
                'type' => SettingsTypeConstant::TEXT,
                'group' => SettingsGroupConstant::OPENAI,
                'description' => 'Groq API Key (Ücretsiz, kart gerekmez. https://console.groq.com/keys)',
                'sort_order' => 214,
                'is_encrypted' => true,
            ],
            [
                'key' => 'groq_model',
                'value' => 'openai/gpt-oss-20b',
                'type' => SettingsTypeConstant::TEXT,
                'group' => SettingsGroupConstant::OPENAI,
                'description' => 'Groq Model',
                'sort_order' => 215,
                'is_encrypted' => false,
            ],
            [
                'key' => 'chatbot_enabled',
                'value' => '1',
                'type' => SettingsTypeConstant::BOOLEAN,
                'group' => SettingsGroupConstant::OPENAI,
                'description' => 'Sitede chat asistanını göster',
                'sort_order' => 216,
                'is_encrypted' => false,
            ],
            [
                'key' => 'anthropic_api_key',
                'value' => '',
                'type' => SettingsTypeConstant::TEXT,
                'group' => SettingsGroupConstant::OPENAI,
                'description' => 'Anthropic API Key',
                'sort_order' => 212,
                'is_encrypted' => true,
            ],
            [
                'key' => 'anthropic_model',
                'value' => 'claude-3-5-sonnet-latest',
                'type' => SettingsTypeConstant::TEXT,
                'group' => SettingsGroupConstant::OPENAI,
                'description' => 'Anthropic Model',
                'sort_order' => 213,
                'is_encrypted' => false,
            ],

            // ---- Meta Graph API / Social media auto-post (Task #10) ----
            [
                'key' => 'meta_page_id',
                'value' => '',
                'type' => SettingsTypeConstant::TEXT,
                'group' => SettingsGroupConstant::SOCIAL,
                'description' => 'Facebook Page ID',
                'sort_order' => 220,
                'is_encrypted' => false,
            ],
            [
                'key' => 'meta_page_token',
                'value' => '',
                'type' => SettingsTypeConstant::TEXT,
                'group' => SettingsGroupConstant::SOCIAL,
                'description' => 'Facebook Page Access Token',
                'sort_order' => 221,
                'is_encrypted' => true,
            ],
            [
                'key' => 'meta_ig_user_id',
                'value' => '',
                'type' => SettingsTypeConstant::TEXT,
                'group' => SettingsGroupConstant::SOCIAL,
                'description' => 'Instagram Business User ID',
                'sort_order' => 222,
                'is_encrypted' => false,
            ],
            [
                'key' => 'social_autopost_enabled',
                'value' => '0',
                'type' => SettingsTypeConstant::BOOLEAN,
                'group' => SettingsGroupConstant::SOCIAL,
                'description' => 'Yeni ilan yayınlandığında otomatik paylaş',
                'sort_order' => 223,
                'is_encrypted' => false,
            ],

            // ---- PDF flyer (Task #12) ----
            [
                'key' => 'flyer_default_theme',
                'value' => 'orange',
                'type' => SettingsTypeConstant::SELECT,
                'group' => SettingsGroupConstant::GENERAL,
                'description' => 'Varsayılan Flyer Teması',
                'options' => 'orange,blue,green,red,purple,gold',
                'sort_order' => 230,
                'is_encrypted' => false,
            ],
            [
                'key' => 'flyer_default_orientation',
                'value' => 'portrait',
                'type' => SettingsTypeConstant::SELECT,
                'group' => SettingsGroupConstant::GENERAL,
                'description' => 'Varsayılan Flyer Yönü',
                'options' => 'portrait,landscape',
                'sort_order' => 231,
                'is_encrypted' => false,
            ],

        ];
    }

    /**
     * Get setting definition by key
     */
    public static function getSettingByKey(string $key): ?array
    {
        $settings = self::getDefaultSettings();
        foreach ($settings as $setting) {
            if ($setting['key'] === $key) {
                return $setting;
            }
        }

        return null;
    }

    /**
     * Get type for a setting key
     */
    public static function getType(string $key): ?string
    {
        $setting = self::getSettingByKey($key);

        return $setting['type'] ?? null;
    }

    /**
     * Get group for a setting key
     */
    public static function getGroup(string $key): ?string
    {
        $setting = self::getSettingByKey($key);

        return $setting['group'] ?? null;
    }

    /**
     * Get description for a setting key
     */
    public static function getDescription(string $key): ?string
    {
        $setting = self::getSettingByKey($key);

        return $setting['description'] ?? null;
    }

    /**
     * Get options for a setting key
     */
    public static function getOptions(string $key): ?string
    {
        $setting = self::getSettingByKey($key);

        return $setting['options'] ?? null;
    }

    /**
     * Get all groups from settings
     */
    public static function getAllGroups(): array
    {
        $settings = self::getDefaultSettings();
        $groups = [];
        foreach ($settings as $setting) {
            if (! in_array($setting['group'], $groups)) {
                $groups[] = $setting['group'];
            }
        }
        sort($groups);

        return $groups;
    }
}
