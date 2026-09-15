<?php

namespace App\Constants;

class UserRolesConstant
{
    const USER = 'user';
    const ADMIN = 'admin';
    const AGENT = 'agent';

    public static function getAllRoles(): array
    {
        return [
            self::USER,
            self::ADMIN,
            self::AGENT,
        ];
    }
}