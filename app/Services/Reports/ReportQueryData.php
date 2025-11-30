<?php

namespace App\Services\Reports;

use App\Models\Mikrotik;
use App\Models\Billing\BillingPeriode;
use App\Models\Expenses\Expense;
use Illuminate\Support\Carbon;

class ReportQueryData
{
    /**
     * Ambil periode bulan ini.
     * Contoh hasil: "October 2025"
     */
    public function getCurrentPeriode()
    {
        return BillingPeriode::query()
            ->whereMonth('start_date', now()->month)
            ->whereYear('start_date', now()->year)
            ->value('name');
    }

    /**
     * Ambil periode spesifik berdasarkan bulan & tahun tertentu.
     * @param int $month (1-12)
     * @param int|null $year default tahun sekarang
     */
    public function getPeriodeByMonth(int $month, ?int $year = null)
    {
        $year = $year ?? now()->year;

        return BillingPeriode::query()
            ->whereMonth('start_date', $month)
            ->whereYear('start_date', $year)
            ->value('name');
    }

    /**
     * Ambil semua periode dari Januari tahun berjalan hingga bulan ini.
     * Contoh hasil: ["January 2025", "February 2025", ..., "October 2025"]
     */
    public function getActivePeriodes()
    {
        $start = Carbon::create(now()->year, 1, 1)->startOfMonth();
        $end = Carbon::now()->startOfMonth();

        return BillingPeriode::query()
            ->whereBetween('start_date', [$start, $end])
            ->orderBy('start_date')
            ->pluck('name');
    }

    /**
     * Ambil semua periode dari Januari tahun tertentu hingga Desember tahun itu.
     * @param int $year
     */
    public function getPeriodesByYear(int $year)
    {
        $start = Carbon::create($year, 1, 1)->startOfMonth();
        $end = Carbon::create($year, 12, 31)->endOfMonth();

        return BillingPeriode::query()
            ->whereBetween('start_date', [$start, $end])
            ->orderBy('start_date')
            ->pluck('name');
    }

    /**
     * Ambil semua periode aktif dalam range bebas (tanggal custom)
     * Misal untuk kebutuhan analisis triwulan, semester, dsb.
     * @param string|Carbon $start
     * @param string|Carbon $end
     */
    public function getPeriodesBetween($start, $end)
    {
        $start = Carbon::parse($start)->startOfMonth();
        $end = Carbon::parse($end)->endOfMonth();

        return BillingPeriode::query()
            ->whereBetween('start_date', [$start, $end])
            ->orderBy('start_date')
            ->pluck('name');
    }

    /**
     * Ambil laporan billing untuk user tertentu.
     *
     * @param  int $userId
     * @param  string|null $periodeName
     * @return array
     */
    public function getBillingReport(int $userId, ?string $periodeName = null)
    {
        // Default ke bulan ini kalau tidak dikirim
        $periodeName = $periodeName ?? BillingPeriode::query()
            ->whereMonth('start_date', now()->month)
            ->whereYear('start_date', now()->year)
            ->value('name');

        $mikrotiks = Mikrotik::with(['customers' => function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->with([
                    'package',
                    'billings.billingSubscription.billingPeriode'
                ]);
        }, 'customers.user'])->get();

