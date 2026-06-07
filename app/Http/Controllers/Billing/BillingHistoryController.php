<?php

namespace App\Http\Controllers\Billing;

use App\Exports\Billing\BillingYearWorkbookExport;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingPeriode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Billings\BillingQueryService;
use App\Services\Billings\BillingReportService;
use Illuminate\Support\Str;
use App\Exports\Billing\BillingSnapshotExport;
use Maatwebsite\Excel\Facades\Excel;

class BillingHistoryController extends Controller
{
    private BillingQueryService $billingQueryService;
    private BillingReportService $billingReportService;

    public function __construct(
        BillingReportService $billingReportService,
        BillingQueryService $billingQueryService
    ) {
        $this->billingQueryService = $billingQueryService;
        $this->billingReportService = $billingReportService;
        $this->middleware('permission:billing-list|billing-create|billing-edit|billing-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:billing-create', ['only' => ['create', 'store', 'create_one', 'store_one']]);
        $this->middleware('permission:billing-edit', ['only' => ['edit', 'update', 'payment', 'unpayment', 'transfer', 'paylater', 'post_paylater']]);
        $this->middleware('permission:billing-delete', ['only' => ['deletePeriode', 'destroyPeriode']]);
    }

    public function index(Request $request)
    {
        $traceId = (string) Str::uuid();
        $paginate = $request->input('perPage', 25);

        // query sudah dipindah ke service
        $billingsQuery = $this->billingQueryService->buildBillingQuery($request, $traceId, true);

        $searchParams = $this->billingQueryService->prepareSearchParams($request);

        // PDF export
        if ($request->cetak_pdf === 'selected') {
            $pdf = $this->billingReportService->generatePdf($billingsQuery, $searchParams, $traceId);
            return $pdf->download("Tagihan_{$searchParams['name']}_{$searchParams['address']}_{$searchParams['status']}_{$searchParams['month']}_{$searchParams['year']}.pdf");
        }

        if ($request->cetak_pdf) {
            $allQuery = Billing::with(['billingSubscription', 'customer']);
            $pdf = $this->billingReportService->generatePdf($allQuery, $searchParams, $traceId);
            return $pdf->download("All_Billing.pdf");
        }

        // total amount
        $totalPrice = $billingsQuery->sum('total_amount');

        $billingsQuery->with(['billingSubscription', 'billingSubscription.billingPeriode']);
        // pagination
        $data = $billingsQuery->paginate($paginate)->appends($request->all());

        // 1. Ambil semua data periode yang dibutuhkan dalam satu query
        $allPeriodes = BillingPeriode::orderBy('id', 'ASC')->get(['id', 'name', 'year']);

        // 2. Format ulang teks nama periode khusus UNTUK TAMPILAN DROPDOWN mengikuti locale aktif
        $periode = $allPeriodes->mapWithKeys(function ($item) {
            // Coba parse kolom 'name' menggunakan Carbon
            // Jika format di DB Anda murni tanggal (Y-m-d) atau nama bulan (e.g., "March 2026")
            try {
                $translatedName = \Carbon\Carbon::parse($item->name)->translatedFormat('F Y');
            } catch (\Exception $e) {
                // Fallback jika format 'name' bukan standar tanggal yang bisa diparse Carbon
                $translatedName = $item->name;
            }

            // Return format array: [ ID_DATABASE => "Nama Bulan Terjemahan" ]
            return [$item->id => $translatedName];
        });

        // 3. Ambil data tahun yang unik (tetap sama seperti optimasi sebelumnya)
        $years = $allPeriodes->whereNotNull('year')
            ->sortByDesc('year')
            ->pluck('year', 'year')
            ->unique()
            ->toArray();

        if (empty($years)) {
            $years = [now()->year => now()->year];
        }

        $filterData = $this->billingReportService->getFilterData($traceId);

        // dd($data);

        return view('billings.billing_histories.index', array_merge(
            [
                'data'          => $data,
                'totalPrice'    => $totalPrice,
                'searchStatus'  => $searchParams['status'],
                'searchName'    => $searchParams['name'],
                'searchAddress' => $searchParams['address'],
                'statuses'      => $this->billingQueryService->getStatuses(),
                'selectedPeriod' => $request->period,
                'periode'       => $periode,
                'years'         => $years
            ],
            $filterData
        ))->with('i', (request()->input('page', 1) - 1) * $paginate);
    }

    // Mengubah nama method agar lebih sesuai
    public function exportYearExport(Request $request)
    {
        $year = $request->get('export_year', now()->year);
        return Excel::download(new BillingYearWorkbookExport($year), 'Billing-' . $year . '.xlsx');
    }

    public function exportSelectedPeriode(Request $request)
    {

        // dd($request->all());
        $timestamp = now()->format('Ymd_His');
        $status  = $request->get('status');
        $periodeId = $request->get('periode_id');

        $periode = BillingPeriode::find($periodeId);

        $statusMap = [
            'LS' => 'PAID',
            'BL' => 'UNPAID',
            'PL' => 'PAYLATER',
            'VD' => 'NOT ACTIVE PERIOD',
        ];

        $statusCamel = 'All';
        if ($status != null) {
            $statusCamel = Str::title(strtolower($statusMap[$status]));
        }

        // dd($status, $statusCamel, $periodeId, $periode?->name, $timestamp);

        return Excel::download(
            new BillingSnapshotExport($status, $periodeId),
            "{$statusCamel}_Billing_{$periode->name}_{$timestamp}.xlsx"
        );
    }
}
