<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Paket;
use App\Models\Customer;
use App\Models\Mikrotik;
use Illuminate\Http\Request;
//use Yajra\DataTables\Facades\DataTables;
use App\Models\Billing\Billing;
use Spatie\Permission\Models\Role;
use App\Http\Resources\Pppoe\SecretResource;
use App\Models\AutoIsolir;
use App\Models\Billing\BillingPeriode;
use App\Models\Websystem;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\SystemService;
use App\Services\Routerboard\TestConnectionService;
use Jenssegers\Agent\Agent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
        // Check for version mismatch
        if ($this->version != $this->newVersion) {
            return redirect()->route('settings.update.index')->with('error', 'Please update first!');
        }

        // Fetch Mikrotik data
        $mikrotiks = Mikrotik::orderBy('name', 'ASC')->get();
        $routersEnable = Mikrotik::where('disabled', 'false')->get();

        // Initialize counters
        $countRouterOnline = $countRouterOffline = $customersOnline = $sumCustomersOnline = $customersOffline = $sumCustomersOffline = $countCustomersIsolir = $sumCustomersIsolir = 0;
        $onlineRouters = collect();

        // Process enabled routers
        foreach ($routersEnable as $routerEnable) {
            $profileIsolir = AutoIsolir::where('mikrotik_id', $routerEnable->id)->value('profile_id');

            try {
                // Test router connection
                $this->testConnectionService->checkOnline($routerEnable);
                $countRouterOnline++;
                $onlineRouters->push($routerEnable);

                // Get interfaces and active customers
                $usernameCustomers = Customer::where('mikrotik_id', $routerEnable->id)
                    ->where('disabled', 'false')
                    ->pluck('username');

                $activeSecrets = collect($this->secretService->getPppActive($routerEnable))
                    ->pluck('name');

                // Count customers
                $customersOnline = $activeSecrets->count();
                $customersOffline = $usernameCustomers->diff($activeSecrets)->count();

                // Count isolir customers
                if ($profileIsolir) {
                    $secretProfileIsolir = $this->secretService->getSecretByProfile($routerEnable, $profileIsolir);
                    if ($secretProfileIsolir !== 'error') {
                        $countCustomersIsolir = collect($secretProfileIsolir)
                            ->where('disabled', 'false')
                            ->count();
                    }
                }
            } catch (Exception $e) {
                $countRouterOffline++;
            }

            // Update cumulative counters
            $sumCustomersOnline += $customersOnline;
            $sumCustomersOffline += $customersOffline;
            $sumCustomersIsolir += $countCustomersIsolir;
        }
        $customers = Customer::all();

        // Billing data
        $lastBillingPeriode = BillingPeriode::latest('created_at')->first();
        $lastBillings = $lastBillingPeriode ? $lastBillingPeriode->billing : false;

        // Calculate billing summary
        $billingSummary = $this->calculateBillingSummary();
        $totalBilling = $billingSummary['total_billing'];
        $totalPaid = $billingSummary['total_paid'];
        $totalPayLater = $billingSummary['total_paylater'];
        $totalUnpaid = $billingSummary['total_unpaid'];

        if ($lastBillingPeriode) {
            $adminBillingSummary = Billing::with(['customer', 'customer.user'])
                ->where('billing_periode_id', $lastBillingPeriode->id)
                ->get()
                ->groupBy(fn($billing) => $billing->customer->user->id ?? 'Not Linked')
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
        } else {
            $adminBillingSummary = collect([]);
        }

        $data = Billing::with('billing_periode')
            ->selectRaw("
        billing_periode_id,
        COUNT(DISTINCT id) as total_customers, -- Hitung total pelanggan unik
        SUM(CASE WHEN status IN ('LS', 'BL', 'PL') THEN paket_price ELSE 0 END) as total_nominal,
        SUM(CASE WHEN status = 'LS' THEN paket_price ELSE 0 END) as total_paid,
        SUM(CASE WHEN status IN ('BL', 'PL') THEN paket_price ELSE 0 END) as total_unpaid
    ")
            ->groupBy('billing_periode_id')
            ->get()
            ->map(function ($item) {
                return [
                    'month_name' => $item->billing_periode->name, // Ambil nama bulan dari relasi
                    'total_customers' => $item->total_customers, // Jumlah pelanggan
                    'total_nominal' => $item->total_nominal, // Total nominal tagihan (LS, BL, PL)
                    'total_paid' => $item->total_paid, // Tagihan yang sudah lunas (LS)
                    'total_unpaid' => $item->total_unpaid // Tagihan yang belum lunas (BL, PL)
                ];
            });

        // Detect mobile view
        $isMobile = (new Agent())->isMobile();

        // Render view
        return view('home', compact(
            'sumCustomersOffline',
            'sumCustomersOnline',
            'mikrotiks',
            'lastBillings',
            'lastBillingPeriode',
            'countRouterOnline',
            'countRouterOffline',
            'routersEnable',
            'onlineRouters',
            'sumCustomersIsolir',
            'totalBilling',
            'totalPaid',
            'totalPayLater',
            'totalUnpaid',
            'customers',
            'data',
            'adminBillingSummary',
        ));
    }

    public function calculateBillingSummary()
    {
        $lastBillingPeriode = BillingPeriode::latest('created_at')->first();
        $searchPeriode = $lastBillingPeriode->name;

        // Query for billings
        $billings = Billing::query()
            ->whereHas('billing_periode', function ($query) use ($searchPeriode) {
                $query->where('name', 'like', "%{$searchPeriode}%");
            });

        $totalBilling = (clone $billings)->sum('paket_price');
        $totalPaid = (clone $billings)->where('status', 'LS')->sum('paket_price');
        $totalPayLater = (clone $billings)->where('status', 'PL')->sum('paket_price');
        $totalUnpaid = (clone $billings)->where('status', 'BL')->sum('paket_price');

        // Return result as an array
        return [
            'total_billing' => $totalBilling,
            'total_paid' => $totalPaid,
            'total_paylater' => $totalPayLater,
            'total_unpaid' => $totalUnpaid,
        ];
    }
}
