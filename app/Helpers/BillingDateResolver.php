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
         * 💡 PERBAIKAN: Gunakan Carbon::parse() untuk mengonversi gabungan nama bulan dan tahun.
         * Contoh: "1 August 2026" -> Aman di-parse oleh Carbon!
         */
        $billingMonthDate = Carbon::parse("1 {$periode->month} {$periode->year}");

        // Default due date = Tanggal 15 pukul 23:59:59 pada bulan & tahun billing tersebut
        $dueDate = $billingMonthDate->copy()->day(15)->endOfDay();

        // Jika billing dibuat SETELAH tanggal 15 23:59:59 -> jatuh tempo geser ke tanggal 15 bulan depan
        if ($issueDate->gt($dueDate)) {
            $dueDate = $dueDate->addMonth()->day(15)->endOfDay();
        }

        return $dueDate;
    }
}
