<?php

namespace App\Enums;

final class UserRole
{
    const ADMIN = 'admin';
    const AGENT = 'agent';
    const USER  = 'user';

    public static function values(): array
    {
        return [self::ADMIN, self::AGENT, self::USER];
    }
}
