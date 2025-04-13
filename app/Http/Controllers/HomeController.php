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
        $isPrivileged = $auth['isSuperAdmin'] || $auth['isCashier'];

        if ($this->version != $this->newVersion) {
            return redirect()->route('settings.update.index')->with('error', 'Please update first!');
        }

        if (!($auth['isSuperAdmin'] || $auth['isCashier'])) {
            $routersEnable = Mikrotik::whereIn('id', Customer::where('user_id', $auth['userId'])->pluck('mikrotik_id'))
                ->where('disabled', 'false')
                ->get();
        } else {
            $routersEnable = Mikrotik::where('disabled', 'false')->get();
        }

        // Proses router dan dapatkan statistik
        $routerStats = $this->processRouters($routersEnable, $isPrivileged, $auth['userId']);
        extract($routerStats); // Extract ke dalam variabel seperti $sumCustomersOnline, dsb.

        $mikrotiks = Mikrotik::orderBy('name')->get();
        $customerSummary = $this->calculateCustomerSummary($auth['userId'], $isPrivileged);

        [$lastBillingPeriode, $lastBillings] = $this->getLastBillingData($auth['userId'], $isPrivileged);
        $summaryTotals = $this->calculateBillingSummary($lastBillings, $auth['isCashier']);

        $adminBillingSummary = $this->getAdminBillingSummary($lastBillingPeriode->id, $auth['isSuperAdmin'], $auth['userId']) ?? null;

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
            'adminBillingSummary',
        ), $summaryTotals, $customerSummary));
    }

    private function getUserAuthData()
    {
        $roles = UserAuth::getRole();
        $isSuperAdmin = UserAuth::isSuperAdmin();
        $isAdmin = UserAuth::isAdmin();
        $isCashier = UserAuth::isCashier();
        $userId = UserAuth::getUserId();

        $userDetails = UserAuth::getUserDetails();
        $user = $userDetails['user'];

        return [
            'roles' => $roles,
            'isSuperAdmin' => $isSuperAdmin,
            'isAdmin' => $isAdmin,
            'isCashier' => $isCashier,
            'userId' => $userId,
            'user' => $user,
        ];
    }

    private function processRouters($routersEnable, $isPrivileged, $userId)
    {
        $countRouterOnline = $countRouterOffline = $sumCustomersOnline = $sumCustomersOffline = $sumCustomersIsolir = 0;
        $onlineRouters = collect();

        $isolirProfiles = AutoIsolir::whereIn('mikrotik_id', $routersEnable->pluck('id'))->pluck('profile_id', 'mikrotik_id');

        foreach ($routersEnable as $router) {
            $profileIsolir = $isolirProfiles->get($router->id);

            try {
                $this->testConnectionService->checkOnline($router);
                $onlineRouters->push($router);
                $countRouterOnline++;

                $customerQuery = Customer::where('mikrotik_id', $router->id)->where('disabled', 'false');

                if (!$isPrivileged) {
                    $customerQuery->where('user_id', $userId);
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
                LogHelper::logError(null, [
                    'mikrotik_id' => $router->id,
                    'error_message' => $e->getMessage(),
                ], 500, 'Router connection failed');
                $countRouterOffline++;
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

    private function getAdminBillingSummary($billingPeriodeId, $isSuperAdmin, $userId)
    {
        // Jika Super Admin, ambil semua billing
        if ($isSuperAdmin) {
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

        // Jika Admin, ambil billing hanya untuk customer miliknya
        return Billing::with(['customer', 'customer.user'])
            ->where('billing_periode_id', $billingPeriodeId)
            ->whereHas('customer.user', function ($query) use ($userId) {
                $query->where('id', $userId);
            })
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

    private function getMonthlyBillingSummary($userId, $isAdmin)
    {
        $query = Billing::with('billing_periode')
            ->selectRaw("
            billing_periode_id,
            COUNT(DISTINCT id) as total_customers,
            SUM(CASE WHEN status IN ('LS', 'BL', 'PL') THEN paket_price ELSE 0 END) as total_nominal,
            SUM(CASE WHEN status = 'LS' THEN paket_price ELSE 0 END) as total_paid,
            SUM(CASE WHEN status IN ('BL', 'PL') THEN paket_price ELSE 0 END) as total_unpaid
        ");

        Log::info("Roles: {$isAdmin}");

        if (!$isAdmin) {
            $query->whereHas('customer.user', fn($q) => $q->where('id', $userId));
        }

        return $query->groupBy('billing_periode_id')->get()->map(function ($item) {
            return [
                'month_name' => $item->billing_periode->name,
                'total_customers' => $item->total_customers,
                'total_nominal' => $item->total_nominal,
                'total_paid' => $item->total_paid,
                'total_unpaid' => $item->total_unpaid,
            ];
        });
    }

    private function getMonthlyCustomerCountForCashier($isCashier)
    {
        if (!$isCashier) {
            return collect();  // Bukan kasir, return kosong.
        }

        $query = Billing::with('billing_periode')
            ->selectRaw("
            billing_periode_id,
            COUNT(DISTINCT customer_id) as total_customers,
            SUM(CASE WHEN status = 'LS' THEN 1 ELSE 0 END) as total_paid,
            SUM(CASE WHEN status = 'PL' THEN 1 ELSE 0 END) as total_paylater,
            SUM(CASE WHEN status = 'BL' THEN 1 ELSE 0 END) as total_unpaid
        ")
            ->groupBy('billing_periode_id')
            ->get();

        return $query->map(function ($item) {
            return [
                'month_name' => $item->billing_periode->name,
                'total_customers' => (int) $item->total_customers,
                'total_paid' => (int) $item->total_paid,
                'total_paylater' => (int) $item->total_paylater,
                'total_unpaid' => (int) $item->total_unpaid,
            ];
        });
    }

    private function getLastBillingData($userId, $isPrivileged)
    {
        $lastBillingPeriode = BillingPeriode::latest('created_at')->first();

        if (!$lastBillingPeriode) {
            return [null, collect()];
        }

        $billingQuery = Billing::where('billing_periode_id', $lastBillingPeriode->id);

        if (!$isPrivileged) {
            $billingQuery->whereHas('customer.user', function ($query) use ($userId) {
                $query->where('id', $userId);
            });
        }

        $lastBillings = $billingQuery->get();

        return [$lastBillingPeriode, $lastBillings];
    }

    private function calculateCustomerSummary($userId, $isPrivileged)
    {
        $query = Customer::query();

        if (!$isPrivileged) {
            $query->where('user_id', $userId);
        }

        $result = $query->selectRaw('
        COUNT(*) as totalCustomers,
        SUM(CASE WHEN disabled = "false" THEN 1 ELSE 0 END) as totalEnabled,
        SUM(CASE WHEN disabled = "true" THEN 1 ELSE 0 END) as totalDisabled,
        SUM(CASE WHEN disabled = "true" AND activation = "false" THEN 1 ELSE 0 END) as totalNonActiveDisabled
    ')->first();

        return [
            'totalCustomers' => (int) $result->totalCustomers,
            'totalEnabled' => (int) $result->totalEnabled,
            'totalDisabled' => (int) $result->totalDisabled,
            'totalNonActiveDisabled' => (int) $result->totalNonActiveDisabled,
        ];
    }

    private function calculateBillingSummary($billings, $isCashier)
    {
        try {
            if ($isCashier) {
                return [
                    'totalBilling' => null,
                    'totalPaid' => null,
                    'totalPaylater' => null,
                    'totalUnpaid' => null,
                ];
            }

            if (!$billings) {
                return [
                    'totalBilling' => 0,
                    'totalPaid' => 0,
                    'totalPaylater' => 0,
                    'totalUnpaid' => 0,
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

        $userId = $auth['userId'];
        $isAdmin = $auth['isAdmin'];
        $isSuperAdmin = $auth['isSuperAdmin'];
        $isCashier = $auth['isCashier'];

        $billingSummary = [];

        if ($isSuperAdmin || $isAdmin) {
            $billingSummary = $this->getMonthlyBillingSummary($userId, !$isSuperAdmin);
            // Kalo superadmin => !$isSuperAdmin = false -> tampil semua
            // Kalo admin => !$isSuperAdmin = true -> filter customer miliknya
        } else {
            $billingSummary = $this->getMonthlyCustomerCountForCashier(true);
        }

        // Log::info('Billing Summary:', ['summary' => $billingSummary]);

        return response()->json([
            'isSuperAdmin' => $isSuperAdmin,
            'isAdmin' => $isAdmin,
            'isCashier' => $isCashier,
            'billingSummary' => $billingSummary
        ]);
    }
}
