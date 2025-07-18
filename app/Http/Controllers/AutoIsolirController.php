<?php

namespace App\Http\Controllers;

use App\Models\Mikrotik;
use App\Models\AutoIsolir;
use Illuminate\Http\Request;
use App\Models\Paket\PppProfile;
use App\Jobs\DisabledScheduleJob;
use App\Services\Routerboard\ScriptService;
use App\Services\Routerboard\FirewallService;
use App\Services\Routerboard\ScheduleService;
use App\Http\Resources\FirewallAddressListResource;
use App\Services\Routerboard\ProxyService;
use App\Services\Routerboard\TestConnectionService;
use App\Services\AutoIsolirService;
use Illuminate\Support\Str;
use App\Helpers\LogHelper;

class AutoIsolirController extends Controller
{
    private TestConnectionService $testConnectionService;
    private ScriptService $scriptService;
    private ScheduleService $scheduleService;
    private FirewallService $firewallService;
    private ProxyService $proxyService;
    private AutoIsolirService $autoIsolirService;
    public function __construct(TestConnectionService $testConnectionService, ScriptService $scriptService, ScheduleService $scheduleService, FirewallService $firewallService, ProxyService $proxyService, AutoIsolirService $autoIsolirService)
    {
        $this->testConnectionService = $testConnectionService;
        $this->scriptService = $scriptService;
        $this->scheduleService = $scheduleService;
        $this->firewallService = $firewallService;
        $this->proxyService = $proxyService;
        $this->autoIsolirService = $autoIsolirService;
        $this->middleware('permission:is-admin', ['only' => ['index', 'create', 'store', 'show', 'edit', 'update', 'edit_nat', 'update_nat', 'disable', 'enable', 'force_disable', 'destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $autoisolirs = AutoIsolir::with('profile:id,profile_name')->paginate(10);
        return view('autoisolirs.index', compact('autoisolirs'))
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $mikrotiks = Mikrotik::where('disabled', 'false')->orderBy('name', 'ASC')->pluck('name', 'id');
        $profiles = PppProfile::where('disabled', 'false')->orderBy('profile_name', 'ASC')->pluck('profile_name', 'id');
        // dd($profiles);
        // $ipAddressList = $this->firewallService->getIpAddressList($router);
        return view('autoisolirs.create', compact('mikrotiks', 'profiles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     $dueDate = null;
    //     $this->validate(
    //         $request,
    //         [
    //             'mikrotik_id' => 'required|unique:auto_isolirs',
    //             'profile_id' => ['required'],
    //             'address_list_isolir' => ['required'],
    //             'name' => ['required', 'alpha_dash'],
    //             'comment_payment' => ['required', 'alpha_dash'],
    //             'comment_unpayment' => ['required', 'alpha_dash'],
    //             'activation_date' => ['required'],
    //             // 'ros_version_id' => ['required'],

    //             //  'address_list_non_isolir' => ['required'],
    //         ],
    //         [
    //             'address_list_isolir' => 'Address list isolir required'
    //         ]
    //     );
    //     if ($request->activation_date == 'false') {
    //         $this->validate($request, [
    //             'due_date' => ['required', 'numeric', 'max:28'],
    //         ]);
    //         $dueDate = $request->due_date;
    //     }
    //     $mikrotik_id = $request->mikrotik_id;
    //     $router = Mikrotik::where('id', $mikrotik_id)->first();
    //     try {
    //         $this->testConnectionService->checkOnline($router);
    //     } catch (\Exception $e) {
    //         return redirect()->back()->withErrors(['msg' => 'Router offline, please check your router.']);
    //     }

    //     //$profile = $request->profile_id;
    //     $profileId = $request->profile_id;
    //     $profile = PppProfile::where('id', $profileId)->value('profile_name');
    //     $nameScript = $nameSchedule = $request->name;
    //     $activationDate = $request->activation_date;
    //     $payment = $request->comment_payment;
    //     $unpayment = $request->comment_unpayment;
    //     // $rosVersion = $request->ros_version_id;
    //     $rosVersion = $router->ros_version_id;
    //     // $address_list_non_isolir = $request->address_list_non_isolir;
    //     $address_list_isolir = $request->address_list_isolir;

    //     if ($activationDate == 'true') {
    //         $script = $this->scriptService->addAutoisolirActivationDate($router, $rosVersion, $nameScript, $unpayment, $profile, $address_list_isolir);
    //     } else {
    //         $script = $this->scriptService->addAutoisolirDueDate($router, $rosVersion, $nameScript, $dueDate, $unpayment, $profile, $address_list_isolir);
    //     }

    //     if (isset($script['after']['message'])) {
    //         if ($script['after']['message'] == 'failure: item with such name already exists') {
    //             return redirect()->back()->withErrors(['msg' => 'Script with such name already exists']);
    //         } else {
    //             return redirect()->back()->withErrors(['msg' => 'An error occurred while creating the script', $script['after']['message']]);
    //         }
    //     } else {
    //         $scriptId = $script['after']['ret'];
    //     }

    //     $schedule = $this->scheduleService->addScheduleEveryDay($router, $nameSchedule, $nameScript);
    //     if (isset($schedule['after']['message'])) {
    //         $this->scriptService->removeScript($router, $scriptId);
    //         if ($schedule['after']['message'] == 'failure: item with this name already exists') {
    //             return redirect()->back()->withErrors(['msg' => 'Scedule with such name already exists']);
    //         } else {
    //             return redirect()->back()->withErrors([
    //                 'msg' =>
    //                 'An error occurred while creating the schedule',
    //                 $schedule['after']['message'],
    //                 'Script creation cancelled!'
    //             ]);
    //         }
    //     } else {
    //         $scheduleId = $schedule['after']['ret'];
    //         AutoIsolir::create([
    //             'name' => $request->name,
    //             'mikrotik_id' => $mikrotik_id,
    //             'profile_id' => $request->profile_id,
    //             'script_id' => $scriptId,
    //             'schedule_id' => $scheduleId,
    //             'activation_date' => $activationDate,
    //             'comment_payment' => $payment,
    //             'comment_unpayment' => $unpayment,
    //             'due_date' => $dueDate,
    //             //'ros_version_id' => $rosVersion,
    //             'disabled' => 'false',
    //             'nat_id' => 0,
    //             'proxy_access_id' => 0,
    //             'address_list_isolir' => $address_list_isolir,
    //             // 'address_list_non_isolir' => $address_list_non_isolir,
    //         ]);

    //         return redirect()->route('autoisolir.index')
    //             ->with('success', 'Auto isolir created successfully');
    //     }
    // }
    public function store(Request $request)
    {
        $traceId = (string) Str::uuid();
        $validated = $request->validate([
            'mikrotik_id' => 'required|unique:auto_isolirs',
            'profile_id' => ['required'],
            'address_list_isolir' => ['required'],
            'name' => ['required', 'alpha_dash'],
            'comment_payment' => ['required', 'alpha_dash'],
            'comment_unpayment' => ['required', 'alpha_dash'],
            'activation_date' => ['required'],
        ], [
            'address_list_isolir.required' => 'Address list isolir required'
        ]);

        if ($request->activation_date === 'false') {
            $request->validate([
                'due_date' => ['required', 'numeric', 'max:28'],
            ]);
        }

        try {
            $this->autoIsolirService->createAutoIsolir($request);
            LogHelper::logInfo(null, ['mikrotik_id' => $request->mikrotik_id], 200, 'AutoIsolir stored successfully', $traceId);
            return redirect()->route('autoisolir.index')->with('success', 'Auto isolir created successfully');
        } catch (\Exception $e) {
            LogHelper::logError(null, ['mikrotik_id' => $request->mikrotik_id, 'msg' => $e->getMessage()], 500, 'AutoIsolir failed to store', $traceId);
            return redirect()->back()->withErrors(['msg' => $e->getMessage()]);
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
            $ipAddressList = $this->firewallService->getIpAddressList($router);
            // return dd($ipAddressList);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['msg' => 'Server ' . $router->name . ' offline, please check your router.']);
        }
        if ($router->disabled == 'true') {

            return redirect()->back()->withErrors(['msg' => 'Server ' . $router->name . ' disable, please enable first to change configuration.']);
        }
        // $profiles = $this->secretService->getPppProfile($router);
        // $collectionProfiles = ProfileResource::collection($profiles)->pluck('name', 'name');
        // Ambil data profile dari database yang status disabled = 'false'
        $profiles = PppProfile::where('disabled', 'false')
            ->orderBy('profile_name', 'ASC')
            ->get();

        // Pluck data menjadi format key-value (id => profile_name)
        $collectionProfiles = $profiles->pluck('profile_name', 'id');
        $collectionIpAddressLists = FirewallAddressListResource::collection($ipAddressList)->pluck('list', 'list');

        return view('autoisolirs.edit', [
            'autoisolir' => $autoisolir,
            'collectionProfiles' => $collectionProfiles,
            'collectionIpAddressLists' => $collectionIpAddressLists,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, AutoIsolir $autoisolir)
    // {
    //     $dueDate = null;
    //     $this->validate(
    //         $request,
    //         [
    //             'profile_id' => ['required'],
    //             'comment_payment' => ['required', 'alpha_dash'],
    //             'comment_unpayment' => ['required', 'alpha_dash'],
    //             'activation_date' => ['required'],
    //             // 'ros_version_id' => ['required'],
    //             'address_list_isolir' => ['required'],
    //         ],
    //         [
    //             'address_list_isolir.required' => 'Address list isolir required'
    //         ]
    //     );

    //     if ($request->activation_date == 'false') {
    //         $this->validate($request, [
    //             'due_date' => ['required', 'numeric', 'max:28'],
    //         ]);
    //         $dueDate = $request->due_date;
    //     }
    //     // $mikrotik_id = $autoisolir->mikrotik_id;
    //     $router = $autoisolir->mikrotik;
    //     // $router = Mikrotik::where('id', $mikrotik_id)->first();
    //     try {
    //         $this->testConnectionService->checkOnline($router);
    //         LogHelper::logInfo(null, ['router' => $router->name], 200, 'Router is online');
    //     } catch (\Exception $e) {
    //         LogHelper::logError(null, ['router' => $router->name], 500, 'Router offline: ' . $e->getMessage());
    //         return redirect()->back()->withErrors(['msg' => 'Router offline, please check your router.']);
    //     }

    //     //$profile = $request->profile_id;
    //     $profileId = $request->profile_id;
    //     $profile = PppProfile::where('id', $profileId)->value('profile_name');
    //     $dueDate = $request->due_date;
    //     // $nameScript = $nameSchedule = $request->name;
    //     $payment = $request->comment_payment;
    //     $unpayment = $request->comment_unpayment;
    //     $scriptId = $autoisolir->script_id;
    //     $activationDate = $request->activation_date;
    //     $rosVersion = $router->ros_version_id;
    //     $address_list_isolir = $request->address_list_isolir;

    //     $periodeId = BillingPeriode::latest('created_at')->value('id');
    //     //$address_list = 'IP_ISOLIR';
    //     if (isset($periodeId)) {
    //         $customers_ppp = Customer::where('internet_service', 'ppp')->where('disabled', 'false')->where('activation', 'true')->get();
    //         $customers_ip_static = Customer::where('internet_service', 'ip_static')->where('disabled', 'false')->where('activation', 'true')->get();
    //         if ($activationDate == 'true') {
    //             $response = $this->scriptService->setAutoisolirActivationDate($router, $rosVersion, $scriptId, $unpayment, $profile, $address_list_isolir);
    //             LogHelper::logInfo(null, ['response' => $response], 200, 'Response from setAutoisolirActivationDate');
    //             $periodeName = BillingPeriode::latest('created_at')->value('name');
    //             $nextMonth = Carbon::parse($periodeName)->addMonth()->format('m');
    //             $month = Carbon::parse($periodeName)->format('m');
    //             $year = Carbon::parse($periodeName)->format('Y');
    //             foreach ($customers_ppp as $customer) {
    //                 $paylater = Billing::where('customer_id', $customer->id)->latest('updated_at')->value('pay_later');
    //                 if ($paylater) {
    //                     $date = Carbon::parse($paylater)->format('d');
    //                     $month = Carbon::parse($paylater)->format('m');
    //                     $year = Carbon::parse($paylater)->format('Y');
    //                 } else {
    //                     $date = Carbon::parse($customer->activation_date)->format('d');
    //                 }
    //                 $statusPayment = Billing::where('customer_id', $customer->id)->where('billing_periode_id', $periodeId)->value('status');
    //                 if ($statusPayment == 'LS') {
    //                     $comment_unpayment = $unpayment . '_' . $date . '_' . $nextMonth . '_' . $year;
    //                 } else {
    //                     $comment_unpayment = $unpayment . '_' . $date . '_' . $month . '_' . $year;
    //                 }
    //                 dispatch(new AddPaymentBeforeDueDateJob($router, $comment_unpayment, $customer->secret_id))->onQueue('default');
    //             }
    //             foreach ($customers_ip_static as $customer) {
    //                 $paylater = Billing::where('customer_id', $customer->id)->latest('updated_at')->value('pay_later');
    //                 if ($paylater) {
    //                     $date = Carbon::parse($paylater)->format('d');
    //                     $month = Carbon::parse($paylater)->format('m');
    //                     $year = Carbon::parse($paylater)->format('Y');
    //                 } else {
    //                     $date = Carbon::parse($customer->activation_date)->format('d');
    //                 }
    //                 $statusPayment = Billing::where('customer_id', $customer->id)->where('billing_periode_id', $periodeId)->value('status');
    //                 if ($statusPayment == 'LS') {
    //                     $comment_unpayment = $unpayment . '_' . $date . '_' . $nextMonth . '_' . $year;
    //                 } else {
    //                     $comment_unpayment = $unpayment . '_' . $date . '_' . $month . '_' . $year;
    //                 }
    //                 dispatch(new AddPaymentIpStaticBeforeDueDateJob($router, $customer->simpleque_id, $comment_unpayment))->onQueue('default');
    //             }
    //         } else {
    //             $response = $this->scriptService->setAutoisolirDueDate($router, $rosVersion, $scriptId, $dueDate, $unpayment, $profile, $address_list_isolir);
    //             LogHelper::logInfo(null, ['response' => $response], 200, 'Response from setAutoisolirDueDate');
    //             foreach ($customers_ppp as $customer) {
    //                 // $date = Carbon::parse($customer->activation_date)->format('d');
    //                 $statusPayment = Billing::where('customer_id', $customer->id)->where('billing_periode_id', $periodeId)->value('status');
    //                 if ($statusPayment == 'LS') {
    //                     dispatch(new AddPaymentBeforeDueDateJob($router, $payment, $customer->secret_id))->onQueue('default');
    //                 } else {
    //                     dispatch(new AddPaymentBeforeDueDateJob($router, $unpayment, $customer->secret_id))->onQueue('default');
    //                 }
    //             }

    //             foreach ($customers_ip_static as $customer) {
    //                 // $date = Carbon::parse($customer->activation_date)->format('d');
    //                 $statusPayment = Billing::where('customer_id', $customer->id)->where('billing_periode_id', $periodeId)->value('status');
    //                 if ($statusPayment == 'LS') {
    //                     dispatch(new AddPaymentIpStaticBeforeDueDateJob($router, $customer->simpleque_id, $payment))->onQueue('default');
    //                 } else {
    //                     dispatch(new AddPaymentIpStaticBeforeDueDateJob($router, $customer->simpleque_id, $unpayment))->onQueue('default');
    //                 }
    //             }
    //         }
    //     }

    //     if (isset($response['after']['message'])) {
    //         $rawMessage = $response['after']['message'];
    //         LogHelper::logError(null, ['raw' => $rawMessage], 400, 'Mikrotik script update failed');

    //         if (str_contains($rawMessage, "policy")) {
    //             $friendlyMessage = "Akses API user tidak memiliki izin untuk mengedit script. Harap periksa policy user di Mikrotik.";
    //         } elseif (str_contains($rawMessage, "such name already exists")) {
    //             $friendlyMessage = "Nama script sudah digunakan di Mikrotik. Silakan gunakan nama lain.";
    //         } else {
    //             $friendlyMessage = "Terjadi kesalahan saat menjalankan perintah Mikrotik: " . $rawMessage;
    //         }
    //         return redirect()->back()->withErrors(['msg' => $friendlyMessage]);
    //     } else {
    //         $autoisolir->update([
    //             'profile_id' => $request->profile_id,
    //             'comment_payment' => $payment,
    //             'comment_unpayment' => $unpayment,
    //             'due_date' => $dueDate,
    //             'activation_date' => $activationDate,
    //             //  'ros_version_id' => $rosVersion,
    //             'address_list_isolir' => $address_list_isolir,
    //         ]);
    //         LogHelper::logInfo(null, ['autoisolir_id' => $autoisolir->id], 200, 'AutoIsolir updated successfully');
    //         return redirect()->route('autoisolir.index')
    //             ->with('success', 'Auto isolir updated.');
    //     }
    // }

    public function update(Request $request, AutoIsolir $autoisolir)
    {
        $traceId = (string) Str::uuid();
        $this->validateRequest($request);

        try {
            $this->autoIsolirService->updateAutoIsolir([
                'profile_id' => $request->profile_id,
                'comment_payment' => $request->comment_payment,
                'comment_unpayment' => $request->comment_unpayment,
                'activation_date' => $request->activation_date,
                'due_date' => $request->due_date ?? null,
                'address_list_isolir' => $request->address_list_isolir,
                'scriptService' => $this->scriptService,
            ], $autoisolir);

            LogHelper::logInfo(null, ['autoisolir_id' => $autoisolir->id], 200, 'AutoIsolir updated successfully', $traceId);
            return redirect()->route('autoisolir.index')->with('success', 'Auto isolir updated.');
        } catch (\Exception $e) {
            LogHelper::logError(null, ['autoisolir_id' => $autoisolir->id, 'msg' => $e->getMessage()], 500, 'AutoIsolir updated failed', $traceId);
            return back()->withErrors(['msg' => $e->getMessage()]);
        }
    }

    public function edit_nat(AutoIsolir $autoisolir)
    {
        $router = Mikrotik::where('id', $autoisolir->mikrotik_id)->first();
        /*
        if ($router->disabled == 'true') {
            return redirect()->back()->withErrors(['msg' => 'Server ' . $router->name . ' disable, please enable first to change configuration.']);
        }
        try {
            $ipAddressList = $this->firewallService->getIpAddressList($router);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['msg' => 'Server ' . $router->name . ' offline, please check your router.']);
        }
        $collectionIpAddressLists = FirewallAddressListResource::collection($ipAddressList)->pluck('list', 'list');
        return view('autoisolirs.edit_nat', compact('autoisolir', 'collectionIpAddressLists'));
        */
        return view('autoisolirs.edit_nat', compact('autoisolir'));
    }

    // public function update_nat(Request $request, AutoIsolir $autoisolir)
    // {
    //     $this->validate($request, [
    //         'nat_dst_address' => ['required'],
    //         // 'nat_src_address_list' => ['required'],
    //         'access_action' => ['required'],
    //         'proxy_access_src_address' => ['required'],
    //     ]);
    //     $nat_dst_address = $request->nat_dst_address;
    //     // $nat_src_address_list = $request->nat_src_address_list;
    //     $nat_src_address_list = $autoisolir->address_list_isolir;
    //     //if ($request->nat_dst_address_list) {
    //     $nat_dst_address_list = $request->nat_dst_address_list;
    //     // } else {
    //     //     $nat_dst_address_list = 'ALLOW_ACCESS_ISOLIR';
    //     // }
    //     $proxy_access_src_address = $request->proxy_access_src_address;
    //     $action = $request->access_action;
    //     // return dd($action);

    //     $router = Mikrotik::where('id', $autoisolir->mikrotik_id)->first();
    //     //return dd($router);
    //     if ($router->disabled == 'true') {
    //         return redirect()->back()->withErrors(['msg' => 'Server ' . $router->name . ' disable, please enable first to change configuration.']);
    //     }
    //     try {
    //         if ($autoisolir->nat_id == 0) {
    //             $createNatIsolir = $this->firewallService->createNatService($router, $nat_dst_address, $nat_src_address_list, $nat_dst_address_list);
    //             if (isset($createNatIsolir['after']['message'])) {
    //                 $natIsolirId = 0;
    //             } else {
    //                 $natIsolirId = $createNatIsolir['after']['ret'];
    //                 $autoisolir->update([
    //                     'nat_id' => $natIsolirId,
    //                 ]);
    //             }
    //         } else {
    //             $updateNatIsolir = $this->firewallService->updateNatService($router, $nat_dst_address, $nat_src_address_list, $nat_dst_address_list, $autoisolir->nat_id);
    //         }

    //         /*  if ($autoisolir->firewall_filter_id == 0) {
    //             $createFilterDrop = $this->firewallService->createFilterDropService($router, $nat_src_address_list, $nat_dst_address);
    //             if (isset($createFilterDrop['after']['message'])) {
    //                 $filterDropId = 0;
    //             } else {
    //                 $filterDropId = $createFilterDrop['after']['ret'];
    //                 $autoisolir->update([
    //                     'firewall_filter_id' => $filterDropId
    //                 ]);
    //             }
    //         } else {
    //             $updateFilterDrop = $this->firewallService->updateFilterDropService($router, $nat_src_address_list, $nat_dst_address, $autoisolir->firewall_filter_id);
    //         }
    //         */

    //         if ($autoisolir->proxy_access_id == 0) {
    //             if ($autoisolir->mikrotik->ros_version_id == '1') {
    //                 $createProxyAccessIsolir = $this->proxyService->createAccessServiceV6_49($router, $proxy_access_src_address, $nat_dst_address, $action);
    //             } else if ($autoisolir->mikrotik->ros_version_id == '2') {
    //                 $createProxyAccessIsolir = $this->proxyService->createAccessServiceV7_10($router, $proxy_access_src_address, $nat_dst_address, $action);
    //             }
    //             if (isset($createProxyAccessIsolir['after']['message'])) {
    //                 $proxyAccessId =  0;
    //                 return redirect()->back()->withErrors(['msg' => $createProxyAccessIsolir['after']['message']]);
    //             } else {
    //                 $proxyAccessId = $createProxyAccessIsolir['after']['ret'];
    //                 $autoisolir->update([
    //                     'proxy_access_id' => $proxyAccessId
    //                 ]);
    //             }
    //         } else {
    //             if ($autoisolir->ros_version_id == '1') {
    //                 $updateProxyAccessIsolir = $this->proxyService->updateAccessServiceV6_49($router, $proxy_access_src_address, $nat_dst_address, $autoisolir->proxy_access_id, $action);
    //             } else if ($autoisolir->ros_version_id == '2') {
    //                 $updateProxyAccessIsolir = $this->proxyService->updateAccessServiceV7_10($router, $proxy_access_src_address, $nat_dst_address, $autoisolir->proxy_access_id, $action);
    //             }
    //         }

    //         $this->proxyService->enableProxyService($router);
    //         //return dd($createProxyAccessIsolir);
    //     } catch (\Exception $e) {
    //         return redirect()->back()->withErrors(['msg' => 'Server ' . $router->name . ' offline, please check your router.']);
    //     }

    //     $autoisolir->update([
    //         'nat_dst_address' => $nat_dst_address,
    //         // 'nat_src_address_list' => $nat_src_address_list,
    //         'nat_dst_address_list' => $nat_dst_address_list,
    //         'proxy_access_src_address' => $proxy_access_src_address,
    //         'proxy_access_action_data' => $nat_dst_address . '/isolir',
    //         'proxy_access_action' => $action,
    //     ]);

    //     return redirect()->back()
    //         ->with('success', 'Updated successfully');
    // }

    public function update_nat(Request $request, AutoIsolir $autoisolir)
    {
        $traceId = (string) Str::uuid();
        $this->validate($request, [
            'nat_dst_address' => ['required'],
            'access_action' => ['required'],
            'proxy_access_src_address' => ['required'],
        ]);

        try {
            $this->autoIsolirService->updateNatProxy($autoisolir, $request->all());

            LogHelper::logInfo(null, [
                'autoisolir_id' => $autoisolir->id,
                'router' => $autoisolir->mikrotik->name,
            ], 200, 'NAT & Proxy Access updated', $traceId);

            return redirect()->back()->with('success', 'Updated successfully');
        } catch (\Exception $e) {
            LogHelper::logError(null, [
                'autoisolir_id' => $autoisolir->id,
                'router' => $autoisolir->mikrotik->name,
                'error' => $e->getMessage(),
            ], 500, 'Failed to update NAT & Proxy Access', $traceId);

            return redirect()->back()->withErrors(['msg' => $e->getMessage()]);
        }
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
