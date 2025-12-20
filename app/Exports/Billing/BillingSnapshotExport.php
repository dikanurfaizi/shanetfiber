<?php

namespace App\Exports\Billing;

use App\Exports\Billing\Sheets\BillingPeriodeSheet;
use App\Models\Billings\BillingPeriode;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BillingSnapshotExport implements WithMultipleSheets
{
    public function __construct(
        protected ?string $status,
        protected ?int $periodeId
    ) {}

    public function sheets(): array
    {
        // CASE A: user pilih 1 periode
        if ($this->periodeId) {
            $periode = BillingPeriode::findOrFail($this->periodeId);

            return [
                new BillingPeriodeSheet(
                    periodeId: $periode->id,
                    periodeName: $periode->name
                )
            ];
        }

        // CASE B: export semua periode (misal tahun berjalan)
        // return BillingPeriode::query()
        //     ->whereYear('start_date', now()->year)
        //     ->get()
        //     ->map(
        //         fn($periode) =>
        //         new BillingPeriodeSheet(
        //             periodeId: $periode->id,
        //             periodeName: $periode->name
        //         )
        //     )
        //     ->toArray();
        return BillingPeriode::query()
            ->whereMonth('start_date', now()->month())
            ->get()
            ->map(fn($periode) =>
            new BillingPeriodeSheet(
                periodeId: $periode->id,
                periodeName: $periode->name
            ))->toArray();
    }
}
