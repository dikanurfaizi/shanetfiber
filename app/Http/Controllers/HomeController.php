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
        if ($this->version != $this->newVersion) {
            return redirect()->route('settings.update.index')->with('error', 'Please update first!');
        }

        $roles = UserAuth::getRole();
        $isAdmin = UserAuth::isAdmin();
        $userId = UserAuth::getUserId();
        $userDetails = UserAuth::getUserDetails();
        $user = $userDetails['user'];

        // Ambil router yang aktif, dan filter jika bukan admin
        $routersEnable = Mikrotik::where('disabled', 'false')->get();

        if (!$isAdmin) {
            $routersEnable = $routersEnable->filter(
                fn($router) =>
                Customer::where('mikrotik_id', $router->id)->where('user_id', $userId)->exists()
            );
        }

        // Proses router dan dapatkan statistik
        $routerStats = $this->processRouters($routersEnable, $roles, $userId);
        extract($routerStats); // Extract ke dalam variabel seperti $sumCustomersOnline, dsb.

        $mikrotiks = Mikrotik::orderBy('name')->get();
        $customers = $isAdmin ? Customer::all() : $user->customers;

        [$lastBillingPeriode, $lastBillings] = $this->getLastBillingData($userId, $roles);
        $summaryTotals = $this->calculateBillingSummary($lastBillings);

        $adminBillingSummary = $isAdmin ? $this->getAdminBillingSummary($lastBillingPeriode->id) : null;

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
            'customers',
            'lastBillingPeriode',
            'lastBillings',
            'adminBillingSummary',
        ), $summaryTotals));
    }

    private function processRouters($routersEnable, $roles, $userId)
    {
        $countRouterOnline = $countRouterOffline = $sumCustomersOnline = $sumCustomersOffline = $sumCustomersIsolir = 0;
        $onlineRouters = collect();
        $isAdmin = $roles->contains('Admin');

        foreach ($routersEnable as $router) {
            $profileIsolir = AutoIsolir::where('mikrotik_id', $router->id)->value('profile_id');

            try {
                $this->testConnectionService->checkOnline($router);
                $onlineRouters->push($router);
                $countRouterOnline++;

                $customerQuery = Customer::where('mikrotik_id', $router->id)->where('disabled', 'false');
                if (!$isAdmin) {
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

    private function getAdminBillingSummary($billingPeriodeId)
    {
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

    private function getLastBillingData($userId, $roles)
    {
        $lastBillingPeriode = BillingPeriode::latest('created_at')->first();

        if (!$lastBillingPeriode) {
            return [null, collect()];
        }

        $billingQuery = Billing::where('billing_periode_id', $lastBillingPeriode->id);

        if (!$roles->contains('Admin')) {
            $billingQuery->whereHas('customer.user', function ($query) use ($userId) {
                $query->where('id', $userId);
            });
        }

        $lastBillings = $billingQuery->get();

        return [$lastBillingPeriode, $lastBillings];
    }

    private function calculateBillingSummary($billings)
    {
        try {
            $totalBilling = (clone $billings)->sum('paket_price') ?? 0;
            $totalPaid = (clone $billings)->where('status', 'LS')->sum('paket_price') ?? 0;
            $totalPayLater = (clone $billings)->where('status', 'PL')->sum('paket_price') ?? 0;
            $totalUnpaid = (clone $billings)->where('status', 'BL')->sum('paket_price') ?? 0;

            return [
                'totalBilling' => $totalBilling,
                'totalPaid' => $totalPaid,
                'totalPaylater' => $totalPayLater,
                'totalUnpaid' => $totalUnpaid,
            ];
        } catch (\Exception $e) {
            Log::error("Error calculating billing totals: " . $e->getMessage(), [
                'exception' => $e
            ]);
            return [
                'totalBilling' => 0,
                'totalPaid' => 0,
                'totalPaylater' => 0,
                'totalUnpaid' => 0,
            ];
        }
    }

    public function getSummaryBillingMonthly()
    {
        $isAdmin = UserAuth::isAdmin();
        $userId = UserAuth::getUserId();
        $billingSummary = $this->getMonthlyBillingSummary($userId, $isAdmin);

        return response()->json($billingSummary);
    }
}
