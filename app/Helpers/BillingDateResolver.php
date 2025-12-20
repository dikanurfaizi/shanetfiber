<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\Billings\BillingPeriode;

class BillingDateResolver
{
    public function resolveDueDate(BillingPeriode $periode, ?Carbon $issueDate = null): Carbon
    {
        $issueDate = $issueDate ?? now();

        /**
         * Convert month name + year → Carbon date
         * contoh: "December 2025"
         */
        $billingMonthDate = Carbon::parse(
            $periode->month . ' ' . $periode->year
        );

        // default due date = tanggal 15 di bulan billing
        $dueDate = $billingMonthDate->copy()->day(15);

        // kalau billing dibuat SETELAH tanggal 15 → jatuh tempo bulan depan
        if ($issueDate->gt($dueDate)) {
            $dueDate = $dueDate->addMonth()->day(15);
        }

        return $dueDate->startOfDay();
    }
}
