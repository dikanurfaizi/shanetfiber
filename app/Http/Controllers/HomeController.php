<?php

namespace App\Http\Controllers;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Helpers\UserAuth;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingPeriode;
use App\Models\Customers\Customer;
use App\Models\InvestorAdmin;
use App\Models\Mikrotik;
use App\Models\User;
use App\Models\Websystem;
use App\Services\Auth\CustomerAuthService;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    private DashboardService $dashboardService;

    protected $newVersion;

    protected $version;

    protected $authService;

    public function __construct(DashboardService $dashboardService, CustomerAuthService $authService)
    {
        $this->authService = $authService;
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

    public function home()
    {
        $data = $this->authService->getLoginData();

        return view('customers.auth.homepage', $data);
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
        $timer = new ExecutionTimer;
        $auth = $this->getUserAuthData();
        $user = $auth['user'];
        $userId = $auth['userId'];

        // ====== Versi Check ======
        if ($this->version != $this->newVersion) {
            LogHelper::logWarning(null, [
                'trace_id' => $traceId,
                'current_version' => $this->version,
                'required_version' => $this->newVersion,
            ], 400, 'Version mismatch', $traceId, $timer->lap());

            return redirect()->route('settings.update.index')
                ->with('error', 'Please update first!');
        }

        // ====== Investor Override Logic ======
        if ($user->can('investor-customer-list')) {
            $linkedAdminIds = InvestorAdmin::where('investor_id', $userId)->pluck('admin_id')->toArray();
            $userIds = array_merge([$userId], $linkedAdminIds);
        } else {
            $userIds = [$userId];
        }

        // ====== Base Query sesuai permission ======
        $baseQuery = $user->can('view-customers-widget-total')
            ? Mikrotik::query()
            : Mikrotik::whereIn('id', Customer::whereIn('user_id', $userIds)->pluck('mikrotik_id'));

        // ====== Ambil semua routers (aktif + disabled) ======
        $mikrotiks = $baseQuery->orderBy('name')->get();

        // ====== Hitungan ======
        $totalRouters = $mikrotiks->count();
        $routerEnabled = $mikrotiks->where('disabled', 'false')->count();
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

        if ($user && $lastBillingPeriode) {
            if ($user->can('investor-billing-widget')) {
                $adminBillingSummary = $this->getOwnerBillingSummary($lastBillingPeriode->id, $user->id);
            } elseif ($user->can('view-billings-widget-total')) {
                $adminBillingSummary = $this->getOwnerBillingSummary($lastBillingPeriode->id);
            }
        } elseif (! $lastBillingPeriode) {
            LogHelper::logWarning(null, ['user_id' => $user->id], 404, 'No last billing period found for admin summary', $traceId, $timer->lap());
        }

        $movementData = $this->calculateCustomerRegistrationChurnData($request, $traceId);
        extract($movementData);

        $isMobile = (new \Jenssegers\Agent\Agent)->isMobile();

        // Pastikan $isSuperAdmin terdefinisi dengan aman
        $isSuperAdmin = $isSuperAdmin ?? $user->can('view-customers-widget-total');

        $limitMonths = 3;

        // 1. Ambil 3 ID periode billing terbaru (3 Bulan Terakhir) dari DB
        $recentPeriodeIds = BillingPeriode::orderBy('id', 'desc')
            ->limit($limitMonths)
            ->pluck('id')
            ->toArray();

        // 2. Selalu select admin_name agar nama admin pemilik customer selalu tersedia di Blade
        $selectColumns = [
            'customers.id',
            'customers.slug',
            'customers.name',
            'customers.contact',
            'customers.user_id',
            'admins.name as admin_name',
        ];

        $query = Customer::query()
            ->join('billings', 'customers.id', '=', 'billings.customer_id')
            ->join('billing_subscription_periodes', 'billing_subscription_periodes.id', '=', 'billings.billing_subscription_id')
            ->join('billing_periodes', 'billing_subscription_periodes.billing_periode_id', '=', 'billing_periodes.id')
            ->leftJoin('users as admins', 'customers.user_id', '=', 'admins.id')
            ->where('customers.is_active', true)  // Pelanggan aktif
            ->where('customers.activation', true)
            ->where('billings.status', 'VD')       // Status tidak dibayar sama sekali (VD)
            ->whereIn('billing_periodes.id', $recentPeriodeIds); // Filter 3 periode (3 bulan) terakhir

        // Filter scope data berdasarkan role user yang login
        if (!$isSuperAdmin) {
            $query->where('customers.user_id', auth()->id());
        }

        $unpaid3MonthsCustomers = $query->select($selectColumns)
            ->selectRaw('COUNT(DISTINCT billings.id) as unpaid_months_count')
            ->selectRaw("GROUP_CONCAT(DISTINCT billing_periodes.name ORDER BY billing_periodes.id DESC SEPARATOR ', ') as unpaid_periods")
            ->groupBy([
                'customers.id',
                'customers.slug',
                'customers.name',
                'customers.contact',
                'customers.user_id',
                'admins.name',
            ])
            // Urutkan dari jumlah bulan tunggakan terbanyak dalam 1 tahun terakhir
            ->orderByDesc('unpaid_months_count')
            ->get();

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
            'isSuperAdmin',
            'unpaid3MonthsCustomers',
            'limitMonths',
        ), $summaryTotals, $customerSummary));
    }

    private function getOwnerBillingSummary(int $billingPeriodeId, ?int $investorId = null)
    {
        // Subquery: Ambil seluruh billing pada periode tertentu tanpa membatasi max ID
        $periodBillingsSub = DB::table('billings as b2')
            ->join('billing_subscription_periodes as bsp2', 'b2.billing_subscription_id', '=', 'bsp2.id')
            ->where('bsp2.billing_periode_id', $billingPeriodeId)
            ->select(
                'b2.id',
                'b2.customer_id',
                'b2.total_amount',
                'b2.status'
            );

        // Base query user + customer + billing
        // Catatan: c.deleted_at IS NULL dilepas agar data VOID dari customer soft-deleted tetap terhitung
        $users = DB::table('users as u')
            ->join('customers as c', 'c.user_id', '=', 'u.id')
            ->leftJoinSub($periodBillingsSub, 'b', function ($join) {
                $join->on('b.customer_id', '=', 'c.id');
            })
            ->leftJoin('billing_periodes as bp', function ($join) use ($billingPeriodeId) {
                $join->on('bp.id', '=', DB::raw($billingPeriodeId));
            });

        // 🔹 Jika ada investor_id, batasi user yang terlihat
        if ($investorId) {
            $users->join('investor_admins as ia', 'ia.admin_id', '=', 'u.id')
                ->where('ia.investor_id', $investorId);
        }

        $users->select(
            'u.id as admin_id',
            'u.name as admin_name',
            DB::raw("$billingPeriodeId as billing_periode_id"),
            'bp.name as periode_name',
            DB::raw('COUNT(DISTINCT c.id) as total_customers'),
            DB::raw('COALESCE(SUM(b.total_amount), 0) as total_billing'),
            DB::raw('COALESCE(SUM(CASE WHEN b.status = "LS" THEN b.total_amount ELSE 0 END), 0) as total_paid'),
            DB::raw('COALESCE(SUM(CASE WHEN b.status = "PL" THEN b.total_amount ELSE 0 END), 0) as total_paylater'),
            DB::raw('COALESCE(SUM(CASE WHEN b.status = "BL" THEN b.total_amount ELSE 0 END), 0) as total_unpaid'),
            DB::raw('COALESCE(SUM(CASE WHEN b.status = "VD" THEN b.total_amount ELSE 0 END), 0) as total_void'),
            DB::raw('COUNT(DISTINCT CASE WHEN b.status = "LS" THEN b.customer_id END) as total_paid_customers'),
            DB::raw('COUNT(DISTINCT CASE WHEN b.status = "PL" THEN b.customer_id END) as total_paylater_customers'),
            DB::raw('COUNT(DISTINCT CASE WHEN b.status = "BL" THEN b.customer_id END) as total_unpaid_customers'),
            DB::raw('COUNT(DISTINCT CASE WHEN b.status = "VD" THEN b.customer_id END) as total_void_customers')
        )
            ->groupBy('u.id', 'u.name', 'bp.name')
            ->havingRaw('COUNT(DISTINCT b.customer_id) > 0')
            ->orderByDesc('total_customers');

        return $users->get();
    }

    private function getMonthlyBillingSummaryTotal()
    {
        return DB::table('billings as b')
            ->join('billing_subscription_periodes as bsp', 'b.billing_subscription_id', '=', 'bsp.id')
            ->join('billing_periodes as bp', 'bp.id', '=', 'bsp.billing_periode_id')
            ->whereBetween('bp.created_at', [
                DB::raw("STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-01-01'), '%Y-%m-%d')"),
                now(),
            ])
            ->select(
                'bsp.billing_periode_id',
                'bp.name as month_name',
                'bp.month as month',
                'bp.year as year',
                DB::raw('COUNT(DISTINCT b.customer_id) as total_customers'),

                // 🔹 Pemisahan Nominal per Status
                DB::raw("SUM(CASE WHEN b.status = 'LS' THEN b.total_amount ELSE 0 END) as total_paid"),
                DB::raw("SUM(CASE WHEN b.status = 'PL' THEN b.total_amount ELSE 0 END) as total_paylater"),
                DB::raw("SUM(CASE WHEN b.status = 'BL' THEN b.total_amount ELSE 0 END) as total_unpaid"),
                DB::raw("SUM(CASE WHEN b.status = 'VD' THEN b.total_amount ELSE 0 END) as total_void"),
                DB::raw('SUM(b.total_amount) as total_nominal')
            )
            // 🔹 DIBERSIHKAN: 'bp.month' dan 'bp.year' WAJIB masuk ke dalam groupBy
            ->groupBy('bsp.billing_periode_id', 'bp.name', 'bp.month', 'bp.year')
            ->orderBy('bsp.billing_periode_id')
            ->get()
            ->toArray();
    }

    private function getMonthlyBillingSummaryRelated(int $userId, bool $isInvestor = false)
    {
        $visibleUserIds = [$userId];

        if ($isInvestor) {
            $linkedAdminIds = DB::table('investor_admins')
                ->where('investor_id', $userId)
                ->pluck('admin_id')
                ->toArray();

            $visibleUserIds = array_unique(array_merge($visibleUserIds, $linkedAdminIds));
        }

        return DB::table('billings as b')
            ->join('billing_subscription_periodes as bsp', 'b.billing_subscription_id', '=', 'bsp.id')
            ->join('billing_periodes as bp', 'bp.id', '=', 'bsp.billing_periode_id')
            ->join('customers as c', 'c.id', '=', 'b.customer_id')
            ->join('users as u', 'u.id', '=', 'c.user_id')
            ->whereIn('c.user_id', $visibleUserIds)
            ->whereBetween('bp.created_at', [
                DB::raw("STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-01-01'), '%Y-%m-%d')"),
                now(),
            ])
            ->select(
                'bsp.billing_periode_id',
                'bp.name as month_name',
                'bp.month as month',
                'bp.year as year',
                'u.id as admin_id',
                'u.name as admin_name',
                DB::raw('COUNT(DISTINCT b.customer_id) as total_customers'),
                DB::raw("SUM(CASE WHEN b.status = 'LS' THEN b.total_amount ELSE 0 END) as total_paid"),
                DB::raw("SUM(CASE WHEN b.status = 'PL' THEN b.total_amount ELSE 0 END) as total_paylater"),
                DB::raw("SUM(CASE WHEN b.status = 'BL' THEN b.total_amount ELSE 0 END) as total_unpaid"),
                DB::raw("SUM(CASE WHEN b.status = 'VD' THEN b.total_amount ELSE 0 END) as total_void"),
                DB::raw('SUM(b.total_amount) as total_nominal')
            )
            // 🔹 DIBERSIHKAN: Masukkan 'bp.month' & 'bp.year' ke groupBy
            ->groupBy('bsp.billing_periode_id', 'bp.name', 'bp.month', 'bp.year', 'u.id', 'u.name')
            ->orderBy('bsp.billing_periode_id')
            ->get()
            ->toArray();
    }

    private function getMonthlyCustomerCountForCashier()
    {
        return DB::table('billings as b')
            ->join('billing_subscription_periodes as bsp', 'b.billing_subscription_id', '=', 'bsp.id')
            ->join('billing_periodes as bp', 'bp.id', '=', 'bsp.billing_periode_id') // 🔹 Tambahkan join ke bp
            ->whereBetween('bp.created_at', [
                DB::raw("STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-01-01'), '%Y-%m-%d')"),
                now(),
            ])
            ->select(
                'bsp.billing_periode_id',
                'bp.name as month_name',
                'bp.month as month',                   // 🔹 Tambahkan month
                'bp.year as year',                     // 🔹 Tambahkan year
                DB::raw('COUNT(DISTINCT b.customer_id) as total_customers'),
                DB::raw("SUM(CASE WHEN b.status = 'LS' THEN b.total_amount ELSE 0 END) as total_paid"),
                DB::raw("SUM(CASE WHEN b.status = 'PL' THEN b.total_amount ELSE 0 END) as total_paylater"),
                DB::raw("SUM(CASE WHEN b.status = 'BL' THEN b.total_amount ELSE 0 END) as total_unpaid"),
                DB::raw("SUM(CASE WHEN b.status = 'VD' THEN b.total_amount ELSE 0 END) as total_void"),
                DB::raw('SUM(b.total_amount) as total_nominal')
            )
            // 🔹 DIBERSIHKAN: Masukkan kolom select ke groupBy
            ->groupBy('bsp.billing_periode_id', 'bp.name', 'bp.month', 'bp.year')
            ->orderBy('bsp.billing_periode_id')
            ->get()
            ->toArray();
    }

    private function getLastBillingData(string $traceId)
    {
        $timer = new ExecutionTimer;

        $auth = $this->getUserAuthData();
        $user = $auth['user'];
        $userId = $auth['userId'];

        $billingPeriode = BillingPeriode::orderBy('created_at', 'desc')->first();

        if (! $billingPeriode) {
            LogHelper::logInfo(null, [
                'billingPeriode' => $billingPeriode,
                'lastBillings' => collect(),
            ], 200, 'Details getLastBillingData (no billing period found)', $traceId, $timer->lap());

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
        } elseif ($user->can('investor-billing-widget')) {
            // akses investor: hanya billings terkait admin yang berelasi
            $linkedAdminIds = InvestorAdmin::where('investor_id', $userId)->pluck('admin_id')->toArray();
            $visibleUserIds = array_merge([$userId], $linkedAdminIds);

            $billingQuery->whereHas('customer.user', function ($query) use ($visibleUserIds) {
                $query->whereIn('id', $visibleUserIds);
            });
        } else {
            // akses terbatas: hanya billings terkait user
            $billingQuery->whereHas('customer.user', function ($query) use ($userId) {
                $query->where('id', $userId);
            });
        }

        $lastBillings = $billingQuery->get();

        LogHelper::logInfo(null, [
            'billingPeriode' => $billingPeriode,
            'userId' => $userId,
        ], 200, 'Details getLastBillingData', $traceId, $timer->lap());

        return [$billingPeriode, $lastBillings];
    }

    private function calculateCustomerSummary()
    {
        $startTime = microtime(true);
        $auth = $this->getUserAuthData();
        $user = $auth['user'];
        $userId = $auth['userId'];

        // 🔹 Bangun base query
        $customerQuery = Customer::withTrashed()->newQuery();

        // 🔹 Tentukan user scope
        if ($user->can('view-billings-widget-total') || $user->can('view-billings-widget-customer-count')) {
            // Admin global: bisa lihat semua
            $scope = 'global';
            $visibleUserIds = null; // tanpa filter user_id
        } elseif ($user->can('investor-billing-widget')) {
            // Investor: lihat admin yang berelasi
            $scope = 'investor';
            $visibleUserIds = array_merge(
                [$userId],
                InvestorAdmin::where('investor_id', $userId)->pluck('admin_id')->toArray()
            );
        } else {
            // User biasa: hanya miliknya
            $scope = 'self';
            $visibleUserIds = [$userId];
        }

        if ($scope !== 'global') {
            $customerQuery->whereIn('user_id', $visibleUserIds);
        }

        // 🔹 Hitung ringkasan total
        $total = (clone $customerQuery)->selectRaw('
        COUNT(*) as totalCustomers,
        SUM(CASE WHEN is_active = 1 AND activation = 1 AND deleted_at IS NULL THEN 1 ELSE 0 END) as totalEnabled,
        SUM(CASE WHEN is_active = 0 AND activation = 0 AND deleted_at IS NULL THEN 1 ELSE 0 END) as totalDisabled,
        SUM(CASE WHEN is_active = 0 AND activation = 0 AND deleted_at IS NOT NULL THEN 1 ELSE 0 END) as totalNonActiveDisabled
    ')->first();

        $totalActiveCustomers = (clone $customerQuery)
            ->where('is_active', true)
            ->where('activation', true)
            ->whereNull('deleted_at')
            ->count();

        // 🔹 Group per Mikrotik
        $perMikrotik = DB::table('mikrotiks')
            ->leftJoin('customers', function ($join) use ($user, $visibleUserIds) {
                $join->on('customers.mikrotik_id', '=', 'mikrotiks.id')
                    ->where('customers.is_active', true)
                    ->where('customers.activation', true)
                    ->whereNull('customers.deleted_at');

                // Filter user scope jika tidak punya izin total view
                if (! ($user->can('view-billings-widget-total') || $user->can('view-billings-widget-customer-count'))) {
                    $join->whereIn('customers.user_id', $visibleUserIds);
                }
            })
            ->select(
                'mikrotiks.id',
                'mikrotiks.name as mikrotik_name',
                DB::raw('COUNT(customers.id) as total_customers')
            )
            ->groupBy('mikrotiks.id', 'mikrotiks.name')
            ->orderBy('mikrotiks.id')
            ->get();

        return [
            'totalCustomers' => (int) $total->totalCustomers,
            'totalActiveCustomers' => (int) $totalActiveCustomers,
            'totalEnabled' => (int) $total->totalEnabled,
            'totalDisabled' => (int) $total->totalDisabled,
            'totalNonActiveDisabled' => (int) $total->totalNonActiveDisabled,
            'perMikrotik' => $perMikrotik,
        ];
    }

    private function calculatePackageSummary()
    {
        $auth = $this->getUserAuthData();
        $user = $auth['user'];
        $userId = $auth['userId'];

        // Base query menggunakan customer_subscription
        $query = Customer::query()
            ->select(
                'packages.id as package_id',
                'packages.name as package_name',
                DB::raw('COUNT(customers.id) as total_customers')
            )
            ->join('customer_subscriptions', 'customer_subscriptions.customer_id', '=', 'customers.id')
            ->join('packages', 'customer_subscriptions.package_id', '=', 'packages.id')
            ->whereNull('customers.deleted_at')
            ->where('customer_subscriptions.status', 'active')   // ⬅ WAJIB! hindari hitung dobel
            ->groupBy('packages.id', 'packages.name')
            ->orderByDesc('total_customers');

        // === ROLE FILTERING ===
        if ($user->can('view-customer-registration-chart')) {
            // superadmin → no filter
        } elseif ($user->can('view-customer-churn-chart')) {
            $query->where('customers.user_id', $userId);
        } elseif ($user->can('investor-billing-chart')) {
            $linkedAdminIds = InvestorAdmin::where('investor_id', $userId)
                ->pluck('admin_id')
                ->toArray();

            $visibleUserIds = array_merge([$userId], $linkedAdminIds);
            $query->whereIn('customers.user_id', $visibleUserIds);
        } else {
            return collect();
        }

        return $query->get();
    }

    // private function calculateCustomerRegistrationChurnData($request, $traceId): array
    // {
    //     $timer = new ExecutionTimer();
    //     $auth = $this->getUserAuthData();
    //     $user = $auth['user'];
    //     $userId = $auth['userId'];
    //     $userName = $auth['userName'];

    //     $isSuperAdmin = $user->can('view-customer-registration-chart');
    //     $isInvestor = $user->can('investor-customer-registration');
    //     $months = collect();
    //     $registrationData = [];
    //     $churnData = [];

    //     $startDate = now()->subMonths(11)->startOfMonth();
    //     $endDate = now()->endOfMonth();

    //     // Bangun list bulan
    //     $current = $startDate->copy();
    //     while ($current <= $endDate) {
    //         $months->push($current->format('M Y'));
    //         $current->addMonth();
    //     }

    //     // 🔹 CASE 1: Super Admin => semua admin
    //     if ($isSuperAdmin) {
    //         $admins = User::where('disabled', 'false')->get();
    //     }
    //     // 🔹 CASE 2: Investor => admin yang berelasi dengan investor
    //     elseif ($isInvestor) {
    //         $linkedAdminIds = DB::table('investor_admins')
    //             ->where('investor_id', $userId)
    //             ->pluck('admin_id')
    //             ->toArray();

    //         // Kalau investor juga ingin melihat data dirinya sendiri (opsional)
    //         $admins = User::whereIn('id', array_merge([$userId], $linkedAdminIds))
    //             ->where('disabled', 'false')
    //             ->get();

    //         LogHelper::logInfo(null, [
    //             'investor_id' => $userId,
    //             'linked_admin_ids' => $linkedAdminIds,
    //             'total_admins' => $admins->count(),
    //         ], 200, 'Investor related admins for registration/churn data', $traceId, $timer->lap());
    //     }
    //     // 🔹 CASE 3: Admin biasa => hanya dirinya sendiri
    //     else {
    //         $admins = User::where('id', $userId)
    //             ->where('disabled', 'false')
    //             ->get();
    //     }

    //     // 🔹 Loop setiap admin untuk hitung data bulanan
    //     foreach ($admins as $admin) {
    //         $adminId = $admin->id;
    //         $registrationData[$adminId] = [
    //             'adminName' => $admin->name,
    //             'counts' => []
    //         ];
    //         $churnData[$adminId] = ['counts' => []];

    //         $current = $startDate->copy();
    //         while ($current <= $endDate) {
    //             $monthStart = $current->copy()->startOfMonth();
    //             $monthEnd = $current->copy()->endOfMonth();

    //             $registrationCount = Customer::where('user_id', $adminId)
    //                 ->whereBetween('created_at', [$monthStart, $monthEnd])
    //                 ->count();

    //             $churnCount = Customer::where('user_id', $adminId)
    //                 ->where(function ($q) {
    //                     $q->where('disabled', true)
    //                         ->orWhereNotNull('deleted_at');
    //                 })
    //                 ->whereBetween('deleted_at', [$monthStart, $monthEnd])
    //                 ->count();

    //             $registrationData[$adminId]['counts'][] = $registrationCount;
    //             $churnData[$adminId]['counts'][] = $churnCount;
    //             $current->addMonth();
    //         }
    //     }

    //     return [
    //         'months' => $months,
    //         'registrationData' => $registrationData,
    //         'churnData' => $churnData,
    //         'isSuperAdmin' => $isSuperAdmin,
    //         'isInvestor' => $isInvestor,
    //     ];
    // }

    private function calculateCustomerRegistrationChurnData($request, $traceId): array
    {
        $timer = new ExecutionTimer;
        $auth = $this->getUserAuthData();
        $user = $auth['user'];
        $userId = $auth['userId'];

        $isSuperAdmin = $user->can('view-customer-registration-chart');
        $isInvestor = $user->can('investor-customer-registration');
        $months = collect();
        $registrationData = [];

        $startDate = now()->subMonths(11)->startOfMonth();
        $endDate = now()->endOfMonth();

        // Bangun list bulan
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $months->push($current->format('M Y'));
            $current->addMonth();
        }

        // 🔹 CASE 1: Super Admin => semua admin
        if ($isSuperAdmin) {
            $admins = User::where('disabled', 'false')->get();
        }
        // 🔹 CASE 2: Investor => admin yang berelasi dengan investor
        elseif ($isInvestor) {
            $linkedAdminIds = DB::table('investor_admins')
                ->where('investor_id', $userId)
                ->pluck('admin_id')
                ->toArray();

            $admins = User::whereIn('id', array_merge([$userId], $linkedAdminIds))
                ->where('disabled', 'false')
                ->get();
        }
        // 🔹 CASE 3: Admin biasa => hanya dirinya sendiri
        else {
            $admins = User::where('id', $userId)
                ->where('disabled', 'false')
                ->get();
        }

        // 🔹 Loop setiap admin untuk hitung data pendaftaran bulanan
        foreach ($admins as $admin) {
            $adminId = $admin->id;
            $registrationData[$adminId] = [
                'adminName' => $admin->name,
                'counts' => [],
            ];

            $current = $startDate->copy();
            while ($current <= $endDate) {
                $monthStart = $current->copy()->startOfMonth();
                $monthEnd = $current->copy()->endOfMonth();

                $registrationCount = Customer::where('user_id', $adminId)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->count();

                $registrationData[$adminId]['counts'][] = $registrationCount;
                $current->addMonth();
            }
        }

        return [
            'months' => $months,
            'registrationData' => $registrationData,
            'isSuperAdmin' => $isSuperAdmin,
            'isInvestor' => $isInvestor,
        ];
    }

    private function calculateBillingSummary($billings, $traceId)
    {
        $timer = new ExecutionTimer;
        $auth = $this->getUserAuthData();
        $user = $auth['user'];

        try {
            if ($user->can('view-billings-widget-customer-count') || ! $billings) {
                return [
                    'totalBilling' => null,
                    'totalPaid' => null,
                    'totalPaylater' => null,
                    'totalUnpaid' => null,
                    'totalVoid' => null,
                ];
            }

            return [
                'totalBilling' => $billings->sum('total_amount'),
                'totalPaid' => $billings->where('status', 'LS')->sum('total_amount'),
                'totalPaylater' => $billings->where('status', 'PL')->sum('total_amount'),
                'totalUnpaid' => $billings->where('status', 'BL')->sum('total_amount'),
                'totalVoid' => $billings->where('status', 'VD')->sum('total_amount'),
            ];
        } catch (\Exception $e) {
            LogHelper::logError(null, [
                'billings' => $billings instanceof \Illuminate\Support\Collection ? $billings->toArray() : [],
                'error_message' => $e->getMessage(),
            ], 500, 'Error calculating billing totals', $traceId, $timer->lap());

            return [
                'totalBilling' => 0,
                'totalPaid' => 0,
                'totalPaylater' => 0,
                'totalUnpaid' => 0,
                'totalVoid' => 0,
            ];
        }
    }

    public function getBillingMonthly()
    {
        $auth = $this->getUserAuthData();
        $user = $auth['user'];
        $userId = $auth['userId'];

        $billingPeriode = BillingPeriode::orderBy('created_at', 'desc')->first();

        // Menentukan billing summary berdasarkan permission
        $billingSummary = match (true) {
            $user->can('view-billings-chart-nominal-total') => $this->getMonthlyBillingSummaryTotal($billingPeriode->id),
            $user->can('view-billings-chart-nominal-related') => $this->getMonthlyBillingSummaryRelated($userId, false),
            $user->can('view-billings-chart-customer-count') => $this->getMonthlyCustomerCountForCashier(),
            $user->can('investor-billing-chart') => $this->getMonthlyBillingSummaryRelated($userId, true),
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
        if ($user->can('investor-billing-chart')) {
            $permissions[] = 'investor-billing-chart';
        }

        return response()->json([
            'billingSummary' => $billingSummary,
            'permissions' => $permissions, // Kirim hanya permissions yang dibutuhkan
        ]);
    }

    public function getCustomerRegistrationChurnData(Request $request)
    {
        $timer = new ExecutionTimer;
        $traceId = (string) Str::uuid();
        try {
            return response()->json($this->calculateCustomerRegistrationChurnData($request, $traceId));
        } catch (\Exception $e) {
            logHelper::logError(null, [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
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
            'message' => 'Dashboard refresh triggered, please wait a moment.',
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
