<?php

namespace App\Http\Controllers\Billing;

use App\Events\PaymentSucceeded;
use App\Events\PayLaterApplied;
use App\Exports\Billing\BillingSnapshotExport;
use App\Exports\Billing\BillingYearWorkbookExport;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingPeriode;
use App\Models\Customers\CustomerSubscription;
use App\Models\Websystem;
use App\Models\Receipt;
use App\Models\Membership\CustomerMembership;
use App\Services\Billings\BillingQueryService;
use App\Services\Billings\BillingReportService;
use App\Services\Billings\BillingService;
use App\Services\Billings\PayLaterService;
use App\Services\Billings\PaymentCancellationService;
use App\Services\Billings\PaymentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class BillingController extends Controller
{
    private BillingService $billingService;

    private PaymentService $paymentService;

    private PaymentCancellationService $paymentCancellationService;

    private BillingQueryService $billingQueryService;

    private BillingReportService $billingReportService;

    private PayLaterService $payLaterService;

    public function __construct(
        BillingService $billingService,
        PaymentService $paymentService,
        PaymentCancellationService $paymentCancellationService,
        BillingQueryService $billingQueryService,
        BillingReportService $billingReportService,
        PayLaterService $payLaterService
    ) {
        $this->billingService = $billingService;
        $this->paymentService = $paymentService;
        $this->paymentCancellationService = $paymentCancellationService;
        $this->billingQueryService = $billingQueryService;
        $this->billingReportService = $billingReportService;
        $this->payLaterService = $payLaterService;
        $this->middleware('permission:billing-list|billing-create|billing-edit|billing-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:billing-create', ['only' => ['create', 'store', 'create_one', 'store_one']]);
        $this->middleware('permission:billing-edit', ['only' => ['edit', 'update', 'payment', 'unpayment', 'transfer', 'paylater', 'post_paylater']]);
        $this->middleware('permission:billing-delete', ['only' => ['deletePeriode', 'destroyPeriode']]);
    }

    // ... (method index, create, create_one tetap sama)
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $traceId = (string) Str::uuid();
        $paginate = $request->input('perPage', 25);

        // query sudah dipindah ke service
        $billingsQuery = $this->billingQueryService->buildBillingQuery($request, $traceId, false);

        $searchParams = $this->billingQueryService->prepareSearchParams($request);

        // PDF export
        if ($request->cetak_pdf === 'selected') {
            $pdf = $this->billingReportService->generatePdf($billingsQuery, $searchParams, $traceId);

            return $pdf->download("Tagihan_{$searchParams['name']}_{$searchParams['address']}_{$searchParams['status']}_{$searchParams['month']}_{$searchParams['year']}.pdf");
        }

        if ($request->cetak_pdf) {
            $allQuery = Billing::with(['billingSubscription', 'customer']);
            $pdf = $this->billingReportService->generatePdf($allQuery, $searchParams, $traceId);

            return $pdf->download('All_Billing.pdf');
        }

        // total amount
        $totalPrice = $billingsQuery->sum('total_amount');

        // pagination
        $data = $billingsQuery->paginate($paginate)->appends($request->all());

        // ambil periode dari item pertama yang sedang ditampilkan (periode terakhir)
        $firstBilling = collect($data->items())->first();
        $periode = $firstBilling?->billingSubscription?->billing_periode;

        $filterData = $this->billingReportService->getFilterData($traceId);

        LogHelper::logInfo(
            null,
            [
                'searchParams' => array_filter($searchParams, fn($v) => ! is_null($v) && $v !== ''),
                'totalPrice' => $totalPrice,
                'totalData' => $data->total(),
            ],
            200,
            'Successfully retrieved billing data',
            $traceId
        );

        return view('billings.index', array_merge(
            [
                'data' => $data,
                'totalPrice' => $totalPrice,
                'searchStatus' => $searchParams['status'],
                'searchName' => $searchParams['name'],
                'searchAddress' => $searchParams['address'],
                'statuses' => $this->billingQueryService->getStatuses(),
                'selectedPeriod' => $request->period,
                'periode' => $periode,
            ],
            $filterData
        ))->with('i', (request()->input('page', 1) - 1) * $paginate);
    }

    public function create()
    {
        $traceId = (string) Str::uuid();
        $billingPeriodeName = BillingPeriode::latest('created_at')->value('name');
        $periodeNow = Carbon::now()->format('F Y');
        if ($periodeNow == $billingPeriodeName) {
            $billingPeriodes = CarbonPeriod::create(now()->addmonth(), '1 month', now()->addMonths(12));
        } else {
            $billingPeriodes = CarbonPeriod::create(now(), '1 month', now()->addMonths(11));
        }
        $data = BillingPeriode::paginate(12);

        $websystem = Websystem::first();
        $billingAutoGenerate = $websystem?->billing_auto_generate ?? false;
        $billingAutoDay = $websystem?->billing_auto_day;

        return view('billings.create', compact('data', 'billingPeriodes', 'billingAutoGenerate', 'billingAutoDay'))
            ->with('i', (request()->input('page', 1) - 1) * 12);
    }

    public function create_one()
    {
        $traceId = (string) Str::uuid();
        $periodes = BillingPeriode::latest()->get();

        return view('billings.create_one', compact('periodes'));
    }

    public function store(Request $request)
    {
        $traceId = (string) Str::uuid();
        try {
            $periode = $request->name ?? Carbon::now()->format('F Y');
            $billingPeriode = $this->billingService->createBillingPeriode($periode, $traceId);

            LogHelper::logInfo(
                null,
                ['periode_created' => $billingPeriode->name],
                200,
                'Billing have been successfully created.',
                $traceId
            );

            $message = "Periode {$billingPeriode->name} have been successfully created.";

            return redirect()->route('billings.create.one')->with('success', $message);
        } catch (\Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Failed to create billings', $traceId);

            return redirect()->route('billings.create')->with('error', 'Terjadi Kesalahan, Silakan Periksa Kembali Inputan Atau Hubungi Tim IT.');
        }
    }

    public function store_one(Request $request)
    {
        $traceId = (string) Str::uuid();
        try {
            $billingPeriod = BillingPeriode::latest('created_at')->first();
            $totalBillingCreated = $this->billingService->generateBillingsForPeriod($billingPeriod, $traceId);

            LogHelper::logInfo(
                null,
                ['total_billing_created' => $totalBillingCreated],
                200,
                'Billing have been successfully created.',
                $traceId
            );

            $message = "{$totalBillingCreated} Billing for {$request->name} have been successfully created.";

            return redirect()->route('billings.index')->with('success', $message);
        } catch (\Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Failed to create billings', $traceId);

            return redirect()->route('billings.index')->with('error', 'Terjadi Kesalahan, Silakan Periksa Kembali Inputan Atau Hubungi Tim IT.');
        }
    }

    public function post_paylater(Request $request, Billing $billing)
    {
        $traceId = (string) Str::uuid();
        $result = $this->payLaterService->processPayLater($request, $billing, $traceId);

        if ($result['success']) {
            event(new PayLaterApplied(
                billingId: $billing->id,
                traceId: $traceId
            ));
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    public function payment(Request $request, Billing $billing)
    {
        $traceId = (string) Str::uuid();
        $timer = new ExecutionTimer();

        LogHelper::logInfo(null, [
            'billing_id'     => $billing->id,
            'customer_id'    => $billing->customer_id,
            'payment_method' => $request->payment_methode,
        ], 100, 'payment.request.received', $traceId, $timer->lap());

        $paymentMethod = $request->payment_methode;

        // === ROUTING DECISIONS (LOG ONCE PER PATH) ===
        if ($paymentMethod === 'transfer') {

            LogHelper::logInfo(null, [
                'billing_id' => $billing->id,
            ], 200, 'payment.route.transfer', $traceId, $timer->lap());

            return redirect()->route('billings.transfer', $billing->slug);
        }

        if ($paymentMethod === 'paylater') {

            LogHelper::logInfo(null, [
                'billing_id' => $billing->id,
            ], 200, 'payment.route.paylater', $traceId, $timer->lap());

            return redirect()->route('billings.paylater', $billing->slug);
        }

        if ($paymentMethod === 'not-active-periode') {

            $billing->update([
                'teller_name' => auth()->user()->name,
                'status'      => 'NP',
            ]);

            LogHelper::logInfo(null, [
                'billing_id' => $billing->id,
                'status'     => 'NP',
            ], 200, 'payment.marked.no_usage', $traceId, $timer->lap());

            return redirect()->back()
                ->with('success', 'The billing status for ' . $billing->customer_name . ' has been marked as No Usage for this period.');
        }

        // === MAIN PAYMENT FLOW ===
        try {
            $paymentData = $this->preparePaymentData($request);

            $result = $this->paymentService
                ->processPayment($billing, $paymentData, $traceId);

            if (!empty($result['receipt'])) {
                event(new PaymentSucceeded($result['receipt']->id, $traceId));
            }

            return redirect()->back()
                ->with('success', 'Payment ' . $billing->customer_name . ' successfully.');
        } catch (\Throwable $e) {

            // hanya log di boundary, DETAIL sudah di service
            LogHelper::logError(null, [
                'billing_id' => $billing->id,
                'exception'  => class_basename($e),
                'message'    => $e->getMessage(),
            ], 500, 'payment.request.failed', $traceId, $timer->lap());

            return redirect()->back()
                ->with('error', 'Payment failed.');
        }
    }

    private function preparePaymentData(Request $request): array
    {
        $paymentData = [
            'payment_method' => 'cash',
            'bank_name' => null,
            'account_name' => null,
            'account_number' => null,
            'deposit_bank_name' => null,
            'deposit_account_name' => null,
            'deposit_account_number' => null,
            'reference_id' => null,
        ];

        if ($request->transfer_payment) {
            $bank = Bank::findOrFail($request->bank_transfer);

            $paymentData = [
                'payment_method' => 'transfer',
                'bank_name' => $bank->bank,
                'account_name' => $bank->name,
                'account_number' => $bank->account_number,
                'deposit_bank_name' => $request->deposit_bank_name,
                'deposit_account_name' => $request->deposit_account_name,
                'deposit_account_number' => $request->deposit_account_number,
                'reference_id' => $request->reference_id,
            ];
        }

        return $paymentData;
    }

    public function unpayment(Billing $billing)
    {
        $traceId = (string) Str::uuid();

        try {
            $result = $this->paymentCancellationService
                ->cancelPayment($billing->billingPayment, $traceId);

            if ($result['success']) {
                return redirect()
                    ->back()
                    ->with('success', $result['message'] ?? 'Unpayment berhasil.');
            }

            return redirect()
                ->back()
                ->with('error', $result['message'] ?? 'Unpayment gagal.');
        } catch (\Throwable $e) {
            // Fallback: kalau ada error tak terduga di service
            return redirect()
                ->back()
                ->with('error', 'Unpayment failed: ' . $e->getMessage());
        }
    }

    public function show(Billing $billing)
    {
        $memberDisplay = 'Non Member';
        $endDate = '-';
        $discount = 0;

        // Load customer + billingSubscription
        $billing->loadMissing(['customer', 'billingSubscription', 'billingPayment.receipt']);

        // Ambil semua billing pelanggan, pagination
        $billings = Billing::where('customer_id', $billing->customer_id)
            ->with(['billingPayment', 'billingSubscription'])   // 👈 eager load per billing
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        // TOTALS
        $totalUnpaidAmount = Billing::where('customer_id', $billing->customer_id)
            ->whereIn('status', ['BL', 'PL']) // belum lunas / pay later
            ->sum('total_amount');

        $totalPaidAmount = Billing::where('customer_id', $billing->customer_id)
            ->where('status', 'LS') // lunas
            ->sum('total_amount');

        $totalUnpaidBill = Billing::where('customer_id', $billing->customer_id)
            ->whereIn('status', ['BL', 'PL'])
            ->count();

        $totalPaidBill = Billing::where('customer_id', $billing->customer_id)
            ->where('status', 'LS')
            ->count();

        // Membership & Subscription
        $customerMembership = CustomerMembership::where('customer_id', $billing->customer_id)->first();
        $customerSubscription = CustomerSubscription::where('customer_id', $billing->customer_id)
            ->where('status', 'active')
            ->first();

        // Membership Logic
        if ($customerMembership !== null) {

            $memberDisplay = $customerMembership->membership->name;
            $endDate = 'Lifetime';

            // Hitung discount by type
            if ($customerMembership->membership->discount_type === 'amount') {
                $discount = $customerMembership->membership->discount_value;
            } else {

                // Persentase dari harga paket aktif
                $packagePrice = $customerSubscription?->package?->price ?? 0;

                $discount = ($customerMembership->membership->discount_value / 100) * $packagePrice;
            }

            // Override end_date jika ada
            if (!empty($customerMembership->end_date)) {
                $endDate = Carbon::parse($customerMembership->end_date)->format('d F Y');
            }
        }

        $billings->getCollection()->transform(function ($item) {

            // LS — Payment Time
            $paymentTime = $item->billingPayment && $item->billingPayment->payment_time
                ? Carbon::parse($item->billingPayment->payment_time)->format('d M Y, H:i') . ' WIB'
                : '-';

            // PL — Pay Later Time
            $payLaterTime = $item->pay_later
                ? Carbon::parse($item->pay_later)->format('d M Y, H:i') . ' WIB'
                : '-';

            // BL — Due Date
            $dueDateTime = $item->due_date
                ? Carbon::parse($item->due_date)->format('d M Y, H:i') . ' WIB'
                : '-';

            // Payment Method
            $paymentMethod = $item->billingPayment?->payment_method
                ? ucwords(str_replace('_', ' ', strtolower($item->billingPayment->payment_method)))
                : 'cash';

            // Transfer Receipt Data
            $receipt = Receipt::where('billing_payment_id', $item->billingPayment?->id)->first();

            $item->formatted = [
                'payment_time'    => $paymentTime,
                'pay_later'       => $payLaterTime,
                'due_date'        => $dueDateTime,
                'method'          => $paymentMethod,
                'bank_name'       => $receipt->bank_name ?? null,
                'account_name'    => $receipt->account_name ?? null,
                'account_number'  => $receipt->account_number ?? null,
                'receipt_slug'    => $receipt->slug ?? null,
                'paid_by' => $item->billingPayment?->paidBy?->name,
            ];

            return $item;
        });

        return view('billings.show', [
            'billing' => $billing,
            'billings' => $billings,

            // Additional totals requested
            'totalUnpaidAmount' => $totalUnpaidAmount,
            'totalPaidAmount' => $totalPaidAmount,
            'totalUnpaidBill' => $totalUnpaidBill,
            'totalPaidBill' => $totalPaidBill,

            // Membership & subscription data
            'memberDisplay' => $memberDisplay,
            'endDate' => $endDate,
            'discount' => $discount,

            // Active subscription
            'customerSubscription' => $customerSubscription,
        ]);
    }

    public function paylater(Request $request, Billing $billing)
    {
        return view('billings.paylater', compact('billing'));
    }

    public function transfer(Request $request, Billing $billing)
    {
        $banks = Bank::orderBy('name', 'ASC')->where('disabled', 'false')->get();
        $billingCustomerId = $billing->customer_id;
        $billings = Billing::where('customer_id', $billingCustomerId)->where('status', 'BL')->get();

        return view('billings.transfer', compact('billing', 'banks'), [
            'billings' => $billings,
        ]);
    }

    public function deletePeriode(BillingPeriode $billingperiode)
    {
        return view('billings.delete', compact('billingperiode'));
    }

    public function destroyPeriode(BillingPeriode $billingperiode)
    {
        $billingperiode->delete();

        return redirect()->route('billings.create')->with('success', 'Periode billing deleted successfully.');
    }

    public function billing_auto_generate(Request $request)
    {
        $request->validate([
            'billing_auto_day' => 'nullable|integer|min:1|max:28',
            'billing_auto_generate' => 'nullable|boolean',
        ]);

        $websystem = Websystem::first();

        // Jika input tidak ada, dianggap 0 (false)
        $billingAutoGenerate = $request->input('billing_auto_generate', 0);

        $websystem->billing_auto_generate = (bool) $billingAutoGenerate;
        $websystem->billing_auto_day = $request->input('billing_auto_day');
        $websystem->save();

        return redirect()->back()->with('success', 'Automatic billing configuration has been set');
    }

    public function exportAll(Request $request)
    {
        LogHelper::logInfo(null, ['request' => $request->all()], 200, "", null);
        return Excel::download(
            new BillingYearWorkbookExport(),
            'Billing-' . now()->year . '.xlsx'
        );
    }

    public function exportSelected(Request $request)
    {
        $timestamp = now()->format('Ymd_His');
        $status = $request->get('status');

        $periode = $request->get('periode');

        $statusMap = [
            'LS' => 'PAID',
            'BL' => 'UNPAID',
            'PL' => 'PAYLATER',
            'NP' => 'NOT ACTIVE PERIOD',
        ];

        $statusCamel = now()->format('F Y');
        $fileName = "Billing_{$statusCamel}_{$timestamp}.xlsx";
        if ($status != null) {
            $statusCamel = Str::title(strtolower($statusMap[$status]));
            $fileName = "{$statusCamel}_Billing_{$periode}_{$timestamp}.xlsx";
        }

        return Excel::download(
            new BillingSnapshotExport($status, $periode),
            $fileName
        );
    }

    public function printInvoice(Billing $billing)
    {
        // Pastikan invoice hanya untuk BL / PL
        if (!in_array($billing->status, ['BL', 'PL'], true)) {
            abort(403, 'Invoice only available for unpaid billing');
        }

        $billing->load([
            'customer',
            'billingItems',      // 👈 ini kunci
            'billingSubscription',
        ]);

        $banks = Bank::orderBy('name', 'ASC')->where('disabled', 'false')->where('user_id', $billing->customer->user_id)->get();

        $websystem = Websystem::where('slug', 'websystem')->first();

        $items = $billing->billingItems->groupBy('item_type');

        return PDF::loadView('billings.print-invoice', [
            'billing'    => $billing,
            'customer'   => $billing->customer,
            'websystem'  => $websystem,

            // grouped items
            'packages'  => $items->get('package', collect()),
            'addons'    => $items->get('addon', collect()),
            'discounts' => $items->get('discount', collect()),

            'banks'      => $banks,
        ])->download(
            'INVOICE_' . $billing->billing_number . '.pdf'
        );
    }
}
