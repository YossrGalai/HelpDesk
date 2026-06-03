<?php

namespace App\Enums;

final class TicketStatus
{
    public const OPEN = 'OPEN';

    public const IN_PROGRESS = 'IN_PROGRESS';

    public const CLOSED = 'CLOSED';

    public static function values(): array
    {
        return [
            self::OPEN,
            self::IN_PROGRESS,
            self::CLOSED,
        ];
    }
}
