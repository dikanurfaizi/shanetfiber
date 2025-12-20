<?php

namespace App\Services\Receipts;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ReferenceIdGeneratorService
{
    public static function generate(string $prefix = 'REF'): string
    {
        return sprintf(
            '%s-%s-%s',
            strtoupper($prefix),
            Carbon::now()->format('Ymd'),
            strtoupper(Str::random(8))
        );
    }
}
