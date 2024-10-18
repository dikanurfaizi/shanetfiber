<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Customer;
use App\Models\Mikrotik;
use App\Models\AutoIsolir;
use Illuminate\Http\Request;
use App\Models\Billing\Billing;
use App\Jobs\DisabledScheduleJob;
use App\Models\Billing\BillingPeriode;
use App\Jobs\AddPaymentBeforeDueDateJob;
use App\Services\Routerboard\ScriptService;
use App\Http\Resources\Pppoe\ProfileResource;
use App\Services\Routerboard\FirewallService;
use App\Services\Routerboard\ScheduleService;
use App\Services\Routerboard\Ppp\SecretService;
use App\Http\Resources\FirewallAddressListResource;
use App\Services\Routerboard\ProxyService;
use App\Services\Routerboard\TestConnectionService;

class AutoIsolirController extends Controller
{
    private TestConnectionService $testConnectionService;
    private SecretService $secretService;
    private ScriptService $scriptService;
    private ScheduleService $scheduleService;
    private FirewallService $firewallService;
    private ProxyService $proxyService;
    public function __construct(TestConnectionService $testConnectionService, SecretService $secretService, ScriptService $scriptService, ScheduleService $scheduleService, FirewallService $firewallService, ProxyService $proxyService)
    {
        $this->testConnectionService = $testConnectionService;
        $this->secretService = $secretService;
        $this->scriptService = $scriptService;
        $this->scheduleService = $scheduleService;
        $this->firewallService = $firewallService;
        $this->proxyService = $proxyService;

        $this->middleware('permission:is-admin', ['only' => ['index', 'create', 'store', 'show', 'edit', 'update', 'edit_nat', 'update_nat', 'disable', 'enable', 'force_disable', 'destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $autoisolirs = AutoIsolir::paginate(10);
        return view('autoisolirs.index', compact('autoisolirs'))
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $mikrotiks = Mikrotik::where('disabled', 'false')->orderBy('name', 'ASC')->pluck('name', 'id');
        return view('autoisolirs.create', compact('mikrotiks'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $dueDate = null;
        $this->validate($request, [
            'mikrotik_id' => 'required|unique:auto_isolirs',
            'profile_id' => ['required'],
            'name' => ['required', 'alpha_dash'],
            'comment_payment' => ['required', 'alpha_dash'],
            'comment_unpayment' => ['required', 'alpha_dash'],
            'activation_date' => ['required'],
            'ros_version_id' => ['required'],
        ]);
        if ($request->activation_date == 'false') {
            $this->validate($request, [
                'due_date' => ['required', 'numeric', 'max:28'],
            ]);
            $dueDate = $request->due_date;
        }
        $mikrotik_id = $request->mikrotik_id;
        $router = Mikrotik::where('id', $mikrotik_id)->first();
        try {
            $this->testConnectionService->checkOnline($router);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['msg' => 'Router offline, please check your router.']);
        }

        $profile = $request->profile_id;

        $nameScript = $nameSchedule = $request->name;
        $activationDate = $request->activation_date;
        $payment = $request->comment_payment;
        $unpayment = $request->comment_unpayment;
        $rosVersion = $request->ros_version_id;



        if ($activationDate == 'true') {
            $script = $this->scriptService->addAutoisolirActivationDate($router, $rosVersion, $nameScript, $unpayment, $profile);
        } else {
            $script = $this->scriptService->addAutoisolirDueDate($router, $rosVersion, $nameScript, $dueDate, $unpayment, $profile);
        }

        if (isset($script['after']['message'])) {
            if ($script['after']['message'] == 'failure: item with such name already exists') {
                return redirect()->back()->withErrors(['msg' => 'Script with such name already exists']);
            } else {
                return redirect()->back()->withErrors(['msg' => 'An error occurred while creating the script', $script['after']['message']]);
            }
        } else {
            $scriptId = $script['after']['ret'];
        }

        $schedule = $this->scheduleService->addScheduleEveryDay($router, $nameSchedule, $nameScript);
        if (isset($schedule['after']['message'])) {
            $this->scriptService->removeScript($router, $scriptId);
            if ($schedule['after']['message'] == 'failure: item with this name already exists') {
                return redirect()->back()->withErrors(['msg' => 'Scedule with such name already exists']);
            } else {
                return redirect()->back()->withErrors([
                    'msg' =>
                    'An error occurred while creating the schedule',
                    $schedule['after']['message'],
                    'Script creation cancelled!'
                ]);
            }
        } else {
            $scheduleId = $schedule['after']['ret'];
            AutoIsolir::create([
                'name' => $request->name,
                'mikrotik_id' => $mikrotik_id,
                'profile_id' => $request->profile_id,
                'script_id' => $scriptId,
                'schedule_id' => $scheduleId,
                'activation_date' => $activationDate,
                'comment_payment' => $payment,
                'comment_unpayment' => $unpayment,
                'due_date' => $dueDate,
                'ros_version_id' => $rosVersion,
                'disabled' => 'false',
                'nat_id' => 0,
                'proxy_access_id' => 0,
            ]);

            return redirect()->route('autoisolir.index')
                ->with('success', 'Auto isolir created successfully');
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(AutoIsolir $autoisolir)
    {
        $router = Mikrotik::where('id', $autoisolir->mikrotik_id)->first();

        try {
            $this->testConnectionService->checkOnline($router);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['msg' => 'Router offline, please check your router.']);
        }

        $scheduleId = $autoisolir->schedule_id;
        $schedules = $this->scheduleService->getScheduleById($router, $scheduleId);
        $schedule = $schedules[0];
        $scriptId = $autoisolir->script_id;
        $scripts = $this->scriptService->getScriptById($router, $scriptId);
        $script = $scripts[0];

        return view('autoisolirs.show', compact('autoisolir', 'schedule', 'script'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AutoIsolir $autoisolir)
    {
        $router = Mikrotik::where('id', $autoisolir->mikrotik_id)->first();
        try {
            $this->testConnectionService->checkOnline($router);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['msg' => 'Server ' . $router->name . ' offline, please check your router.']);
        }
        if ($router->disabled == 'true') {

            return redirect()->back()->withErrors(['msg' => 'Server ' . $router->name . ' disable, please enable first to change configuration.']);
        }
        $profiles = $this->secretService->getPppProfile($router);
        $collectionProfiles = ProfileResource::collection($profiles)->pluck('name', 'name');

        return view('autoisolirs.edit', [
            'autoisolir' => $autoisolir,
            'collectionProfiles' => $collectionProfiles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AutoIsolir $autoisolir)
    {
        $dueDate = null;
        $this->validate($request, [
            'profile_id' => ['required'],
            'comment_payment' => ['required', 'alpha_dash'],
            'comment_unpayment' => ['required', 'alpha_dash'],
            'activation_date' => ['required'],
            'ros_version_id' => ['required'],
        ]);

        if ($request->activation_date == 'false') {
            $this->validate($request, [
                'due_date' => ['required', 'numeric', 'max:28'],
            ]);
            $dueDate = $request->due_date;
        }


        $mikrotik_id = $autoisolir->mikrotik_id;
        $router = Mikrotik::where('id', $mikrotik_id)->first();
        try {
            $this->testConnectionService->checkOnline($router);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['msg' => 'Router offline, please check your router.']);
        }

        $profile = $request->profile_id;
        $dueDate = $request->due_date;
        // $nameScript = $nameSchedule = $request->name;
        $payment = $request->comment_payment;
        $unpayment = $request->comment_unpayment;
        $scriptId = $autoisolir->script_id;
        $activationDate = $request->activation_date;
        $rosVersion = $request->ros_version_id;

        $customers = Customer::where('disabled', 'false')->where('activation', 'true')->get();

        $periodeId = BillingPeriode::latest('created_at')->value('id');
        if (isset($periodeId)) {
            if ($activationDate == 'true') {
                $response = $this->scriptService->setAutoisolirActivationDate($router, $rosVersion, $scriptId, $unpayment, $profile);
                $periodeName = BillingPeriode::latest('created_at')->value('name');
                $nextMonth = Carbon::parse($periodeName)->addMonth()->format('m');
                $month = Carbon::parse($periodeName)->format('m');
                $year = Carbon::parse($periodeName)->format('Y');
                foreach ($customers as $customer) {
                    $paylater = Billing::where('customer_id', $customer->id)->latest('updated_at')->value('pay_later');
                    if ($paylater) {
                        $date = Carbon::parse($paylater)->format('d');
                        $month = Carbon::parse($paylater)->format('m');
                        $year = Carbon::parse($paylater)->format('Y');
                    } else {
                        $date = Carbon::parse($customer->activation_date)->format('d');
                    }


                    $statusPayment = Billing::where('customer_id', $customer->id)->where('billing_periode_id', $periodeId)->value('status');
                    if ($statusPayment == 'LS') {
                        $comment_unpayment = $unpayment . '_' . $date . '_' . $nextMonth . '_' . $year;
                        dispatch(new AddPaymentBeforeDueDateJob($router, $comment_unpayment, $customer->secret_id))->onQueue('default');
                    } else {
                        $comment_unpayment = $unpayment . '_' . $date . '_' . $month . '_' . $year;
                        dispatch(new AddPaymentBeforeDueDateJob($router, $comment_unpayment, $customer->secret_id))->onQueue('default');
                    }
                }
            } else {
                $response = $this->scriptService->setAutoisolirDueDate($router, $rosVersion, $scriptId, $dueDate, $unpayment, $profile);
                foreach ($customers as $customer) {
                    // $date = Carbon::parse($customer->activation_date)->format('d');
                    $statusPayment = Billing::where('customer_id', $customer->id)->where('billing_periode_id', $periodeId)->value('status');
                    if ($statusPayment == 'LS') {

                        dispatch(new AddPaymentBeforeDueDateJob($router, $payment, $customer->secret_id))->onQueue('default');
                    } else {

                        dispatch(new AddPaymentBeforeDueDateJob($router, $unpayment, $customer->secret_id))->onQueue('default');
                    }
                }
            }
        }

        if (isset($response['after']['message'])) {
            return redirect()->back()->withErrors(['msg' => $scriptId['after']['message']]);
        } else {
            $autoisolir->update([
                'profile_id' => $request->profile_id,
                'comment_payment' => $payment,
                'comment_unpayment' => $unpayment,
                'due_date' => $dueDate,
                'activation_date' => $activationDate,
                'ros_version_id' => $rosVersion,
            ]);
            return redirect()->route('autoisolir.index')
                ->with('success', 'Auto isolir updated.');
        }
    }

    public function edit_nat(AutoIsolir $autoisolir)
    {
        $router = Mikrotik::where('id', $autoisolir->mikrotik_id)->first();
        if ($router->disabled == 'true') {
            return redirect()->back()->withErrors(['msg' => 'Server ' . $router->name . ' disable, please enable first to change configuration.']);
        }
        try {
            $ipAddressList = $this->firewallService->getIpAddressList($router);
            // return dd($ipAddressList);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['msg' => 'Server ' . $router->name . ' offline, please check your router.']);
        }

        $collectionIpAddressLists = FirewallAddressListResource::collection($ipAddressList)->pluck('list', 'list');
        // return dd($collectionIpAddressLists);
        // $response = $this->firewallService->getIpAddressList($router);
        return view('autoisolirs.edit_nat', compact('autoisolir', 'collectionIpAddressLists'));
    }

    public function update_nat(Request $request, AutoIsolir $autoisolir)
    {
        $this->validate($request, [
            'nat_dst_address' => ['required'],
            'nat_src_address_list' => ['required'],
            'access_action' => ['required'],
            'proxy_access_src_address' => ['required'],
        ]);
        $nat_dst_address = $request->nat_dst_address;
        $nat_src_address_list = $request->nat_src_address_list;
        //if ($request->nat_dst_address_list) {
        $nat_dst_address_list = $request->nat_dst_address_list;
        // } else {
        //     $nat_dst_address_list = 'ALLOW_ACCESS_ISOLIR';
        // }
        $proxy_access_src_address = $request->proxy_access_src_address;
        $action = $request->access_action;
        // return dd($action);

        $router = Mikrotik::where('id', $autoisolir->mikrotik_id)->first();
        //return dd($router);
        if ($router->disabled == 'true') {
            return redirect()->back()->withErrors(['msg' => 'Server ' . $router->name . ' disable, please enable first to change configuration.']);
        }
        try {
            if ($autoisolir->nat_id == 0) {
                $createNatIsolir = $this->firewallService->createNatService($router, $nat_dst_address, $nat_src_address_list, $nat_dst_address_list);
                if (isset($createNatIsolir['after']['message'])) {
                    $natIsolirId = 0;
                } else {
                    $natIsolirId = $createNatIsolir['after']['ret'];
                    $autoisolir->update([
                        'nat_id' => $natIsolirId,
                    ]);
                }
            } else {
                $updateNatIsolir = $this->firewallService->updateNatService($router, $nat_dst_address, $nat_src_address_list, $nat_dst_address_list, $autoisolir->nat_id);
            }

            if ($autoisolir->firewall_filter_id == 0) {
                $createFilterDrop = $this->firewallService->createFilterDropService($router, $nat_src_address_list, $nat_dst_address);
                if (isset($createFilterDrop['after']['message'])) {
                    $filterDropId = 0;
                } else {
                    $filterDropId = $createFilterDrop['after']['ret'];
                    $autoisolir->update([
                        'firewall_filter_id' => $filterDropId
                    ]);
                }
            } else {
                $updateFilterDrop = $this->firewallService->updateFilterDropService($router, $nat_src_address_list, $nat_dst_address, $autoisolir->firewall_filter_id);
            }

            if ($autoisolir->proxy_access_id == 0) {
                if ($autoisolir->ros_version_id == '1') {
                    $createProxyAccessIsolir = $this->proxyService->createAccessServiceV6_49($router, $proxy_access_src_address, $nat_dst_address, $action);
                } else if ($autoisolir->ros_version_id == '2') {
                    $createProxyAccessIsolir = $this->proxyService->createAccessServiceV7_10($router, $proxy_access_src_address, $nat_dst_address, $action);
                }
                if (isset($createProxyAccessIsolir['after']['message'])) {
                    $proxyAccessId =  0;
                } else {
                    $proxyAccessId = $createProxyAccessIsolir['after']['ret'];
                    $autoisolir->update([
                        'proxy_access_id' => $proxyAccessId
                    ]);
                }
            } else {
                if ($autoisolir->ros_version_id == '1') {
                    $updateProxyAccessIsolir = $this->proxyService->updateAccessServiceV6_49($router, $proxy_access_src_address, $nat_dst_address, $autoisolir->proxy_access_id, $action);
                } else if ($autoisolir->ros_version_id == '2') {
                    $updateProxyAccessIsolir = $this->proxyService->updateAccessServiceV7_10($router, $proxy_access_src_address, $nat_dst_address, $autoisolir->proxy_access_id, $action);
                }
            }

            $this->proxyService->enableProxyService($router);
            //return dd($createProxyAccessIsolir);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['msg' => 'Server ' . $router->name . ' offline, please check your router.']);
        }

        $autoisolir->update([
            'nat_dst_address' => $nat_dst_address,
            'nat_src_address_list' => $nat_src_address_list,
            'nat_dst_address_list' => $nat_dst_address_list,
            'proxy_access_src_address' => $proxy_access_src_address,
            'proxy_access_action_data' => $nat_dst_address . '/isolir',
            'proxy_access_action' => $action,
        ]);
        return redirect()->back()
            ->with('success', 'Updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AutoIsolir $autoisolir)
    {

        $mikrotik_id = $autoisolir->mikrotik_id;

        $router = Mikrotik::where('id', $mikrotik_id)->first();
        try {
            $this->testConnectionService->checkOnline($router);
        } catch (\Exception $e) {
            $autoisolir->delete();
            return redirect()->back()->withErrors(['msg' => 'Auto isolir on database deleted successfully, but not on your server!', 'Router offline, you can delete manually on your router.']);
        }
        if ($autoisolir->nat_id != 0) {
            $natId = $autoisolir->nat_id;
            $this->firewallService->removeNatSevice($router, $natId);
        }
        if ($autoisolir->firewall_filter_id != 0) {
            $filterId = $autoisolir->firewall_filter_id;
            $this->firewallService->removeFilterDropSevice($router, $filterId);
        }
        if ($autoisolir->proxy_access_id != 0) {
            $proxyAccessId = $autoisolir->proxy_access_id;
            $this->proxyService->removeAccessService($router, $proxyAccessId);
        }
        $this->proxyService->disableProxyService($router);
        $scriptId = $autoisolir->script_id;
        $scheduleId = $autoisolir->schedule_id;
        $this->scriptService->removeScript($router, $scriptId);
        $this->scheduleService->removeSchedule($router, $scheduleId);
        $autoisolir->delete();
        return redirect()->route('autoisolir.index')
            ->with('success', 'Auto isolir deleted successfully');
    }

    public function disable(AutoIsolir $autoisolir)
    {
        $router = Mikrotik::where('id', $autoisolir->mikrotik_id)->first();

        try {
            $this->testConnectionService->checkOnline($router);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['msg' => 'Router offline, please check your router.']);
        }

        $scheduleId = $autoisolir->schedule_id;
        $disabledStatus = 'true';
        dispatch(new DisabledScheduleJob($router, $scheduleId, $disabledStatus))->onQueue('default');
        $autoisolir->update([
            'disabled' => $disabledStatus,
        ]);
        return redirect()->back()->with('success', 'Disable auto isolir successfully');
    }

    public function force_disable(AutoIsolir $autoisolir)
    {
        $disabledStatus = 'true';
        $router = Mikrotik::where('id', $autoisolir->mikrotik_id)->first();
        try {
            $this->testConnectionService->checkOnline($router);
        } catch (\Exception $e) {
            $autoisolir->update([
                'disabled' => $disabledStatus,
            ]);
            return redirect()->back()->with('success', 'Force Disable auto isolir successfully');
            // return redirect()->back()->withErrors(['msg' => 'Router offline, please check your router.']);
        }
        $scheduleId = $autoisolir->schedule_id;

        dispatch(new DisabledScheduleJob($router, $scheduleId, $disabledStatus))->onQueue('default');
        $autoisolir->update([
            'disabled' => $disabledStatus,
        ]);
        return redirect()->back()->with('success', 'Force Disable auto isolir successfully');
    }

    public function enable(AutoIsolir $autoisolir)
    {
        $router = Mikrotik::where('id', $autoisolir->mikrotik_id)->get();
        try {
            $this->testConnectionService->checkOnline($router[0]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['msg' => 'Router offline, please check your router.']);
        }
        $scheduleId = $autoisolir->schedule_id;
        $disabledStatus = 'false';
        dispatch(new DisabledScheduleJob($router[0], $scheduleId, $disabledStatus))->onQueue('default');
        $autoisolir->update([
            'disabled' => $disabledStatus,
        ]);
        return redirect()->back()->with('success', 'Enable customer successfully');
    }
}
