<?php

namespace App\Services\Reports;

use App\Services\Reports\ReportQueryData;

class ReportService
{
    protected ReportQueryData $query;

    public function __construct(ReportQueryData $query)
    {
        $this->query = $query;
    }

    /**
     * Ambil laporan bulanan untuk satu user.
     * 
     * Akan mengambil semua periode dari Januari tahun berjalan sampai bulan ini.
     */
    public function monthly(int $userId)
    {
        // Ambil daftar periode aktif (misal Jan s.d. bulan sekarang)
        $periodes = $this->query->getActivePeriodes();

        // Generate report per periode
        $reports = $periodes->map(
            fn($periode) =>
            $this->query->getBillingReport($userId, $periode)
        );

        return $reports;
    }
}
