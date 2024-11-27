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
        $mikrotiks = Mikrotik::orderBy('name', 'ASC')->get();
        $countRouterOnline = 0;
        $countRouterOffline = 0;
        $countCustomersIsolir = 0;
        $routersEnable = Mikrotik::where('disabled', 'false')->get();
        $customersOnline = 0;
        $customersOffline = 0;
        $sumCustomersOnline = 0;
        $sumCustomersOffline = 0;
        $sumCustomersIsolir = 0;


        if (count($routersEnable) != 0) {
            $onlineRouters = array();
            // $countRouterEnable = count($routersEnable);
            //$i = 0;
            foreach ($routersEnable as $routerEnable) {
                $profileIsolir = AutoIsolir::where('mikrotik_id', $routerEnable->id)->value('profile_id');
                try {
                    $this->testConnectionService->checkOnline($routerEnable);
                    $countRouterOnline++;
                    $onlineRouters[] = $routerEnable;
                    $interfaces = $this->systemService->mikrotikEtherInterface($routerEnable);
                    $usernameCustomers = Customer::where('mikrotik_id', $routerEnable->id)
                        ->where('disabled', 'false')
                        ->pluck('username')->toArray();
                    $collectCustomerUsernames = collect($usernameCustomers);
                    $getAllSecretActive = $this->secretService->getPppActive($routerEnable);
                    $collectionSecretActive = SecretResource::collection($getAllSecretActive);
                    $customersOnline = $collectionSecretActive->count();
                    $secretActive = $collectionSecretActive->pluck(['name'])->toArray();
                    $secretsNonActive = $collectCustomerUsernames->diff($secretActive);
                    $customersOffline = count($secretsNonActive);


                    if ($profileIsolir) {
                        $secretProfileIsolir = $this->secretService->getSecretByProfile($routerEnable, $profileIsolir);
                        if ($secretProfileIsolir != 'error') {
                            $collectionSecretIsolir = SecretResource::collection($secretProfileIsolir)->where('disabled', 'false');
                            // return dd($collectionSecretIsolir);
                            //$collectionSecretIsolir->where('disable', 'false');
                            $countCustomersIsolir = count($collectionSecretIsolir);
                        }
                    }
                } catch (Exception $e) {
                    $interfaces = '';
                    $countRouterOffline++;
                }

                $sumCustomersOffline += $customersOffline;
                $sumCustomersIsolir += $countCustomersIsolir;
                $sumCustomersOnline += $customersOnline;
            }
            $onlineRouters = collect($onlineRouters);
        } else {
            $onlineRouters = false;
            $interfaces = false;
        }
        $customers = Customer::all();

        $lastBillingPeriode = BillingPeriode::latest('created_at')->first();
        if ($lastBillingPeriode) {
            $lastBillings = $lastBillingPeriode->billing;
        } else {
            $lastBillings = false;
        }

        //} else {
        //   $lastBillings = 0;
        //   $lastBillingPeriode = 'No Billing Created';
        // }


        $agent = new Agent();
        $result = $agent->isMobile();

        if ($result) {
            //mobile view
            return view('home', compact(
                'sumCustomersOffline',
                'sumCustomersOnline',
                'mikrotiks',
                'customers',
                'lastBillings',
                'lastBillingPeriode',
                'countRouterOnline',
                'countRouterOffline',
                'routersEnable',
                'onlineRouters',
                'interfaces',
                'sumCustomersIsolir'
            ));
        } else {
            return view('home', compact(
                'sumCustomersOffline',
                'sumCustomersOnline',
                'mikrotiks',
                'customers',
                'lastBillings',
                'lastBillingPeriode',
                'countRouterOnline',
                'countRouterOffline',
                'routersEnable',
                'onlineRouters',
                'interfaces',
                'sumCustomersIsolir'
            ));
        }
    }
}