        $report = $mikrotiks->flatMap(function ($mikrotik) use ($periodeName) {
            return $mikrotik->customers
                ->groupBy('package_id')
                ->map(function ($customers, $packageId) use ($periodeName, $mikrotik) {
                    $pkg  = $customers->first()->package;
                    $user = $customers->first()->user;

                    $billings = $customers->flatMap(
                        fn($c) =>
                        $c->billings->filter(
                            fn($b) =>
                            optional($b->billingSubscription->billingPeriode)->name === $periodeName
                        )
                    );

                    return [
                        'mikrotik'       => $mikrotik->name,
                        'package'        => $pkg->name,
                        'price'          => $pkg->price,
                        'responsibility' => $user->name,
                        'total_cust'     => $customers->count(),

                        'bill'           => $billings->sum('total_amount'),
                        'bill_cust'      => $billings->unique('customer_id')->count(),

                        'payment'        => $billings->where('status', 'LS')->sum('total_amount'),
                        'payment_cust'   => $billings->where('status', 'LS')->unique('customer_id')->count(),

                        'unpayment'      => $billings->whereIn('status', ['BL', 'NP'])->sum('total_amount'),
                        'unpayment_cust' => $billings->whereIn('status', ['BL', 'NP'])->unique('customer_id')->count(),

                        'paylater'       => $billings->where('status', 'PL')->sum('total_amount'),
                        'paylater_cust'  => $billings->where('status', 'PL')->unique('customer_id')->count(),

                        'subs_cust'      => $customers->whereNull('deleted_at')->count(),
                        'subs_nominal'   => $pkg->price * $customers->whereNull('deleted_at')->count(),

                        'unsubs_cust'    => $customers->whereNotNull('deleted_at')->count(),
                        'unsubs_nominal' => $pkg->price * $customers->whereNotNull('deleted_at')->count(),
                    ];
                });
        })->values();

        // Hitung total dan estimate
        $totalCust          = $report->sum('total_cust');
        $totalSubsNominal   = $report->sum('subs_nominal');
        $totalUnsubsNominal = $report->sum('unsubs_nominal');
        $totalBill          = $report->sum('bill');
        $estimateBill       = $totalBill - $totalUnsubsNominal;

        return [
            'periode' => $periodeName,
            'rows' => $report,
            'footer' => [
                'total_cust' => $totalCust,
                'subs_nominal' => $totalSubsNominal,
                'estimate_bill' => [
                    'cust' => $totalCust,
                    'nominal' => $estimateBill
                ]
            ]
        ];
    }

    public function getMonthlyReportForMikrotik(int $userId, int $mikrotikId, ?string $periodeName = null)
    {
        // kalau tidak dikirim, pakai bulan sekarang
        $periodeName = $periodeName ?? Carbon::now()->format('F Y');

        // ambil expenses bulan ini untuk user
        $expensesTotal = Expense::where('user_id', $userId)
            ->whereRaw("DATE_FORMAT(expense_date, '%M %Y') = ?", [$periodeName])
            ->sum('amount');

        // ambil mikrotik + relasi billing
        $mikrotik = Mikrotik::with([
            'customers.billings' => function ($q) use ($periodeName) {
                $q->whereHas('subscriptionPeriode.periode', function ($sq) use ($periodeName) {
                    $sq->where('name', $periodeName);
                });
            },
            'customers' => fn($q) => $q->withTrashed(),
        ])
            ->where('id', $mikrotikId)
            ->first();

        if (!$mikrotik) {
            return null;
        }

        // flatten semua billing yang sesuai periode
        $billings = $mikrotik->customers->flatMap(fn($c) => $c->billings);

        $result = [
            'mikrotik_id'   => $mikrotik->id,
            'mikrotik_name' => $mikrotik->name,
            'periode_name'  => $periodeName,
            'cust_active'   => $mikrotik->customers->count(),
            'bill_total'    => $billings->sum('total_amount'),
            'paid_total'    => $billings->where('status', 'LS')->sum('total_amount'),
            'unpaid_total'  => $billings->whereIn('status', ['BL', 'NP'])->sum('total_amount'),
            'paylater_total' => $billings->where('status', 'PL')->sum('total_amount'),
            'subs_cust'     => $mikrotik->customers->whereNull('deleted_at')->count(),
            'unsub_cust'    => $mikrotik->customers->whereNotNull('deleted_at')->count(),
            'expenses'      => $expensesTotal,
        ];

        $result['net_income'] = $result['paid_total'] - $result['expenses'];

        return collect($result);
    }
}
