<?php

namespace App\Helpers;

use Ramsey\Uuid\Type\Integer;

class OltHelper
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE   = 1;
    const STATUS_SCANNING = 2;

    const STRING_STATUS_INACTIVE = 'INACTIVE';
    const STRING_STATUS_ACTIVE   = 'ACTIVE';
    const STRING_STATUS_SCANNING = 'SCANNING';

    public static function getDisplayStatus($status): string
    {
        return match ($status) {
            self::STATUS_ACTIVE => self::STRING_STATUS_ACTIVE,   // Up
            self::STATUS_INACTIVE => self::STRING_STATUS_INACTIVE, // Down
            default => self::STRING_STATUS_SCANNING,
        };
    }

    public static function getStatusValue($status)
    {
        return match ($status) {
            self::STRING_STATUS_INACTIVE => self::STATUS_INACTIVE,
            self::STRING_STATUS_ACTIVE => self::STATUS_ACTIVE,
            self::STRING_STATUS_SCANNING => self::STATUS_SCANNING,
        };
    }
}
