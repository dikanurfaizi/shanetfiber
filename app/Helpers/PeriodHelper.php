<?php

namespace App\Helpers;

use Carbon\Carbon;
use InvalidArgumentException;

class PeriodHelper
{
    /**
     * Generate selectable billing periods.
     * Example output:
     * [
     *   ['name' => '2025-12', 'label' => 'December 2025'],
     *   ['name' => '2026-01', 'label' => 'January 2026'],
     * ]
     */
    public static function generate(
        int $monthsAhead = 3,
        ?Carbon $start = null
    ): array {
        $start = $start ? $start->copy() : now()->startOfMonth();

        $periods = [];

        for ($i = 0; $i <= $monthsAhead; $i++) {
            $date = $start->copy()->addMonths($i);

            $periods[] = [
                'name'  => $date->format('Y-m'),
                'label' => $date->translatedFormat('F Y'),
            ];
        }

        return $periods;
    }

    /**
     * Convert YYYY-MM into end of month Carbon.
     */
    public static function endOfPeriod(string $period): Carbon
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $period)) {
            throw new InvalidArgumentException("Invalid period format: {$period}");
        }

        return Carbon::createFromFormat('Y-m', $period)
            ->endOfMonth();
    }
}
