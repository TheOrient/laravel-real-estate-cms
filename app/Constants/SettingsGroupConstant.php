<?php

namespace App\Constants;

class SettingsGroupConstant
{
    const GENERAL = 'general';
    const FOOTER = 'footer';
    const SOCIAL = 'social';
    const MOBILE = 'mobile';
    const CONTACT = 'contact';
    const SYSTEM = 'system';
    const API = 'api';
    const SECURITY = 'security';
    const SCRIPTS = 'scripts';
    const EMAIL = 'email';
    const OPENAI = 'openai';

    /**
     * Get all available groups
     *
     * @return array
     */
    public static function getAllGroups(): array
    {
        return [
            self::GENERAL,
            self::FOOTER,
            self::SOCIAL,
            self::MOBILE,
            self::CONTACT,
            self::SYSTEM,
            self::API,
            self::SECURITY,
            self::SCRIPTS,
            self::EMAIL,
            self::OPENAI,
        ];
    }
}
