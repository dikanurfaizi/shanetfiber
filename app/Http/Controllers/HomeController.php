<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Customer;
use App\Models\Mikrotik;
use Illuminate\Http\Request;
use App\Models\Billing\Billing;
use App\Models\AutoIsolir;
use App\Models\Billing\BillingPeriode;
use App\Models\Websystem;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\SystemService;
use App\Services\Routerboard\TestConnectionService;
use Illuminate\Support\Facades\Log;
use App\Helpers\LogHelper;
use App\Helpers\UserAuth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    private TestConnectionService $testConnectionService;
    private SecretService $secretService;
    private SystemService $systemService;
    protected $newVersion;
    protected $version;

    public function __construct(SystemService $systemService, SecretService $secretService, TestConnectionService $testConnectionService)
    {
        $this->testConnectionService = $testConnectionService;
        $this->secretService = $secretService;
        $this->systemService = $systemService;

        $this->middleware('permission:server-edit', ['only' => ['index', 'update']]);
        $this->newVersion = config('app.version');
        $this->version = Websystem::where('id', 1)->value('version');
        if ($this->version == null) {
            $this->version = Websystem::where('slug', 'websystem')->value('version');
        }
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $auth = $this->getUserAuthData();
        $user = $auth['user'];
        $userId = $auth['userId'];

        if ($this->version != $this->newVersion) {
            Log::warning('Version mismatch', ['current_version' => $this->version, 'required_version' => $this->newVersion]);
            return redirect()->route('settings.update.index')->with('error', 'Please update first!');
        }

        // Fetch active Mikrotik routers based on permissions
        $routersEnable = $user->can('view-customers-widget-total') ?
            Mikrotik::where('disabled', 'false')->get() :
            Mikrotik::whereIn('id', Customer::where('user_id', $userId)->pluck('mikrotik_id'))
            ->where('disabled', 'false')
            ->get();

        // Process router stats
        $routerStats = $this->processRouters($routersEnable);
        extract($routerStats);

        // Get Mikrotik list
        $mikrotiks = Mikrotik::orderBy('name')->get();

        // Get customer summary
        $customerSummary = $this->calculateCustomerSummary();

        // Get last billing data
        [$lastBillingPeriode, $lastBillings] = $this->getLastBillingData();

        // Calculate billing summary
        $summaryTotals = $this->calculateBillingSummary($lastBillings);

        // Initialize admin billing summary
        $adminBillingSummary = $user->can('view-billings-widget-total') ?
            $this->getOwnerBillingSummary($lastBillingPeriode->id) : null;

        $isMobile = (new \Jenssegers\Agent\Agent())->isMobile();

        return view('home', array_merge(compact(
            'mikrotiks',
            'routersEnable',
            'onlineRouters',
            'sumCustomersOnline',
            'sumCustomersOffline',
            'sumCustomersIsolir',
            'countRouterOnline',
            'countRouterOffline',
            'lastBillingPeriode',
            'lastBillings',
            'adminBillingSummary'
        ), $summaryTotals, $customerSummary));
    }

    private function getUserAuthData()
    {
        return UserAuth::getUserDetails();
    }

    private function processRouters($routersEnable)
    {
        $auth = $this->getUserAuthData();
        $user = $auth['user'];
        $userId = $auth['userId'];

        $countRouterOnline = $countRouterOffline = $sumCustomersOnline = $sumCustomersOffline = $sumCustomersIsolir = 0;
        $onlineRouters = collect();

        $isolirProfiles = AutoIsolir::whereIn('mikrotik_id', $routersEnable->pluck('id'))->pluck('profile_id', 'mikrotik_id');

        foreach ($routersEnable as $router) {
            $profileIsolir = $isolirProfiles->get($router->id);

            try {
                $this->testConnectionService->checkOnline($router);
                $onlineRouters->push($router);
                $countRouterOnline++;

                // Tentukan query berdasarkan permission
                $customerQuery = Customer::query();
                if ($user->can('view-customers-widget-total')) {
                    $customerQuery->where('mikrotik_id', $router->id)->where('disabled', 'false');
                } else {
                    $customerQuery->where('user_id', $userId)->where('mikrotik_id', $router->id);
                }

                $usernames = $customerQuery->pluck('username');
                $activeSecrets = collect($this->secretService->getPppActive($router))->pluck('name');
                $activeSecretsForUser = $activeSecrets->intersect($usernames);

                $sumCustomersOnline += $activeSecretsForUser->count();
                $sumCustomersOffline += $usernames->diff($activeSecretsForUser)->count();

                $countIsolir = 0;
                if ($profileIsolir) {
                    $secrets = $this->secretService->getSecretByProfile($router, $profileIsolir);
                    if ($secrets !== 'error') {
                        $countIsolir = collect($secrets)
                            ->filter(fn($secret) => $usernames->contains($secret['name']) && $secret['disabled'] === 'false')
                            ->count();
                    }
                }

                $sumCustomersIsolir += $countIsolir;
            } catch (\Exception $e) {
                $countRouterOffline++;
                LogHelper::logError(null, [
                    'mikrotik_id' => $router->id,
                    'error_message' => $e->getMessage(),
                ], 500, 'Router connection failed');
            }
        }

        return compact(
            'onlineRouters',
            'countRouterOnline',
            'countRouterOffline',
            'sumCustomersOnline',
            'sumCustomersOffline',
            'sumCustomersIsolir'
        );
    }

    private function getOwnerBillingSummary($billingPeriodeId)
    {
        // Jika Super Admin, ambil semua billing
        return Billing::with(['customer', 'customer.user'])
            ->where('billing_periode_id', $billingPeriodeId)
            ->get()
            ->groupBy(fn($b) => $b->customer->user->id ?? 'Not Linked')
            ->map(function ($group, $adminId) {
                return [
                    'admin_id' => $adminId,
                    'admin_name' => $group->first()->customer->user->name ?? 'Not Linked User',
                    'total_customers' => $group->unique('customer_id')->count(),
                    'total_billing' => $group->sum('paket_price'),
                    'total_paid' => $group->where('status', 'LS')->sum('paket_price'),
                    'total_paylater' => $group->where('status', 'PL')->sum('paket_price'),
                    'total_unpaid' => $group->where('status', 'BL')->sum('paket_price'),
                    'total_paid_customers' => $group->where('status', 'LS')->unique('customer_id')->count(),
                    'total_paylater_customers' => $group->where('status', 'PL')->unique('customer_id')->count(),
                    'total_unpaid_customers' => $group->where('status', 'BL')->unique('customer_id')->count(),
                ];
            });
    }

    private function getMonthlyBillingSummaryTotal()
    {
        return Billing::with('billing_periode')
            ->selectRaw("
            billing_periode_id,
            COUNT(DISTINCT id) as total_customers,
            SUM(CASE WHEN status IN ('LS', 'BL', 'PL') THEN paket_price ELSE 0 END) as total_nominal,
            SUM(CASE WHEN status = 'LS' THEN paket_price ELSE 0 END) as total_paid,
            SUM(CASE WHEN status IN ('BL', 'PL') THEN paket_price ELSE 0 END) as total_unpaid
        ")
            ->groupBy('billing_periode_id')
            ->get()
            ->map(fn($item) => [
                'month_name'      => $item->billing_periode->name,
                'total_customers' => $item->total_customers,
                'total_nominal'   => $item->total_nominal,
                'total_paid'      => $item->total_paid,
                'total_unpaid'    => $item->total_unpaid,
            ]);
    }

    private function getMonthlyBillingSummaryRelated($userId)
    {
        return Billing::with('billing_periode')
            ->selectRaw("
            billing_periode_id,
            COUNT(DISTINCT id) as total_customers,
            SUM(CASE WHEN status IN ('LS', 'BL', 'PL') THEN paket_price ELSE 0 END) as total_nominal,
            SUM(CASE WHEN status = 'LS' THEN paket_price ELSE 0 END) as total_paid,
            SUM(CASE WHEN status IN ('BL', 'PL') THEN paket_price ELSE 0 END) as total_unpaid
        ")
            ->whereHas('customer.user', fn($q) => $q->where('id', $userId))
            ->groupBy('billing_periode_id')
            ->get()
            ->map(fn($item) => [
                'month_name'      => $item->billing_periode->name,
                'total_customers' => $item->total_customers,
                'total_nominal'   => $item->total_nominal,
                'total_paid'      => $item->total_paid,
                'total_unpaid'    => $item->total_unpaid,
            ]);
    }

    private function getMonthlyCustomerCountForCashier()
    {
        return Billing::with('billing_periode')
            ->selectRaw("
            billing_periode_id,
            COUNT(DISTINCT customer_id) as total_customers,
            SUM(CASE WHEN status = 'LS' THEN 1 ELSE 0 END) as total_paid,
            SUM(CASE WHEN status = 'PL' THEN 1 ELSE 0 END) as total_paylater,
            SUM(CASE WHEN status = 'BL' THEN 1 ELSE 0 END) as total_unpaid
        ")
            ->groupBy('billing_periode_id')
            ->get()
            ->map(fn($item) => [
                'month_name'      => $item->billing_periode->name,
                'total_customers' => (int) $item->total_customers,
                'total_paid'      => (int) $item->total_paid,
                'total_paylater'  => (int) $item->total_paylater,
                'total_unpaid'    => (int) $item->total_unpaid,
            ]);
    }

    private function getLastBillingData()
    {
        $auth = $this->getUserAuthData();
        $user = $auth['user'];
        $userId = $auth['userId'];

        $lastBillingPeriode = BillingPeriode::latest('created_at')->first();

        $billingQuery = Billing::where('billing_periode_id', $lastBillingPeriode->id);

        if ($user->can('view-billings-widget-total') || $user->can('view-billings-widget-customer-count')) {
        } elseif ($user->can('view-billings-widget-related')) {
            $billingQuery->whereHas('customer.user', function ($query) use ($userId) {
                $query->where('id', $userId);
            });
        }
        // Default: filter berdasarkan user_id
        else {
            $billingQuery->whereHas('customer.user', function ($query) use ($userId) {
                $query->where('id', $userId);
            });
        }

        $lastBillings = $billingQuery->get();

        return [$lastBillingPeriode, $lastBillings];
    }

    // private function calculateCustomerSummary()
    // {
    //     $auth = $this->getUserAuthData();
    //     $user = $auth['user'];
    //     $userId = $auth['userId'];

    //     $query = Customer::query();

    //     if ($user->can('view-billings-widget-total') || $user->can('view-billings-widget-customer-count')) {
    //     } else if ($user->can('view-billings-widget-related')) {
    //         $query->where('user_id', $userId);
    //     } else {
    //         $query->where('user_id', $userId);
    //     }

    //     $result = $query->selectRaw('
    //     COUNT(*) as totalCustomers,
    //     SUM(CASE WHEN disabled = "false" THEN 1 ELSE 0 END) as totalEnabled,
    //     SUM(CASE WHEN disabled = "true" THEN 1 ELSE 0 END) as totalDisabled,
    //     SUM(CASE WHEN disabled = "true" AND activation = "false" THEN 1 ELSE 0 END) as totalNonActiveDisabled
    // ')->first();

    //     return [
    //         'totalCustomers' => (int) $result->totalCustomers,
    //         'totalEnabled' => (int) $result->totalEnabled,
    //         'totalDisabled' => (int) $result->totalDisabled,
    //         'totalNonActiveDisabled' => (int) $result->totalNonActiveDisabled,
    //     ];
    // }

    private function calculateCustomerSummary()
    {
        $auth = $this->getUserAuthData();
        $user = $auth['user'];
        $userId = $auth['userId'];

        $customerQuery = Customer::query();

        if (!($user->can('view-billings-widget-total') || $user->can('view-billings-widget-customer-count'))) {
            $customerQuery->where('user_id', $userId);
        }

        // Ringkasan total keseluruhan
        $total = (clone $customerQuery)->selectRaw('
        COUNT(*) as totalCustomers,
        SUM(CASE WHEN disabled = "false" THEN 1 ELSE 0 END) as totalEnabled,
        SUM(CASE WHEN disabled = "true" THEN 1 ELSE 0 END) as totalDisabled,
        SUM(CASE WHEN disabled = "true" AND activation = "false" THEN 1 ELSE 0 END) as totalNonActiveDisabled
    ')->first();

        $perMikrotik = (clone $customerQuery)
            ->rightJoin('mikrotiks', 'customers.mikrotik_id', '=', 'mikrotiks.id')
            ->select('mikrotiks.id', 'mikrotiks.name as mikrotik_name', DB::raw('COUNT(customers.id) as total_customers'))
            ->groupBy('mikrotiks.id', 'mikrotiks.name')
            ->orderBy('mikrotiks.id') // Urutkan berdasarkan id Mikrotik
            ->get();

        return [
            'totalCustomers' => (int) $total->totalCustomers,
            'totalEnabled' => (int) $total->totalEnabled,
            'totalDisabled' => (int) $total->totalDisabled,
            'totalNonActiveDisabled' => (int) $total->totalNonActiveDisabled,
            'perMikrotik' => $perMikrotik, // bisa jadi array atau collection
        ];
    }


    private function calculateBillingSummary($billings)
    {
        $auth = $this->getUserAuthData();
        $user = $auth['user'];

        try {
            if ($user->can('view-billings-widget-customer-count') || !$billings) {
                return [
                    'totalBilling' => null,
                    'totalPaid' => null,
                    'totalPaylater' => null,
                    'totalUnpaid' => null,
                ];
            }

            return [
                'totalBilling' => $billings->sum('paket_price'),
                'totalPaid' => $billings->where('status', 'LS')->sum('paket_price'),
                'totalPaylater' => $billings->where('status', 'PL')->sum('paket_price'),
                'totalUnpaid' => $billings->where('status', 'BL')->sum('paket_price'),
            ];
        } catch (\Exception $e) {
            LogHelper::logError(null, [
                'billings' => $billings instanceof \Illuminate\Support\Collection ? $billings->toArray() : [],
                'error_message' => $e->getMessage(),
            ], 500, 'Error calculating billing totals');
            return [
                'totalBilling' => 0,
                'totalPaid' => 0,
                'totalPaylater' => 0,
                'totalUnpaid' => 0,
            ];
        }
    }

    public function getBillingMonthly()
    {
        $auth = $this->getUserAuthData();
        $user = $auth['user'];
        $userId = $auth['userId'];

        // Menentukan billing summary berdasarkan permission
        $billingSummary = match (true) {
            $user->can('view-billings-chart-nominal-total')   => $this->getMonthlyBillingSummaryTotal(),
            $user->can('view-billings-chart-nominal-related') => $this->getMonthlyBillingSummaryRelated($userId),
            $user->can('view-billings-chart-customer-count')  => $this->getMonthlyCustomerCountForCashier(),
            default => collect(),
        };

        // Kirimkan hanya permissions yang relevan
        $permissions = [];
        if ($user->can('view-billings-chart-nominal-total')) {
            $permissions[] = 'view-billings-chart-nominal-total';
        }
        if ($user->can('view-billings-chart-nominal-related')) {
            $permissions[] = 'view-billings-chart-nominal-related';
        }
        if ($user->can('view-billings-chart-customer-count')) {
            $permissions[] = 'view-billings-chart-customer-count';
        }

        return response()->json([
            'billingSummary' => $billingSummary,
            'permissions' => $permissions, // Kirim hanya permissions yang dibutuhkan
        ]);
    }
}
