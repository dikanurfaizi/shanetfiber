<?php

namespace App\Http\Controllers;

use App\Models\Billing\BillingPeriode;
use App\Models\Receipt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Websystem;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReceiptsExport;
use Illuminate\Support\Str;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\DB;

//use Barryvdh\DomPDF\PDF;

class ReceiptController extends Controller
{


    public function __construct()
    {
        $this->middleware('permission:billing-list|billing-create|billing-edit|billing-delete', ['only' => ['index', 'show', 'print', 'thermal']]);
    }

    private function replaceArray($search, array $replace, $subject)
    {
        foreach ($replace as $value) {
            $subject = preg_replace('/' . preg_quote($search, '/') . '/', $value, $subject, 1);
        }
        return $subject;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $traceId = (string) Str::uuid();
        $startTime = microtime(true); // 🕒 Mulai timing
        $paginate = 25;

        // === 🔍 Ambil parameter pencarian ===
        $searchName = $request->search_name;
        $searchPeriode = $request->search_month . ' ' . $request->search_year;
        $searchAddress = $request->search_address;
        $paymentMethode = $request->payment_methode;
        $tellerName = $request->teller;

        $accountName = null;
        $bankName = null;
        $accountNumber = null;

        if ($request->account_bank) {
            $accountBank = explode('#', $request->account_bank);
            $accountName = $accountBank[0] ?? null;
            $bankName = $accountBank[1] ?? null;
            $accountNumber = $accountBank[2] ?? null;
        }

        // === 🔧 Tentukan Periode Pencarian ===
        if (empty($request->search_month) && empty($request->search_year)) {
            // Tidak ada input sama sekali → pakai bulan & tahun saat ini
            $searchPeriode = date('M-y');
        } else {
            // Jika salah satu diisi, normalisasi ke format M-y
            $year  = $request->search_year ?: date('Y');
            $month = $request->search_month ?: date('m');
            $searchPeriode = date('M-y', strtotime("{$year}-{$month}-01"));
        }

        // === 🧾 Query utama ===
        $query = Receipt::query()
            ->when($searchName, fn($q) => $q->where('customer_name', 'like', "%{$searchName}%"))
            ->when($searchPeriode, function ($q) use ($searchPeriode) {
                // Kompatibilitas untuk 2 format periode
                $monthShort = date('M-y', strtotime($searchPeriode)); // Oct-25
                $monthFull  = date('F Y', strtotime($searchPeriode)); // October 2025

                $q->where(function ($sub) use ($monthShort, $monthFull) {
                    $sub->where('periode', 'like', "%{$monthShort}%")
                        ->orWhere('periode', 'like', "%{$monthFull}%");
                });
            })
            ->when($searchAddress, fn($q) => $q->where('customer_address', $searchAddress))
            ->when($paymentMethode, fn($q) => $q->where('payment_methode', $paymentMethode))
            ->when($accountName, fn($q) => $q->where('account_name', $accountName))
            ->when($bankName, fn($q) => $q->where('bank_name', $bankName))
            ->when($accountNumber, fn($q) => $q->where('account_number', $accountNumber))
            ->when($tellerName, fn($q) => $q->where('teller_name', $tellerName))
            ->orderBy('created_at', 'ASC');

        // === 🧠 Logging: tampilkan SQL & bindings ===
        // $sql = $this->replaceArray('?', $query->getBindings(), $query->toSql());

        LogHelper::logInfo($request, [
            'trace_id' => $traceId,
            'filters' => [
                'search_name' => $searchName,
                'search_periode' => $searchPeriode,
                'search_address' => $searchAddress,
                'payment_methode' => $paymentMethode,
                'account_bank' => $request->account_bank,
                'teller_name' => $tellerName,
            ],
            // 'raw_sql' => $query->toSql(),
            // 'bound_sql' => $sql,
        ], 200, 'Executing Receipt::index query');

        // === 🔢 Hitung total dan ambil data ===
        $totalPrice = $query->sum('package_price');
        $receipts = $query->paginate($paginate);

        $periods = Receipt::select('periode')
            ->distinct()
            ->orderByRaw("STR_TO_DATE(CONCAT('01 ', periode), '%d %M %Y') DESC")
            ->pluck('periode', 'periode');

        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2);

        // === ✅ Log sukses ===
        LogHelper::logInfo($request, [
            'trace_id' => $traceId,
            'result_count' => $receipts->total(),
            'execution_time_ms' => $duration,
            'total_price_sum' => $totalPrice,
        ], 200, 'Successfully retrieved receipt index', $traceId, $duration);

        return view('receipts.index', compact('receipts', 'totalPrice', 'periods'))
            ->with('i', (request()->input('page', 1) - 1) * $paginate);
    }
    /**
     * Show the form for creating a new resource.
     */

    /**
     * Display the specified resource.
     */
    public function show(Receipt $receipt)
    {
        return view('receipts.show', [
            'receipt' => $receipt,
        ]);
    }

    public function print(Receipt $receipt)
    {
        $websystem = Websystem::where('slug', 'websystem')->first();
        $pdf = PDF::loadView('receipts.print', [
            'receipt' => $receipt,
            'websystem' => $websystem
        ]);
        return $pdf->download($receipt->number . str($receipt->customer_name)->slug() . '_' . str($receipt->customer_address)->slug() . '_' . str($receipt->month)->slug() . '_' . $receipt->year . '.pdf');
    }

    public function thermalPrint(Receipt $receipt)
    {
        $websystem = Websystem::where('slug', 'websystem')->first();

        // Atur ukuran kertas thermal (58mm x 100mm) dalam satuan milimeter
        $pdf = PDF::loadView('receipts.thermal', [
            'receipt' => $receipt,
            'websystem' => $websystem
        ])->setPaper([0, 0, 155.43, 283.5], 'portrait'); // 55mm x 100mm dalam satuan point

        //return $pdf->download($receipt->number . '_thermal_' . str($receipt->customer_name)->slug() . '_' . str($receipt->customer_address)->slug() . '_' . str($receipt->month)->slug() . '_' . $receipt->year . '.pdf');
        return $pdf->stream($receipt->number . '_thermal_' . str($receipt->customer_name)->slug() . '_' . str($receipt->customer_address)->slug() . '_' . str($receipt->month)->slug() . '_' . $receipt->year . '.pdf');
    }

    public function exportAll()
    {
        $traceId = (string) Str::uuid();
        $timestamp = now()->format('Ymd_His');
        $fileName = "Receipts_All_{$timestamp}.xlsx";

        return Excel::download(new ReceiptsExport, $fileName);
    }

    public function exportSelected(Request $request)
    {
        $traceId = (string) Str::uuid();
        $timestamp = now()->format('Ymd_His');

        $period = $request->get('period');
        // dd($period);

        if (!$period) {
            return redirect()->back()->with('error', 'Periode harus dipilih.');
        }

        $fileName = "Receipts_{$period}_{$timestamp}.xlsx";

        // Kirim periode ke ReceiptsExport agar hanya ambil sesuai filter
        return Excel::download(new ReceiptsExport($period), $fileName);
    }
}
