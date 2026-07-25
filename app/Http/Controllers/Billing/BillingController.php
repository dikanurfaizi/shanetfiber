<?php

namespace App\Http\Controllers\Billing;

use App\Events\PaymentSucceeded;
use App\Events\PayLaterApplied;
use App\Events\PaymentCancelled;
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
use App\Services\Customers\CustomerActivationService;
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
    private CustomerActivationService $customerActivationService;

    public function __construct(
        BillingService $billingService,
        PaymentService $paymentService,
        PaymentCancellationService $paymentCancellationService,
        BillingQueryService $billingQueryService,
        BillingReportService $billingReportService,
        PayLaterService $payLaterService,
        CustomerActivationService $customerActivationService
    ) {
        $this->billingService = $billingService;
        $this->paymentService = $paymentService;
        $this->paymentCancellationService = $paymentCancellationService;
        $this->billingQueryService = $billingQueryService;
        $this->billingReportService = $billingReportService;
        $this->payLaterService = $payLaterService;
        $this->customerActivationService = $customerActivationService;

        $this->middleware('permission:billing-list|billing-create|billing-edit|billing-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:billing-create', ['only' => ['create', 'store', 'create_one', 'store_one']]);
        $this->middleware('permission:billing-edit', ['only' => ['edit', 'update', 'payment', 'unpayment', 'transfer', 'paylater', 'post_paylater']]);
        $this->middleware('permission:billing-delete', ['only' => ['deletePeriode', 'destroyPeriode']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $traceId = (string) Str::uuid();
        $timer = new ExecutionTimer();
        $paginate = $request->input('perPage', 25);

        $billingsQuery = $this->billingQueryService->buildBillingQuery($request, $traceId, false);
        $searchParams = $this->billingQueryService->prepareSearchParams($request);

        // PDF export selected
        if ($request->cetak_pdf === 'selected') {
            LogHelper::logInfo('BillingController::index', ['mode' => 'pdf_selected', 'params' => $searchParams], 200, 'Exporting selected billings to PDF.', $traceId, $timer->lap());
            $pdf = $this->billingReportService->generatePdf($billingsQuery, $searchParams, $traceId);
            return $pdf->download("Tagihan_{$searchParams['name']}_{$searchParams['address']}_{$searchParams['status']}_{$searchParams['month']}_{$searchParams['year']}.pdf");
        }

        // PDF export all
        if ($request->cetak_pdf) {
            LogHelper::logInfo('BillingController::index', ['mode' => 'pdf_all'], 200, 'Exporting all billings to PDF.', $traceId, $timer->lap());
            $allQuery = Billing::with(['billingSubscription', 'customer']);
            $pdf = $this->billingReportService->generatePdf($allQuery, $searchParams, $traceId);
            return $pdf->download('All_Billing.pdf');
        }

        $totalPrice = $billingsQuery->sum('total_amount');
        $data = $billingsQuery->paginate($paginate)->appends($request->all());

        $firstBilling = collect($data->items())->first();
        $periode = $firstBilling?->billingSubscription?->billing_periode;
        $filterData = $this->billingReportService->getFilterData($traceId);

        LogHelper::logInfo(
            'BillingController::index',
            [
                'searchParams' => array_filter($searchParams, fn($v) => ! is_null($v) && $v !== ''),
                'totalPrice' => $totalPrice,
                'totalData' => $data->total(),
            ],
            200,
            'Successfully retrieved billing index page data.',
            $traceId,
            $timer->lap()
        );

        return view('billings.index', array_merge(
            [
                'data' => $data,
                'totalPrice' => $totalPrice,
                'searchStatus' => $searchParams['status'],
                'searchName' => $searchParams['name'],
                'searchAddress' => $searchParams['address'],
                'statuses' => $this->billingQueryService->getStatuses($traceId),
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
        return view('billings.create_one', ['periodes' => BillingPeriode::latest()->get()]);
    }

    public function store(Request $request)
    {
        $traceId = (string) Str::uuid();
        $timer = new ExecutionTimer();

        LogHelper::logInfo('BillingController::store', $request->all(), 200, 'Initiating billing period creation.', $traceId);

        try {
            $periode = $request->name ?? Carbon::now()->format('F Y');
            $billingPeriode = $this->billingService->createBillingPeriode($periode, $traceId);

            LogHelper::logInfo(
                'BillingController::store',
                ['periode_created' => $billingPeriode->name],
                200,
                'Billing period successfully created.',
                $traceId,
                $timer->lap()
            );

            return redirect()->route('billings.create.one')->with('success', "Periode {$billingPeriode->name} have been successfully created.");
        } catch (\Exception $e) {
            LogHelper::logError('BillingController::store', [
                'request' => $request->all(),
                'exception' => get_class($e),
                'message' => $e->getMessage()
            ], 500, 'Failed to create billing period.', $traceId, $timer->lap());

            return redirect()->route('billings.create')->with('error', 'Terjadi Kesalahan, Silakan Periksa Kembali Inputan Atau Hubungi Tim IT.');
        }
    }

    public function store_one(Request $request)
    {
        $traceId = (string) Str::uuid();
        $timer = new ExecutionTimer();

        LogHelper::logInfo('BillingController::store_one', $request->all(), 200, 'Initiating batch billings generation for period.', $traceId);

        try {
            $billingPeriod = BillingPeriode::latest('created_at')->first();
            $totalBillingCreated = $this->billingService->generateBillingsForPeriod($billingPeriod, $traceId);

            LogHelper::logInfo(
                'BillingController::store_one',
                [
                    'target_period' => $billingPeriod?->name,
                    'total_billing_created' => $totalBillingCreated
                ],
                200,
                'Batch billings generation completed successfully.',
                $traceId,
                $timer->lap()
            );

            return redirect()->route('billings.index')->with('success', "{$totalBillingCreated} Billing for {$request->name} have been successfully created.");
        } catch (\Exception $e) {
            LogHelper::logError('BillingController::store_one', [
                'request' => $request->all(),
                'exception' => get_class($e),
                'message' => $e->getMessage()
            ], 500, 'Failed to generate bulk billings.', $traceId, $timer->lap());

            return redirect()->route('billings.index')->with('error', 'Terjadi Kesalahan, Silakan Periksa Kembali Inputan Atau Hubungi Tim IT.');
        }
    }

    public function post_paylater(Request $request, Billing $billing)
    {
        $traceId = (string) Str::uuid();
        $timer = new ExecutionTimer();

        LogHelper::logInfo('BillingController::post_paylater', [
            'billing_id' => $billing->id,
            'customer_id' => $billing->customer_id,
            'payload' => $request->all()
        ], 200, 'Processing post paylater request.', $traceId);

        $result = $this->payLaterService->processPayLater($request, $billing, $traceId);

        if ($result['success']) {
            event(new PayLaterApplied(billingId: $billing->id, eventId: null, traceId: $traceId));

            LogHelper::logInfo('BillingController::post_paylater', ['billing_id' => $billing->id], 200, 'Paylater applied successfully.', $traceId, $timer->lap());
            return redirect()->back()->with('success', $result['message']);
        }

        LogHelper::logWarning('BillingController::post_paylater', [
            'billing_id' => $billing->id,
            'result' => $result
        ], 400, 'Paylater submission rejected by service.', $traceId, $timer->lap());

        return redirect()->back()->with('error', $result['message']);
    }

    public function payment(Request $request, Billing $billing)
    {
        $traceId = (string) Str::uuid();
        $timer = new ExecutionTimer();
        $paymentMethod = $request->payment_methode;

        LogHelper::logInfo('BillingController::payment', [
            'billing_id' => $billing->id,
            'customer_id' => $billing->customer_id,
            'payment_method' => $paymentMethod,
        ], 200, 'Payment request received.', $traceId, $timer->lap());

        // === 1. ROUTING DECISIONS ===
        if (in_array($paymentMethod, ['transfer', 'paylater'])) {
            LogHelper::logInfo('BillingController::payment', [
                'billing_id' => $billing->id,
                'target_route' => $paymentMethod
            ], 200, "Redirecting payment request to internal route format.", $traceId, $timer->lap());
            return redirect()->route("billings.{$paymentMethod}", $billing->slug);
        }

        // === 2. MIKROTIK INFRASTRUCTURE VALIDATION ===
        $customer = $billing->customer;
        $mikrotik = $customer?->mikrotik;

        if (!$mikrotik) {
            LogHelper::logWarning('BillingController::payment', [
                'billing_id' => $billing->id,
                'customer_id' => $customer?->id
            ], 404, 'Mikrotik router info not found for customer.', $traceId, $timer->lap());
            return redirect()->back()->with('error', __('payment.mikrotik.not_found'));
        }

        if (config('services.shanetfiber.allow_mikrotik_actions')) {
            try {
                $client = MikrotikService::getMikrotik($mikrotik, $traceId);
            } catch (\Throwable $e) {
                LogHelper::logError('BillingController::payment', [
                    'billing_id' => $billing->id,
                    'router_id' => $mikrotik->id,
                    'host' => $mikrotik->host,
                    'exception' => get_class($e),
                    'message' => $e->getMessage()
                ], 500, 'Mikrotik API connection failed during payment authorization.', $traceId, $timer->lap());

                return redirect()->back()->with('error', __('payment.mikrotik.connection_failed'));
            }
        }

        // === 3. NOT ACTIVE PERIOD FLOW ===
        if ($paymentMethod === 'not-active-periode') {
            // Panggil service untuk handle DB & Mikrotik sekaligus
            $this->customerActivationService->processNotActivePeriod($billing, $traceId);

            return redirect()->back()->with('success', __('payment.success.no_usage', [
                'name'    => $billing->customer->name,
                'periode' => $billing->billingSubscription?->billingPeriode?->name,
            ]));
        }

        // === 4. MAIN PAYMENT FLOW ===
        try {
            $paymentData = $this->preparePaymentData($request);
            $result = $this->paymentService->processPayment($billing, $paymentData, $traceId);

            if (!empty($result['receipt'])) {
                event(new PaymentSucceeded($result['receipt']->id, $traceId));
                LogHelper::logInfo('BillingController::payment', [
                    'billing_id' => $billing->id,
                    'receipt_id' => $result['receipt']->id
                ], 200, 'Payment transactions completed & succeeded event dispatched.', $traceId, $timer->lap());
            }

            return redirect()->back()->with('success', __('payment.success.processed', ['name' => $billing->customer_name]));
        } catch (\Throwable $e) {
            LogHelper::logError('BillingController::payment', [
                'billing_id' => $billing->id,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500, 'Unexpected execution error occurred in main payment flow.', $traceId, $timer->lap());

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
        $timer = new ExecutionTimer();

        LogHelper::logInfo('BillingController::unpayment', [
            'billing_id' => $billing->id,
            'payment_id' => $billing->billingPayment?->id
        ], 200, 'Received payment cancellation request (unpayment).', $traceId);

        try {
            $result = $this->paymentCancellationService->cancelPayment($billing->billingPayment, $traceId);

            if ($result['success']) {
                $originalReceipt = $result['originalReceipt'] ?? null;

                event(new PaymentCancelled(
                    billingId: $billing->id,
                    receiptId: is_array($originalReceipt) ? ($originalReceipt['id'] ?? null) : ($originalReceipt->id ?? null),
                    receiptData: $originalReceipt,
                    traceId: $traceId,
                ));

                LogHelper::logInfo('BillingController::unpayment', [
                    'billing_id' => $billing->id,
                    'receipt_id' => is_array($originalReceipt) ? ($originalReceipt['id'] ?? null) : ($originalReceipt->id ?? null)
                ], 200, 'Unpayment action processed successfully and event fired.', $traceId, $timer->lap());

                return redirect()->back()->with('success', $result['message'] ?? 'Unpayment berhasil.');
            }

            LogHelper::logWarning('BillingController::unpayment', [
                'billing_id' => $billing->id,
                'result' => $result
            ], 400, 'Unpayment request failed in cancellation engine.', $traceId, $timer->lap());

            return redirect()->back()->with('error', $result['message'] ?? 'Unpayment gagal.');
        } catch (\Throwable $e) {
            LogHelper::logError('BillingController::unpayment', [
                'billing_id' => $billing->id,
                'exception' => get_class($e),
                'message' => $e->getMessage()
            ], 500, 'Fatal exception caught during unpayment handler routine.', $traceId, $timer->lap());

            return redirect()->back()->with('error', 'Unpayment failed: ' . $e->getMessage());
        }
    }

    public function show(Billing $billing)
    {
        $traceId = (string) Str::uuid();
        $timer = new ExecutionTimer();

        $memberDisplay = 'Non Member';
        $endDate = '-';
        $discount = 0;

        $billing->loadMissing(['customer', 'billingSubscription', 'billingPayment.receipt']);

        $billings = Billing::where('customer_id', $billing->customer_id)
            ->with(['billingPayment', 'billingSubscription'])
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        $totalUnpaidAmount = Billing::where('customer_id', $billing->customer_id)->whereIn('status', ['BL', 'PL'])->sum('total_amount');
        $totalPaidAmount = Billing::where('customer_id', $billing->customer_id)->where('status', 'LS')->sum('total_amount');
        $totalUnpaidBill = Billing::where('customer_id', $billing->customer_id)->whereIn('status', ['BL', 'PL'])->count();
        $totalPaidBill = Billing::where('customer_id', $billing->customer_id)->where('status', 'LS')->count();

        $customerMembership = CustomerMembership::where('customer_id', $billing->customer_id)->first();
        $customerSubscription = $billing?->billingSubscription?->customerSubscription;

        if ($customerMembership !== null) {
            $memberDisplay = $customerMembership->membership->name;
            $endDate = 'Lifetime';

            if ($customerMembership->membership->discount_type === 'amount') {
                $discount = $customerMembership->membership->discount_value;
            } else {
                $packagePrice = $customerSubscription?->package?->price ?? 0;
                $discount = ($customerMembership->membership->discount_value / 100) * $packagePrice;
            }

            if (!empty($customerMembership->end_date)) {
                $endDate = Carbon::parse($customerMembership->end_date)->format('d F Y');
            }
        }

        $billings->getCollection()->transform(function ($item) {
            $paymentTime = $item->billingPayment && $item->billingPayment->payment_time
                ? Carbon::parse($item->billingPayment->payment_time)->format('d M Y, H:i') . ' WIB' : '-';
            $payLaterTime = $item->pay_later ? Carbon::parse($item->pay_later)->format('d M Y, H:i') . ' WIB' : '-';
            $dueDateTime = $item->due_date ? Carbon::parse($item->due_date)->format('d M Y, H:i') . ' WIB' : '-';
            $paymentMethod = $item->billingPayment?->payment_method
                ? ucwords(str_replace('_', ' ', strtolower($item->billingPayment->payment_method))) : 'cash';

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
                'paid_by'         => $item->billingPayment?->paidBy?->name,
            ];

            return $item;
        });

        LogHelper::logInfo('BillingController::show', [
            'billing_id' => $billing->id,
            'customer_id' => $billing->customer_id,
            'membership_status' => $memberDisplay
        ], 200, 'Rendering billing profile show view.', $traceId, $timer->lap());

        return view('billings.show', [
            'billing' => $billing,
            'billings' => $billings,
            'totalUnpaidAmount' => $totalUnpaidAmount,
            'totalPaidAmount' => $totalPaidAmount,
            'totalUnpaidBill' => $totalUnpaidBill,
            'totalPaidBill' => $totalPaidBill,
            'memberDisplay' => $memberDisplay,
            'endDate' => $endDate,
            'discount' => $discount,
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
        $billings = Billing::where('customer_id', $billing->customer_id)->where('status', 'BL')->get();

        return view('billings.transfer', compact('billing', 'banks'), ['billings' => $billings]);
    }

    public function deletePeriode(BillingPeriode $billingperiode)
    {
        return view('billings.delete', compact('billingperiode'));
    }

    public function destroyPeriode(BillingPeriode $billingperiode)
    {
        $traceId = (string) Str::uuid();
        LogHelper::logWarning('BillingController::destroyPeriode', [
            'periode_id' => $billingperiode->id,
            'periode_name' => $billingperiode->name
        ], 200, 'Executing deletion of billing period resource.', $traceId);

        $billingperiode->delete();

        return redirect()->route('billings.create')->with('success', 'Periode billing deleted successfully.');
    }

    public function billing_auto_generate(Request $request)
    {
        $traceId = (string) Str::uuid();
        $request->validate([
            'billing_auto_day' => 'nullable|integer|min:1|max:28',
            'billing_auto_generate' => 'nullable|boolean',
        ]);

        $websystem = Websystem::first();
        $billingAutoGenerate = $request->input('billing_auto_generate', 0);

        $websystem->billing_auto_generate = (bool) $billingAutoGenerate;
        $websystem->billing_auto_day = $request->input('billing_auto_day');
        $websystem->save();

        LogHelper::logInfo('BillingController::billing_auto_generate', [
            'auto_generate' => $websystem->billing_auto_generate,
            'auto_day' => $websystem->billing_auto_day
        ], 200, 'Automatic billing settings adjusted.', $traceId);

        return redirect()->back()->with('success', 'Automatic billing configuration has been set');
    }

    public function exportAll(Request $request)
    {
        $traceId = (string) Str::uuid();
        LogHelper::logInfo('BillingController::exportAll', ['request' => $request->all()], 200, "Triggered download workbook excel yearly.", $traceId);

        return Excel::download(new BillingYearWorkbookExport(), 'Billing-' . now()->year . '.xlsx');
    }

    public function exportSelected(Request $request)
    {
        $traceId = (string) Str::uuid();
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
            $statusCamel = Str::title(strtolower($statusMap[$status] ?? 'Unknown'));
            $fileName = "{$statusCamel}_Billing_{$periode}_{$timestamp}.xlsx";
        }

        LogHelper::logInfo('BillingController::exportSelected', [
            'status' => $status,
            'periode' => $periode,
            'generated_filename' => $fileName
        ], 200, "Triggered custom data billing snapshot download.", $traceId);

        return Excel::download(new BillingSnapshotExport($status, $periode), $fileName);
    }

    public function printInvoice(Billing $billing)
    {
        $traceId = (string) Str::uuid();
        if (!in_array($billing->status, ['BL', 'PL'], true)) {
            LogHelper::logWarning('BillingController::printInvoice', ['billing_id' => $billing->id, 'current_status' => $billing->status], 403, 'User restricted from printing non-unpaid billing invoice.', $traceId);
            abort(403, 'Invoice only available for unpaid billing');
        }

        LogHelper::logInfo('BillingController::printInvoice', ['billing_id' => $billing->id], 200, 'Generating Invoice PDF for download.', $traceId);
        $data = $this->dataPrint($billing);

        return PDF::loadView('billings.print-invoice', $data)->download('INVOICE_' . $billing->billing_number . '.pdf');
    }

    private function dataPrint(Billing $billing): array
    {
        $billing->load(['customer', 'billingItems', 'billingSubscription']);

        $bankCollector = new AdminBankCollector();
        $banks = $bankCollector->getBanksByUserId((int) $billing->customer->user_id);
        $websystem = Websystem::where('slug', 'websystem')->first();
        $items = $billing->billingItems->groupBy('item_type');

        return [
            'billing'    => $billing,
            'customer'   => $billing->customer,
            'websystem'  => $websystem,
            'packages'  => $items->get('package', collect()),
            'addons'    => $items->get('addon', collect()),
            'discounts' => $items->get('discount', collect()),
            'banks'      => $banks,
        ];
    }

    public function paylaterPreview(string $token)
    {
        $traceId = (string) Str::uuid();
        LogHelper::logInfo('BillingController::paylaterPreview', [], 200, "Initiating tokenized paylater decryption process.", $traceId);

        try {
            $normalizedToken = str_replace(['-', '_'], ['+', '/'], $token);
            $padding = strlen($normalizedToken) % 4;
            if ($padding) {
                $normalizedToken .= str_repeat('=', 4 - $padding);
            }

            $slug = Crypt::decryptString($normalizedToken);
        } catch (\Throwable $e) {
            LogHelper::logError('BillingController::paylaterPreview', ['exception' => get_class($e), 'message' => $e->getMessage()], 404, 'Token decryption failed.', $traceId);
            abort(404);
        }

        $billing = Billing::where('slug', $slug)->firstOrFail();
        $data = $this->dataPrint($billing);
        $pdf = PDF::loadView('billings.paylater-preview', $data);

        LogHelper::logInfo('BillingController::paylaterPreview', ['billing_id' => $billing->id, 'slug' => $slug], 200, 'Streaming encrypted paylater invoice preview.', $traceId);

        return $pdf->stream('paylater_' . str($billing->customer->name)->slug() . '_' . $billing->periode?->display_name . '.pdf');
    }
}
