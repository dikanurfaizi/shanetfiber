<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Websystem;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReceiptsExport;
use App\Helpers\ExecutionTimer;
use Illuminate\Support\Str;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\Crypt;

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
        $timer = new ExecutionTimer();
        $paginate = 25;

        // === 🔍 Ambil parameter pencarian bawaan ===
        $searchName = $request->search_name;
        $searchPeriode = $request->search_month . ' ' . $request->search_year;
        $searchAddress = $request->search_address;
        $paymentMethode = $request->payment_methode;
        $tellerName = $request->teller;

        // === 📥 Ambil parameter FILTER BARU (Dropdown & Date) ===
        $filterCustomer = $request->get('filter_customer');
        $filterTeller = $request->get('filter_teller');
        $filterPaymentMethod = $request->get('filter_payment_method');
        $filterDate = $request->get('filter_date');

        $accountName = null;
        $bankName = null;
        $accountNumber = null;

        if ($request->account_bank) {
            $accountBank = explode('#', $request->account_bank);
            $accountName = $accountBank[0] ?? null;
            $bankName = $accountBank[1] ?? null;
            $accountNumber = $accountBank[2] ?? null;
        }

        // === 🔍 Deteksi Apakah User Sedang Melakukan Pencarian/Filter Aktif ===
        $isFiltering = $searchName
            || $searchAddress
            || $paymentMethode
            || $tellerName
            || $filterCustomer
            || $filterTeller
            || $filterPaymentMethod
            || $filterDate
            || $request->account_bank
            || $request->filled('search_month')
            || $request->filled('search_year');

        // === 🔧 Tentukan Periode Pencarian ===
        $searchPeriode = null;

        if (!$isFiltering) {
            $searchPeriode = date('M-y');
        } elseif ($request->filled('search_month') || $request->filled('search_year')) {
            $year  = $request->search_year ?: date('Y');
            $month = $request->search_month ?: date('m');
            $searchPeriode = date('M-y', strtotime("{$year}-{$month}-01"));
        }

        // === 🧾 Query utama ===
        $query = Receipt::query()
            ->when($searchName, fn($q) => $q->where('customer_name', 'like', "%{$searchName}%"))
            ->when($searchPeriode, function ($q) use ($searchPeriode) {
                $monthShort = date('M-y', strtotime($searchPeriode));
                $monthFull  = date('F Y', strtotime($searchPeriode));

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
            ->when($tellerName, fn($q) => $q->where('teller_name', $tellerName));

        // === 📑 Eksekusi Filter Baru ===
        if (!empty($filterCustomer)) {
            $query->where('customer_name', $filterCustomer);
        }

        if (!empty($filterTeller)) {
            $query->where('teller_name', $filterTeller);
        }

        if (!empty($filterPaymentMethod)) {
            if (str_contains($filterPaymentMethod, '|')) {
                [$method, $bank] = explode('|', $filterPaymentMethod);

                $query->whereRaw('LOWER(payment_methode) = ?', [strtolower($method)])
                    ->where('bank_name', $bank);
            } else {
                $query->whereRaw('LOWER(payment_methode) = ?', [strtolower($filterPaymentMethod)]);
            }
        }

        if (!empty($filterDate)) {
            $query->whereDate('payment_time', $filterDate);
        }

        // === 🔝 Default Sorting ===
        $query->orderBy('payment_time', 'desc');

        // === 🔢 Hitung total dan ambil data ===
        $totalPrice = $query->sum('price_after_discount');
        $receipts = $query->paginate($paginate);

        $periods = Receipt::select('periode')
            ->distinct()
            ->orderByRaw("STR_TO_DATE(CONCAT('01 ', periode), '%d %M %Y') DESC")
            ->pluck('periode', 'periode');

        // === 👥 Ambil List Unik untuk Dropdown Filter ===
        $rawPaymentMethods = Receipt::whereNotNull('payment_methode')
            ->distinct()
            ->orderBy('payment_methode', 'asc')
            ->pluck('payment_methode')
            ->toArray();

        $paymentMethodsList = [];

        foreach ($rawPaymentMethods as $method) {
            $upperMethod = strtoupper($method);

            if ($upperMethod === 'TRANSFER') {
                $paymentMethodsList[$method] = 'TRANSFER';

                $banks = Receipt::where('payment_methode', $method)
                    ->whereNotNull('bank_name')
                    ->where('bank_name', '!=', '')
                    ->distinct()
                    ->orderBy('bank_name', 'asc')
                    ->pluck('bank_name')
                    ->toArray();

                if (!empty($banks)) {
                    foreach ($banks as $bank) {
                        $bankUpper = strtoupper($bank);
                        $key = $method . '|' . $bank;
                        $paymentMethodsList[$key] = "TRANSFER - " . $bankUpper;
                    }
                }
            } else {
                $paymentMethodsList[$method] = $upperMethod;
            }
        }

        $paymentMethods = collect($paymentMethodsList);

        $tellers = Receipt::whereNotNull('teller_name')
            ->distinct()
            ->orderBy('teller_name', 'asc')
            ->pluck('teller_name', 'teller_name');

        // 🏠 Ambil daftar address/alamat unik dari tabel Receipt
        $address = Receipt::whereNotNull('customer_address')
            ->where('customer_address', '!=', '')
            ->distinct()
            ->orderBy('customer_address', 'asc')
            ->pluck('customer_address');

        // 📅 Data pendukung untuk dropdown bulan & tahun (jika belum didefinisikan sebelumnya)
        $months = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        ];

        $currentYear = (int) date('Y');
        $years = array_combine(
            range($currentYear, $currentYear - 5),
            range($currentYear, $currentYear - 5)
        );

        LogHelper::logInfo($request, [
            'trace_id' => $traceId,
            'result_count' => $receipts->total(),
        ], 200, 'Successfully retrieved receipt index with local filters', $traceId, $timer->lap());

        // Kirim $address, $months, $years beserta data lainnya ke view
        return view('receipts.index', compact('receipts', 'totalPrice', 'periods', 'tellers', 'paymentMethods', 'address', 'months', 'years'))
            ->with('i', (request()->input('page', 1) - 1) * $paginate);
    }

    /**
     * Display the specified resource.
     */
    public function show(Receipt $receipt)
    {
        $receiptItems = $receipt->items;
        $subtotal = $receiptItems
            ->where('type', '!=', 'discount')
            ->sum('subtotal');

        $discount = $receiptItems
            ->where('type', 'discount')
            ->sum('subtotal');

        $total = $subtotal + $discount + $receipt->tax;
        return view('receipts.show', [
            'receipt' => $receipt,
            'receiptItems' => $receiptItems,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
        ]);
    }

    private function dataPrint(Receipt $receipt): array
    {
        $websystem = Websystem::where('slug', 'websystem')->first();
        $receiptItems = $receipt->items;
        $subtotal = $receiptItems
            ->where('type', '!=', 'discount')
            ->sum('subtotal');

        $discount = $receiptItems
            ->where('type', 'discount')
            ->sum('subtotal');

        $total = $subtotal + $discount + $receipt->tax;

        return [
            'receipt'      => $receipt,
            'receiptItems' => $receiptItems,
            'subtotal'     => $subtotal,
            'discount'     => $discount,
            'total'        => $total,
            'websystem'    => $websystem,
        ];
    }

    public function print(Receipt $receipt)
    {
        $data = $this->dataPrint($receipt);

        $pdf = PDF::loadView('receipts.print', $data);
        return $pdf->download('receipt_' . str($receipt->customer_name)->slug() . '_' . $receipt->periode . '.pdf');
    }

    public function printPreview(string $token)
    {
        // dd($token);
        LogHelper::logInfo(null, ['token' => $token], 200, "Initiate printPreview");
        try {
            // kembalikan ke base64 normal
            $token = str_replace(['-', '_'], ['+', '/'], $token);

            // tambahkan padding =
            $padding = strlen($token) % 4;
            if ($padding) {
                $token .= str_repeat('=', 4 - $padding);
            }

            $slug = Crypt::decryptString($token);
        } catch (\Throwable $e) {
            abort(404);
        }

        $receipt = Receipt::where('slug', $slug)->firstOrFail();

        if (!$receipt) {
            abort(404);
        }

        $data = $this->dataPrint($receipt);

        $pdf = PDF::loadView('receipts.print', $data);

        return $pdf->stream(
            'receipt_' . str($receipt->customer_name)->slug() . '_' . $receipt->periode . '.pdf'
        );
    }

    public function thermalPrint(Receipt $receipt)
    {
        $data = $this->dataPrint($receipt);

        // Atur ukuran kertas thermal (58mm x 100mm) dalam satuan milimeter
        $pdf = PDF::loadView('receipts.thermal', $data)
            ->setPaper([0, 0, 155.43, 283.5], 'portrait'); // 55mm x 100mm dalam satuan point

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
