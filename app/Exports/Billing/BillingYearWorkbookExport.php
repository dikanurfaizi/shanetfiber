<?php

namespace App\Exports\Billing;

use App\Models\Billings\BillingPeriode;
use App\Exports\Billing\Sheets\BillingPeriodeSheet;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BillingYearWorkbookExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $year = now()->year;

        $periodes = BillingPeriode::query()
            ->whereYear('start_date', $year)
            ->orderBy('start_date')
            ->get(['id', 'name']);

        $sheets = [];

        foreach ($periodes as $periode) {
            $sheets[] = new BillingPeriodeSheet(
                periodeId: $periode->id,
                periodeName: $periode->name
            );
        }

        return $sheets;
    }
}
