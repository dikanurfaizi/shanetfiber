<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Customer;
use App\Models\Mikrotik;
use Illuminate\Http\Request;
use App\Models\Billing\Billing;
use App\Models\Billing\BillingPeriode;
use App\Models\Websystem;
use App\Models\User;
use App\Services\Dashboard\DashboardService;
use Illuminate\Support\Facades\Log;
use App\Helpers\LogHelper;
use App\Helpers\UserAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    private DashboardService $dashboardService;
    protected $newVersion;
    protected $version;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;

        $this->middleware('permission:server-edit', ['only' => ['index', 'update']]);
        $this->newVersion = config('app.version');
        $this->version = Websystem::where('id', 1)->value('version');
        if ($this->version == null) {
            $this->version = Websystem::where('slug', 'websystem')->value('version');
        }
    }

    private function getUserAuthData()
    {
        return UserAuth::getUserDetails();
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
        $traceId = (string) Str::uuid();
        $auth = $this->getUserAuthData();
        $user = $auth['user'];
        $userId = $auth['userId'];

        if ($this->version != $this->newVersion) {
            Log::warning('Version mismatch', ['current_version' => $this->version, 'required_version' => $this->newVersion]);
            return redirect()->route('settings.update.index')->with('error', 'Please update first!');
        }

        // Base query sesuai permission
        $baseQuery = $user->can('view-customers-widget-total')
            ? Mikrotik::query()
            : Mikrotik::whereIn('id', Customer::where('user_id', $userId)->pluck('mikrotik_id'));

        // Ambil semua routers (aktif + disabled)
        $mikrotiks = $baseQuery->orderBy('name')->get();

        // Hitungan
        $totalRouters  = $mikrotiks->count();
        $routerEnabled  = $mikrotiks->where('disabled', 'false')->count();
        $routerDisabled = $totalRouters - $routerEnabled;

        // Get customer summary
        $customerSummary = $this->calculateCustomerSummary();

        // Get package summary
        $packageSummary = $this->calculatePackageSummary();

        // Get last billing data
        [$lastBillingPeriode, $lastBillings] = $this->getLastBillingData($traceId);

        // Calculate billing summary
        $summaryTotals = $this->calculateBillingSummary($lastBillings, $traceId);

        // Initialize admin billing summary
        $adminBillingSummary = null;

        if ($user && $user->can('view-billings-widget-total') && $lastBillingPeriode) {
            $adminBillingSummary = $this->getOwnerBillingSummary($lastBillingPeriode->id);
        } else if (!$lastBillingPeriode) {
            LogHelper::logWarning(null, ['user_id' => $user->id], 404, 'No last billing period found for admin summary', $traceId);
        }

        $movementData = $this->calculateCustomerRegistrationChurnData($request, $traceId);

        extract($movementData);

        $isMobile = (new \Jenssegers\Agent\Agent())->isMobile();

        return view('home', array_merge(compact(
            'mikrotiks',
            'totalRouters',
            'routerEnabled',
            'routerDisabled',
            'lastBillingPeriode',
            'lastBillings',
            'adminBillingSummary',
            'packageSummary',
            'months',
            'registrationData',
            'churnData',
            'isSuperAdmin',
        ), $summaryTotals, $customerSummary));
    }

    // private function processRouters($routersEnable, $traceId): void
    // {
    //     $auth = $this->getUserAuthData();
    //     $user = $auth['user'];
    //     $userId = $auth['userId'];

    //     $cacheKey = "dashboard:routers:summary:user:{$userId}";

    //     $countRouterOnline = $countRouterOffline = $sumCustomersOnline = $sumCustomersOffline = $sumCustomersIsolir = 0;
    //     $onlineRouters = collect();

    //     $isolirProfiles = AutoIsolir::whereIn('mikrotik_id', $routersEnable->pluck('id'))->pluck('profile_id', 'mikrotik_id');

    //     foreach ($routersEnable as $router) {
    //         $profileIsolir = $isolirProfiles->get($router->id);

    //         try {
    //             $response = $this->testConnectionService->checkOnline($router, $traceId);

    //             if ($response['status']) {
    //                 $onlineRouters->push([
    //                     'id' => $router->id,
    //                     'host' => $router->host,
    //                     'data' => $response['data'],
    //                 ]);
    //                 $countRouterOnline++;

    //                 // Tentukan query berdasarkan permission
    //                 $customerQuery = Customer::query();
    //                 if ($user->can('view-customers-widget-total')) {
    //                     $customerQuery->where('mikrotik_id', $router->id)->where('disabled', 'false');
    //                 } else {
    //                     $customerQuery->where('user_id', $userId)->where('mikrotik_id', $router->id);
    //                 }

    //                 $usernames = $customerQuery->pluck('username');
    //                 $activeSecrets = collect($this->secretService->getPppActive($router, $traceId))->pluck('name');
    //                 $activeSecretsForUser = $activeSecrets->intersect($usernames);

    //                 $sumCustomersOnline += $activeSecretsForUser->count();
    //                 $sumCustomersOffline += $usernames->diff($activeSecretsForUser)->count();

    //                 $countIsolir = 0;
    //                 if ($profileIsolir) {
    //                     $secrets = $this->secretService->getSecretByProfile($router, $profileIsolir, $traceId);
    //                     if ($secrets !== 'error' && !empty($secrets)) {
    //                         $countIsolir = collect($secrets)
    //                             ->filter(fn($secret) => $usernames->contains($secret['name']) && $secret['disabled'] === 'false')
    //                             ->count();
    //                     }
    //                 }

    //                 $sumCustomersIsolir += $countIsolir;
    //             } else {
    //                 $countRouterOffline++;
    //             }
    //         } catch (\Exception $e) {
    //             $countRouterOffline++;
    //             LogHelper::logError(null, [
    //                 'mikrotik_id' => $router->id,
    //                 'error_message' => $e->getMessage(),
    //             ], 500, 'Router connection failed', $traceId);
    //         }
    //     }

    //     // simpan ke cache langsung, nggak perlu return
    //     Cache::put($cacheKey, [
    //         'onlineRouters' => $onlineRouters,
    //         'countRouterOnline' => $countRouterOnline,
    //         'countRouterOffline' => $countRouterOffline,
    //         'sumCustomersOnline' => $sumCustomersOnline,
    //         'sumCustomersOffline' => $sumCustomersOffline,
    //         'sumCustomersIsolir' => $sumCustomersIsolir,
    //     ], now()->addMinutes(10));
    // }

    private function getOwnerBillingSummary(int $billingPeriodeId)
    {
        // Subquery ambil billing terbaru per customer untuk periode tertentu
        $latestBillingsSub = DB::table('billings as b2')
            ->join('billing_subscription_periodes as bsp2', 'b2.billing_subscription_id', '=', 'bsp2.id')
            ->where('bsp2.billing_periode_id', $billingPeriodeId)
            ->select('b2.*', 'bsp2.billing_periode_id')
            ->whereRaw('b2.id = (
            select max(b3.id)
            from billings b3
            where b3.customer_id = b2.customer_id
        )');

        // Query utama join users, customers, billing terbaru dan periode
        $users = DB::table('users as u')
            ->leftJoin('customers as c', function ($join) {
                $join->on('c.user_id', '=', 'u.id')
                    ->whereNull('c.deleted_at');
            })
            ->leftJoinSub($latestBillingsSub, 'b', function ($join) {
                $join->on('b.customer_id', '=', 'c.id');
            })
            ->leftJoin('billing_periodes as bp', 'bp.id', '=', 'b.billing_periode_id')
            ->select(
                'u.id as admin_id',
                'u.name as admin_name',
                'b.billing_periode_id',
                'bp.name as periode_name',
                DB::raw('COUNT(DISTINCT b.customer_id) as total_customers'),
                DB::raw('COALESCE(SUM(b.total_amount), 0) as total_billing'),
                DB::raw('COALESCE(SUM(CASE WHEN b.status = "LS" THEN b.total_amount ELSE 0 END), 0) as total_paid'),
                DB::raw('COALESCE(SUM(CASE WHEN b.status = "PL" THEN b.total_amount ELSE 0 END), 0) as total_paylater'),
                DB::raw('COALESCE(SUM(CASE WHEN b.status IN ("BL", "NP") THEN b.total_amount ELSE 0 END), 0) as total_unpaid'),
                DB::raw('COUNT(DISTINCT CASE WHEN b.status = "LS" THEN b.customer_id END) as total_paid_customers'),
                DB::raw('COUNT(DISTINCT CASE WHEN b.status = "PL" THEN b.customer_id END) as total_paylater_customers'),
                DB::raw('COUNT(DISTINCT CASE WHEN b.status IN ("BL", "NP") THEN b.customer_id END) as total_unpaid_customers')
            )
            ->groupBy('u.id', 'u.name', 'b.billing_periode_id', 'bp.name')
            ->havingRaw('COUNT(DISTINCT b.customer_id) > 0')
            ->orderByDesc('total_customers')
            ->get();

        return $users;
    }

    private function getMonthlyBillingSummaryTotal()
    {
        return DB::table('billings as b')
            ->join('billing_subscription_periodes as bsp', 'b.billing_subscription_id', '=', 'bsp.id')
            ->join('billing_periodes as bp', 'bp.id', '=', 'bsp.billing_periode_id')
            ->whereBetween('bp.created_at', [
                DB::raw("STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-01-01'), '%Y-%m-%d')"), // Januari tahun ini
                now() // hari ini
            ])
            ->select(
                'bsp.billing_periode_id',
                'bp.name as month_name',
                DB::raw('COUNT(DISTINCT b.customer_id) as total_customers'),
                DB::raw("SUM(CASE WHEN b.status IN ('LS', 'BL', 'PL') THEN b.total_amount ELSE 0 END) as total_nominal"),
                DB::raw("SUM(CASE WHEN b.status = 'LS' THEN b.total_amount ELSE 0 END) as total_paid"),
                DB::raw("SUM(CASE WHEN b.status IN ('BL', 'PL', 'NP') THEN b.total_amount ELSE 0 END) as total_unpaid")
            )
            ->groupBy('bsp.billing_periode_id', 'bp.name')
            ->orderBy('bsp.billing_periode_id')
            ->get()
            ->toArray();
    }

    private function getMonthlyBillingSummaryRelated(int $userId)
    {
        return DB::table('billings as b')
            ->join('billing_subscription_periodes as bsp', 'b.billing_subscription_id', '=', 'bsp.id')
            ->join('billing_periodes as bp', 'bp.id', '=', 'bsp.billing_periode_id')
            ->join('customers as c', 'c.id', '=', 'b.customer_id')
            ->where('c.user_id', $userId)
            ->whereBetween('bp.created_at', [
                DB::raw("STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-01-01'), '%Y-%m-%d')"), // Januari tahun ini
                now() // hari ini
            ])
            ->select(
                'bsp.billing_periode_id',
                'bp.name as month_name',
                DB::raw('COUNT(DISTINCT b.customer_id) as total_customers'),
                DB::raw("SUM(CASE WHEN b.status IN ('LS', 'BL', 'PL') THEN b.total_amount ELSE 0 END) as total_nominal"),
                DB::raw("SUM(CASE WHEN b.status = 'LS' THEN b.total_amount ELSE 0 END) as total_paid"),
                DB::raw("SUM(CASE WHEN b.status IN ('BL', 'PL', 'NP') THEN b.total_amount ELSE 0 END) as total_unpaid")
            )
            ->groupBy('bsp.billing_periode_id', 'bp.name')
            ->orderBy('bsp.billing_periode_id')
            ->get()
            ->toArray();
    }

    private function getMonthlyCustomerCountForCashier()
    {
        return DB::table('billings as b')
            ->join('billing_subscription_periodes as bsp', 'b.billing_subscription_id', '=', 'bsp.id')
            ->select(
                'bsp.billing_periode_id',
                'bsp.billing_periode',
                DB::raw('COUNT(DISTINCT b.customer_id) as total_customers'),
                DB::raw("SUM(CASE WHEN b.status = 'LS' THEN 1 ELSE 0 END) as total_paid"),
                DB::raw("SUM(CASE WHEN b.status = 'PL' THEN 1 ELSE 0 END) as total_paylater"),
                DB::raw("SUM(CASE WHEN b.status IN ('BL', 'NP') THEN 1 ELSE 0 END) as total_unpaid")
            )
            ->groupBy('bsp.billing_periode_id', 'bsp.billing_periode')
            ->get()
            ->toArray();
    }

    private function getLastBillingData($traceId)
    {
        $startTime = microtime(true); // start time

        $auth = $this->getUserAuthData();
        $user = $auth['user'];
        $userId = $auth['userId'];

        $billingPeriode = BillingPeriode::orderBy('created_at', 'desc')->first();

        if (!$billingPeriode) {
            $duration = microtime(true) - $startTime; // lama eksekusi
            LogHelper::logInfo(null, [
                'duration' => $duration,
                'billingPeriode' => $billingPeriode,
                'lastBillings' => collect(),
            ], 200, "Details getLastBillingData (no billing period found)", $traceId, $duration);

            return [null, collect()];
        }

        // Subquery ambil ID billing terbaru per customer di periode terakhir
        $subQuery = Billing::selectRaw('MAX(billings.id)')
            ->join('billing_subscription_periodes as bsp', 'billing_subscription_id', '=', 'bsp.id')
            ->where('bsp.billing_periode_id', $billingPeriode->id)
            ->groupBy('billings.customer_id');

        $billingQuery = Billing::whereIn('id', $subQuery);

        if ($user->can('view-billings-widget-total') || $user->can('view-billings-widget-customer-count')) {
            // akses luas, tanpa filter tambahan
        } else {
            // akses terbatas: hanya billings terkait user
            $billingQuery->whereHas('customer.user', function ($query) use ($userId) {
                $query->where('id', $userId);
            });
        }

        $lastBillings = $billingQuery->get();

        $duration = microtime(true) - $startTime;

        LogHelper::logInfo(null, [
            'duration' => $duration, // durasi eksekusi dalam detik
            'billingPeriode' => $billingPeriode,
            'userId' => $userId,
        ], 200, "Details getLastBillingData", $traceId, $duration);

        return [$billingPeriode, $lastBillings];
    }

    private function calculateCustomerSummary()
    {
        $startTime = microtime(true); // start time
        $auth = $this->getUserAuthData();
        $user = $auth['user'];
        $userId = $auth['userId'];

        $customerQuery = Customer::withTrashed()->newQuery();

        if (!($user->can('view-billings-widget-total') || $user->can('view-billings-widget-customer-count'))) {
            $customerQuery->where('user_id', $userId);
        }

        // Ringkasan total keseluruhan
        $total = (clone $customerQuery)->selectRaw('
            COUNT(*) as totalCustomers,
            SUM(CASE WHEN disabled = "false" AND activation = "true" AND deleted_at IS NULL THEN 1 ELSE 0 END) as totalEnabled,
            SUM(CASE WHEN disabled = "true" AND activation = "false" AND deleted_at IS NULL THEN 1 ELSE 0 END) as totalDisabled,
            SUM(CASE WHEN disabled = "true" AND activation = "false" AND deleted_at IS NOT NULL THEN 1 ELSE 0 END) as totalNonActiveDisabled
        ')->first();

        $totalActiveCustomers = (clone $customerQuery)
            ->where('disabled', 'false')
            ->where('activation', 'true')
            ->whereNull('deleted_at')
            ->count();

        $perMikrotik = DB::table('mikrotiks')
            ->leftJoin('customers', function ($join) use ($user, $userId) {
                $join->on('customers.mikrotik_id', '=', 'mikrotiks.id')
                    ->where('customers.disabled', 'false')
                    ->where('customers.activation', 'true')
                    ->whereNull('customers.deleted_at');

                // Filter user jika bukan admin
                if (!($user->can('view-billings-widget-total') || $user->can('view-billings-widget-customer-count'))) {
                    $join->where('customers.user_id', $userId);
                }
            })
            ->select('mikrotiks.id', 'mikrotiks.name as mikrotik_name', DB::raw('COUNT(customers.id) as total_customers'))
            ->groupBy('mikrotiks.id', 'mikrotiks.name')
            ->orderBy('mikrotiks.id')
            ->get();

        return [
            'totalCustomers' => (int) $total->totalCustomers,
            'totalActiveCustomers' => (int) $totalActiveCustomers,
            'totalEnabled' => (int) $total->totalEnabled,
            'totalDisabled' => (int) $total->totalDisabled,
            'totalNonActiveDisabled' => (int) $total->totalNonActiveDisabled,
            'perMikrotik' => $perMikrotik, // bisa jadi array atau collection
        ];
    }

    private function calculatePackageSummary()
    {
        $startTime = microtime(true); // start time
        $auth = $this->getUserAuthData();
        $user = $auth['user'];
        $userId = $auth['userId'];

        // Base query untuk menghitung jumlah customer per paket
        $query = Customer::query()
            ->select('pakets.id', 'pakets.name as paket_name', DB::raw('COUNT(customers.id) as total_customers'))
            ->join('pakets', 'customers.paket_id', '=', 'pakets.id')
            ->whereNull('customers.deleted_at')
            ->groupBy('pakets.id', 'pakets.name')
            ->orderByDesc('total_customers');

        // Filter berdasarkan role/permission
        if ($user->can('view-customer-registration-chart')) {
            // Superadmin: tidak ada filter tambahan
        } elseif ($user->can('view-customer-churn-chart')) {
            $query->where('customers.user_id', $userId);
        } else {
            // Tidak punya akses
            return collect();
        }

        return $query->get();
    }

    private function calculateCustomerRegistrationChurnData($request, $traceId): array
    {
        $startTime = microtime(true); // start time
        $auth = $this->getUserAuthData();
        $user = $auth['user'];
        $userId = $auth['userId'];
        $userName = $auth['userName'];

        $isSuperAdmin = $user->can('view-customer-registration-chart');
        $months = collect();
        $registrationData = [];
        $churnData = [];

        $startDate = now()->subMonths(11)->startOfMonth();
        $endDate = now()->endOfMonth();

        $current = $startDate->copy();
        while ($current <= $endDate) {
            $months->push($current->format('M Y'));
            $current->addMonth();
        }

        if ($isSuperAdmin) {
            $admins = User::where('disabled', 'false')->get();
            foreach ($admins as $admin) {
                $adminId = $admin->id;
                $registrationData[$adminId] = [
                    'adminName' => $admin->name,
                    'counts' => []
                ];
                $churnData[$adminId] = ['counts' => []];

                $current = $startDate->copy();
                while ($current <= $endDate) {
                    $monthStart = $current->startOfMonth();
                    $monthEnd = $current->copy()->endOfMonth();

                    $registrationCount = Customer::where('user_id', $adminId)
                        ->whereBetween('created_at', [$monthStart, $monthEnd])
                        ->count();

                    $churnCount = Customer::where('user_id', $adminId)
                        ->where(function ($q) {
                            $q->where('disabled', true)
                                ->orWhereNotNull('deleted_at');
                        })
                        ->whereBetween('deleted_at', [$monthStart, $monthEnd])
                        ->count();

                    $registrationData[$adminId]['counts'][] = $registrationCount;
                    $churnData[$adminId]['counts'][] = $churnCount;
                    $current->addMonth();
                }
            }
        } else {
            $adminId = $userId;
            $registrationData[$adminId] = ['adminName' => $userName, 'counts' => []];
            $churnData[$adminId] = ['counts' => []];

            $current = $startDate->copy();
            while ($current <= $endDate) {
                $monthStart = $current->startOfMonth();
                $monthEnd = $current->copy()->endOfMonth();

                $registrationCount = Customer::where('user_id', $adminId)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->count();

                $churnCount = Customer::where('user_id', $adminId)
                    ->where(function ($q) {
                        $q->where('disabled', true)
                            ->orWhereNotNull('deleted_at');
                    })
                    ->whereBetween('deleted_at', [$monthStart, $monthEnd])
                    ->count();

                $registrationData[$adminId]['counts'][] = $registrationCount;
                $churnData[$adminId]['counts'][] = $churnCount;
                $current->addMonth();
            }
        }

        return [
            'months' => $months,
            'registrationData' => $registrationData,
            'churnData' => $churnData,
            'isSuperAdmin' => $isSuperAdmin,
        ];
    }

    private function calculateBillingSummary($billings, $traceId)
    {
        $startTime = microtime(true); // start time
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
                'totalBilling' => $billings->sum('total_amount'),
                'totalPaid' => $billings->where('status', 'LS')->sum('total_amount'),
                'totalPaylater' => $billings->where('status', 'PL')->sum('total_amount'),
                'totalUnpaid' => $billings->whereIn('status', ['BL', 'NP'])->sum('total_amount'),
            ];
        } catch (\Exception $e) {
            LogHelper::logError(null, [
                'billings' => $billings instanceof \Illuminate\Support\Collection ? $billings->toArray() : [],
                'error_message' => $e->getMessage(),
            ], 500, 'Error calculating billing totals', $traceId);
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
        $startTime = microtime(true); // start time
        $auth = $this->getUserAuthData();
        $user = $auth['user'];
        $userId = $auth['userId'];

        $billingPeriode = BillingPeriode::orderBy('created_at', 'desc')->first();

        // Menentukan billing summary berdasarkan permission
        $billingSummary = match (true) {
            $user->can('view-billings-chart-nominal-total')   => $this->getMonthlyBillingSummaryTotal($billingPeriode->id),
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

    public function getCustomerRegistrationChurnData(Request $request)
    {
        $startTime = microtime(true); // start time
        $traceId = (string) Str::uuid();
        try {
            return response()->json($this->calculateCustomerRegistrationChurnData($request, $traceId));
        } catch (\Exception $e) {
            logHelper::logError(null, [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500, 'ERROR getCustomerRegistrationChurnData');
            return response()->json(['error' => 'Failed to fetch data'], $traceId);
        }
    }

    public function triggerDashboard(Request $request)
    {
        $auth = $this->getUserAuthData();
        $userId = $auth['userId'];
        $traceId = (string) Str::uuid();

        $routersEnable = Mikrotik::where('disabled', 'false')->get();

        // langsung jalankan proses & simpan ke cache
        $this->dashboardService->refreshRoutersSummary($routersEnable, $traceId, $userId);

        return response()->json([
            'success' => true,
            'message' => 'Dashboard refresh triggered, please wait a moment.'
        ]);
    }

    public function getDashboardCache(Request $request)
    {
        $auth = $this->getUserAuthData();
        $userId = $auth['userId'];

        $cacheKey = "dashboard:routers:summary:user:{$userId}";

        $data = Cache::get($cacheKey, [
            'onlineRouters' => [],
            'countRouterOnline' => 0,
            'countRouterOffline' => 0,
            'sumCustomersOnline' => 0,
            'sumCustomersOffline' => 0,
            'sumCustomersIsolir' => 0,
        ]);

        return response()->json($data);
    }
}
