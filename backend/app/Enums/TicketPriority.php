<?php

namespace App\Enums;

final class TicketPriority
{
    public const LOW = 'LOW';

    public const MEDIUM = 'MEDIUM';

    public const HIGH = 'HIGH';

    public const CRITICAL = 'CRITICAL';

    public static function values(): array
    {
        return [
            self::LOW,
            self::MEDIUM,
            self::HIGH,
            self::CRITICAL,
        ];
    }
}
