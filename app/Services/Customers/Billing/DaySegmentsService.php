<?php

namespace App\Services\Customers\Billing;

use App\Models\Billings\BillingPeriode;
use Carbon\Carbon;
use DomainException;

class DaySegmentsService
{
    public function calculate(
        BillingPeriode $periode,
        Carbon $changeDate
    ): array {
        $totalDays = $periode->start_date
            ->diffInDays($periode->end_date) + 1;

        $daysOld = $periode->start_date
            ->diffInDays($changeDate);

        $daysNew = $totalDays - $daysOld;

        if ($daysOld < 0 || $daysNew <= 0) {
            throw new DomainException('Invalid prorate day calculation');
        }

        return [
            'total' => $totalDays,
            'old'   => $daysOld,
            'new'   => $daysNew,
        ];
    }
}
