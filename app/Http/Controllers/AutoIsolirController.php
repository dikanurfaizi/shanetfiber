<?php

namespace App\Http\Controllers;

use App\Models\Mikrotik;
use App\Models\AutoIsolir;
use Illuminate\Http\Request;
use App\Models\Packages\PppProfile;
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
        return view('autoisolirs.create', compact('mikrotiks', 'profiles'));
    }

    /**
     * Store a newly created resource in storage.
     */
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
            $this->autoIsolirService->createAutoIsolir($request, $traceId);
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
        $traceId = (string) Str::uuid();
        $router = Mikrotik::where('id', $autoisolir->mikrotik_id)->first();

        try {
            $this->testConnectionService->checkOnline($router, $traceId);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['msg' => 'Router offline, please check your router.']);
        }

        $scheduleId = $autoisolir->schedule_id;
        $schedules = $this->scheduleService->getScheduleById($router, $scheduleId, $traceId);
        $schedule = $schedules[0];
        $scriptId = $autoisolir->script_id;
        $scripts = $this->scriptService->getScriptById($router, $scriptId, $traceId);
        $script = $scripts[0];

        return view('autoisolirs.show', compact('autoisolir', 'schedule', 'script'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AutoIsolir $autoisolir)
    {
        $traceId = (string) Str::uuid();
        $router = Mikrotik::where('id', $autoisolir->mikrotik_id)->first();
        try {
            $this->testConnectionService->checkOnline($router, $traceId);
            $ipAddressList = $this->firewallService->getIpAddressList($router, $traceId);
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
            ], $autoisolir, $traceId);

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
        return view('autoisolirs.edit_nat', compact('autoisolir'));
    }

    public function update_nat(Request $request, AutoIsolir $autoisolir)
    {
        $traceId = (string) Str::uuid();
        $this->validate($request, [
            'nat_dst_address' => ['required'],
            'access_action' => ['required'],
            'proxy_access_src_address' => ['required'],
        ]);

        try {
            $this->autoIsolirService->updateNatProxy($autoisolir, $request->all(), $traceId);

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
        $traceId = (string) Str::uuid();
        $mikrotik_id = $autoisolir->mikrotik_id;

        $router = Mikrotik::where('id', $mikrotik_id)->first();
        try {
            $this->testConnectionService->checkOnline($router, $traceId);
        } catch (\Exception $e) {
            $autoisolir->delete();
            return redirect()->back()->withErrors(['msg' => 'Auto isolir on database deleted successfully, but not on your server!', 'Router offline, you can delete manually on your router.']);
        }
        if ($autoisolir->nat_id != 0) {
            $natId = $autoisolir->nat_id;
            $this->firewallService->removeNatService($router, $natId, $traceId);
        }
        if ($autoisolir->firewall_filter_id != 0) {
            $filterId = $autoisolir->firewall_filter_id;
            $this->firewallService->removeFilterDropService($router, $filterId, $traceId);
        }
        if ($autoisolir->proxy_access_id != 0) {
            $proxyAccessId = $autoisolir->proxy_access_id;
            $this->proxyService->removeAccessService($router, $proxyAccessId, $traceId);
        }
        $this->proxyService->disableProxyService($router, $traceId);
        $scriptId = $autoisolir->script_id;
        $scheduleId = $autoisolir->schedule_id;
        $this->scriptService->removeScript($router, $scriptId, $traceId);
        $this->scheduleService->removeSchedule($router, $scheduleId, $traceId);
        $autoisolir->delete();
        return redirect()->route('autoisolir.index')
            ->with('success', 'Auto isolir deleted successfully');
    }

    public function disable(AutoIsolir $autoisolir)
    {
        $traceId = (string) Str::uuid();
        $router = Mikrotik::where('id', $autoisolir->mikrotik_id)->first();

        try {
            $this->testConnectionService->checkOnline($router, $traceId);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['msg' => 'Router offline, please check your router.']);
        }

        $scheduleId = $autoisolir->schedule_id;
        $disabledStatus = 'true';
        dispatch(new DisabledScheduleJob($router, $scheduleId, $disabledStatus, $traceId))->onQueue('default');
        $autoisolir->update([
            'disabled' => $disabledStatus,
        ]);
        return redirect()->back()->with('success', 'Disable auto isolir successfully');
    }

    public function force_disable(AutoIsolir $autoisolir)
    {
        $traceId = (string) Str::uuid();
        $disabledStatus = 'true';
        $router = Mikrotik::where('id', $autoisolir->mikrotik_id)->first();
        try {
            $this->testConnectionService->checkOnline($router, $traceId);
        } catch (\Exception $e) {
            $autoisolir->update([
                'disabled' => $disabledStatus,
            ]);
            return redirect()->back()->with('success', 'Force Disable auto isolir successfully');
            // return redirect()->back()->withErrors(['msg' => 'Router offline, please check your router.']);
        }
        $scheduleId = $autoisolir->schedule_id;

        dispatch(new DisabledScheduleJob($router, $scheduleId, $disabledStatus, $traceId))->onQueue('default');
        $autoisolir->update([
            'disabled' => $disabledStatus,
        ]);
        return redirect()->back()->with('success', 'Force Disable auto isolir successfully');
    }

    public function enable(AutoIsolir $autoisolir)
    {
        $traceId = (string) Str::uuid();
        $router = Mikrotik::where('id', $autoisolir->mikrotik_id)->get();
        try {
            $this->testConnectionService->checkOnline($router[0], $traceId);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['msg' => 'Router offline, please check your router.']);
        }
        $scheduleId = $autoisolir->schedule_id;
        $disabledStatus = 'false';
        dispatch(new DisabledScheduleJob($router[0], $scheduleId, $disabledStatus, $traceId))->onQueue('default');
        $autoisolir->update([
            'disabled' => $disabledStatus,
        ]);
        return redirect()->back()->with('success', 'Enable customer successfully');
    }
}
