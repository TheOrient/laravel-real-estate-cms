<?php

namespace App\Constants;

class SettingsTypeConstant
{
    const TEXT = 'text';
    const TEXTAREA = 'textarea';
    const IMAGE = 'image';
    const NUMBER = 'number';
    const EMAIL = 'email';
    const PASSWORD = 'password';
    const BOOLEAN = 'boolean';
    const SELECT = 'select';
    const CODE = 'code';

    /**
     * Get all available types
     *
     * @return array
     */
    public static function getAllTypes(): array
    {
        return [
            self::TEXT,
            self::TEXTAREA,
            self::IMAGE,
            self::NUMBER,
            self::EMAIL,
            self::PASSWORD,
            self::BOOLEAN,
            self::SELECT,
            self::CODE,
        ];
    }
}
