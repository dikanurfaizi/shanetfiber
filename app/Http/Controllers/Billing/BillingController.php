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
use App\Services\Messaging\Collectors\AdminBankCollector;
use App\Services\Routerboard\MikrotikService;
use App\Services\Routerboard\Ppp\SecretService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
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

    private SecretService $secretService;

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
        // dd($request);
        $result = $this->payLaterService->processPayLater($request, $billing, $traceId);

        if ($result['success']) {
            event(new PayLaterApplied(
                billingId: $billing->id,
                eventId: null,
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
        $paymentMethod = $request->payment_methode;

        LogHelper::logInfo(null, [
            'billing_id'     => $billing->id,
            'customer_id'    => $billing->customer_id,
            'payment_method' => $paymentMethod,
        ], 100, 'payment.request.received', $traceId, $timer->lap());

        // === 1. ROUTING DECISIONS (SHORT-CIRCUIT) ===
        if (in_array($paymentMethod, ['transfer', 'paylater'])) {
            LogHelper::logInfo(null, ['billing_id' => $billing->id], 200, "payment.route.{$paymentMethod}", $traceId, $timer->lap());
            return redirect()->route("billings.{$paymentMethod}", $billing->slug);
        }

        // === 2. MIKROTIK INFRASTRUCTURE & ACTIVE SESSION VALIDATION ===
        $customer = $billing->customer;
        $mikrotik = $customer?->mikrotik;

        if (!$mikrotik) {
            return redirect()->back()->with('error', __('payment.mikrotik.not_found'));
        }

        // Validasi Jalur Koneksi Utama API MikroTik
        try {
            $client = MikrotikService::getMikrotik($mikrotik, $traceId);
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'billing_id' => $billing->id,
                'router_id'  => $mikrotik->id,
                'host'       => $mikrotik->host,
                'error'      => $e->getMessage()
            ], 500, 'payment.mikrotik_connection.failed', $traceId, $timer->lap());

            return redirect()->back()->with('error', __('payment.mikrotik.connection_failed'));
        }

        // Validasi Keberadaan Sesi Aktif Pelanggan (PPP Active Session)
        // $usernamePpp = $customer->username;
        // try {
        //     $activeUser = $this->secretService->getOnePppSecretActive($mikrotik, $usernamePpp, $traceId);

        //     if (empty($activeUser)) {
        //         LogHelper::logWarning(null, [
        //             'billing_id' => $billing->id,
        //             'username'   => $usernamePpp
        //         ], 404, 'payment.user_session.offline', $traceId, $timer->lap());

        //         return redirect()->back()->with('error', __('payment.mikrotik.user_offline', ['username' => $usernamePpp]));
        //     }
        // } catch (\Throwable $e) {
        //     LogHelper::logError(null, [
        //         'billing_id' => $billing->id,
        //         'username'   => $usernamePpp,
        //         'error'      => $e->getMessage()
        //     ], 500, 'payment.user_query.failed', $traceId, $timer->lap());

        //     return redirect()->back()->with('error', __('payment.mikrotik.query_failed', ['username' => $usernamePpp]));
        // }

        // === 3. NOT ACTIVE PERIOD FLOW ===
        if ($paymentMethod === 'not-active-periode') {
            $userId = auth()->user()->id;

            $billing->update([
                'updated_by' => $userId,
                'status'     => 'VD',
                'updated_at' => now()
            ]);

            $billing->billingSubscription?->update([
                'updated_by' => $userId,
                'status'     => 'expired'
            ]);

            $billing->billingSubscription?->customerSubscription?->update([
                'status'        => 'paused',
                'terminated_at' => now(),
            ]);

            LogHelper::logInfo(null, ['billing_id' => $billing->id, 'status' => 'VD'], 200, 'payment.marked.no_usage', $traceId, $timer->lap());

            return redirect()->back()->with('success', __('payment.success.no_usage', ['name' => $billing->customer_name]));
        }

        // === 4. MAIN PAYMENT FLOW ===
        try {
            $paymentData = $this->preparePaymentData($request);
            $result = $this->paymentService->processPayment($billing, $paymentData, $traceId);

            if (!empty($result['receipt'])) {
                event(new PaymentSucceeded($result['receipt']->id, $traceId));
            }

            return redirect()->back()->with('success', __('payment.success.processed', ['name' => $billing->customer_name]));
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'billing_id' => $billing->id,
                'exception'  => class_basename($e),
                'message'    => $e->getMessage(),
            ], 500, 'payment.request.failed', $traceId, $timer->lap());

            return redirect()->back()->with('error', __('payment.errors.failed'));
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
        $customerSubscription = $billing?->billingSubscription?->customerSubscription;

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
            'VD' => 'NOT ACTIVE PERIOD',
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

        $data = $this->dataPrint($billing);

        return PDF::loadView('billings.print-invoice', $data)->download(
            'INVOICE_' . $billing->billing_number . '.pdf'
        );
    }

    private function dataPrint(Billing $billing): array
    {
        $billing->load([
            'customer',
            'billingItems',
            'billingSubscription',
        ]);

        $bankCollector = new AdminBankCollector();
        $banks = $bankCollector->getBanksByUserId((int) $billing->customer->user_id);

        $websystem = Websystem::where('slug', 'websystem')->first();

        $items = $billing->billingItems->groupBy('item_type');

        return [
            'billing'    => $billing,
            'customer'   => $billing->customer,
            'websystem'  => $websystem,

            // grouped items
            'packages'  => $items->get('package', collect()),
            'addons'    => $items->get('addon', collect()),
            'discounts' => $items->get('discount', collect()),

            'banks'      => $banks,
        ];
    }

    public function paylaterPreview(string $token)
    {
        // dd($token);
        LogHelper::logInfo(null, ['token' => $token], 200, "Initiate paylaterPreview");
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

        $billing = Billing::where('slug', $slug)->firstOrFail();

        if (!$billing) {
            abort(404);
        }

        $data = $this->dataPrint($billing);

        $pdf = PDF::loadView('billings.paylater-preview', $data);

        return $pdf->stream(
            'paylater_' . str($billing->customer->name)->slug() . '_' . $billing->periode->display_name . '.pdf'
        );
    }
}
