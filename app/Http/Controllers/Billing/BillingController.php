<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billings\BillingService;
use App\Services\Billings\PaymentService;
use App\Services\Billings\BillingQueryService;
use App\Services\Billings\BillingReportService;
use App\Services\Billings\PayLaterService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Carbon\CarbonPeriod;
use App\Helpers\LogHelper;
use App\Models\Billing\Billing;
use App\Models\Billing\BillingPeriode;
use App\Models\Bank;
use App\Models\Websystem;
use App\Exports\BillingsExport;
use Maatwebsite\Excel\Facades\Excel;

class BillingController extends Controller
{
    private BillingService $billingService;
    private PaymentService $paymentService;
    private BillingQueryService $billingQueryService;
    private BillingReportService $billingReportService;
    private PayLaterService $payLaterService;

    public function __construct(
        BillingService $billingService,
        PaymentService $paymentService,
        BillingQueryService $billingQueryService,
        BillingReportService $billingReportService,
        PayLaterService $payLaterService
    ) {
        $this->billingService = $billingService;
        $this->paymentService = $paymentService;
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
            return $pdf->download("All_Billing.pdf");
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
                'searchParams' => array_filter($searchParams, fn($v) => !is_null($v) && $v !== ''),
                'totalPrice'   => $totalPrice,
                'totalData'    => $data->total()
            ],
            200,
            "Successfully retrieved billing data",
            $traceId
        );

        return view('billings.index', array_merge(
            [
                'data'          => $data,
                'totalPrice'    => $totalPrice,
                'searchStatus'  => $searchParams['status'],
                'searchName'    => $searchParams['name'],
                'searchAddress' => $searchParams['address'],
                'statuses'      => $this->billingQueryService->getStatuses(),
                'selectedPeriod' => $request->period,
                'periode'       => $periode
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
            // $totalBillingCreated = $this->billingService->generateBillingsForPeriod($billingPeriode, $traceId);

            LogHelper::logInfo(
                null,
                ['periode_created' => $billingPeriode->name],
                200,
                "Billing have been successfully created.",
                $traceId
            );

            $message = "Periode {$billingPeriode->name} have been successfully created.";
            return redirect()->route('billings.create.one')->with('success', $message);
        } catch (\Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, "Failed to create billings", $traceId);
            return redirect()->route('billings.create')->with('error', 'Terjadi Kesalahan, Silakan Periksa Kembali Inputan Atau Hubungi Tim IT.');
        }
    }

    public function store_one(Request $request)
    {
        $traceId = (string) Str::uuid();
        try {
            // dd($request);
            $totalBillingCreated = $this->billingService->generateBillingsForPeriod($request->id, $traceId);

            LogHelper::logInfo(
                null,
                ['total_billing_created' => $totalBillingCreated],
                200,
                "Billing have been successfully created.",
                $traceId
            );

            $message = "{$totalBillingCreated} Billing for {$request->name} have been successfully created.";
            return redirect()->route('billings.index')->with('success', $message);
        } catch (\Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, "Failed to create billings", $traceId);
            return redirect()->route('billings.index')->with('error', 'Terjadi Kesalahan, Silakan Periksa Kembali Inputan Atau Hubungi Tim IT.');
        }
    }

    public function post_paylater(Request $request, Billing $billing)
    {
        $traceId = (string) Str::uuid();
        $result = $this->payLaterService->processPayLater($request, $billing, $traceId);

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    public function payment(Request $request, Billing $billing)
    {
        $traceId = (string) Str::uuid();
        $paymentMethod = $request->payment_methode;

        if ($paymentMethod == 'transfer') {
            return redirect()->route('billings.transfer', $billing->slug);
        } elseif ($paymentMethod == 'paylater') {
            return redirect()->route('billings.paylater', $billing->slug);
        } elseif ($paymentMethod == 'not-active-periode') {
            $billing->update([
                'teller_name' => auth()->user()->name,
                'status' => 'NP',
            ]);
            return redirect()->back()->with('success', 'The billing status for ' . $billing->customer_name . ' has been marked as No Usage for this period.');
        }

        try {
            $paymentData = $this->preparePaymentData($request);
            $result = $this->paymentService->processPayment($billing, $paymentData, $traceId);

            $this->paymentService->sendPaymentNotification(
                $billing->customer_id,
                $paymentData['payment_method'],
                $result['trace_id'],
                $billing->id,
                $result['receipt']->id
            );

            return redirect()->back()->with('success', 'Payment ' . $billing->customer_name . ' successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Payment failed: ' . $e->getMessage());
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
        $result = $this->paymentService->cancelPayment($billing, $traceId);

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    public function show(Billing $billing)
    {
        $billing->loadMissing(['customer.member', 'billingSubscription']);

        $billings = Billing::where('customer_id', $billing->customer_id)
            ->where('status', '!=', 'LS')
            ->get();

        $totalPrice = $billings->sum('total_amount');

        $websystem = Websystem::firstWhere('slug', 'websystem');

        $customer         = $billing->customer;
        $currentPeriode   = $billing->billingSubscription?->billing_periode;

        $isValidMember = match (true) {
            ! $customer                              => false,
            $customer->billing_option === 1          => true,
            $customer->billing_option === 2
                && $customer->billing_period === $currentPeriode
            => true,
            default                                  => false,
        };

        $memberDisplay = $isValidMember
            ? ($customer->member?->type ?? 'Non Member')
            : 'Non Member';

        $discount = $isValidMember
            ? ($customer->member?->discount ?? 0)
            : 0;

        return view('billings.show', [
            'billing'       => $billing,
            'billings'      => $billings,
            'totalPrice'    => $totalPrice,
            'websystem'     => $websystem,
            'memberDisplay' => $memberDisplay,
            'discount'      => $discount,
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
        $timestamp = now()->format('Ymd_His');
        $periode = $request->get('periode'); // contoh: "August 2025"
        return Excel::download(new BillingsExport(null, $periode), "Billings_{$periode}_{$timestamp}.xlsx");
    }

    public function exportSelected(Request $request)
    {
        $timestamp = now()->format('Ymd_His');
        $status  = $request->get('status');
        $periode = $request->get('periode');

        $statusMap = [
            'LS' => 'PAID',
            'BL' => 'UNPAID',
            'PL' => 'PAYLATER',
            'NP' => 'NOT ACTIVE PERIOD',
        ];

        $statusCamel = 'All';
        if ($status != null) {
            $statusCamel = Str::title(strtolower($statusMap[$status]));
        }

        return Excel::download(
            new BillingsExport($status, $periode),
            "{$statusCamel}_Billing_{$periode}_{$timestamp}.xlsx"
        );
    }
}
