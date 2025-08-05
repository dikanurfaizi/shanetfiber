<?php

namespace App\Http\Controllers;

use App\Models\Mikrotik;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\ServerStoreRequest;
use App\Http\Requests\ServerUpdateRequest;
use App\Models\AutoIsolir;
use App\Models\Customer;
use App\Models\Paket\Paket;
use App\Services\Routerboard\SystemService;
use App\Helpers\LogHelper;
use App\Jobs\MigrateCustomerToRouter;

class ServerController extends Controller
{
    private SystemService $systemService;
    public function __construct(SystemService $systemService)
    {
        $this->systemService = $systemService;
        $this->middleware('permission:server-list|server-create|server-edit|server-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:server-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:server-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:server-delete', ['only' => ['destroy', 'delete']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        //  $user = auth()->user();
        // $mikrotiks = Mikrotik::orderBy('name', 'ASC')->paginate(5);
        $paginate = 15;
        $disabled = $request->disabled;
        $mikrotiks = Mikrotik::query()
            ->when($disabled, function ($query) use ($disabled) {
                $query->where('disabled', $disabled);
            })
            ->orderBy('name', 'ASC')
            ->paginate($paginate);
        return view('servers.index', compact('mikrotiks'))
            ->with('i', (request()->input('page', 1) - 1) * $paginate);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('servers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ServerStoreRequest $request): RedirectResponse
    {
        Mikrotik::create([
            'name' => $name = $request->name,
            'slug' => str($name . '-' . Str::random(4))->slug(),
            'host' => $request->host,
            'username' => $request->username,
            'password' => $request->password,
            'port' => $request->port,
            'ros_version_id' => $request->ros_version_id
        ]);
        return redirect()->route('servers.index')
            ->with('success', 'Mikrotik added successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mikrotik $server): View
    {

        return view('servers.edit', compact('server'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ServerUpdateRequest $request, Mikrotik $server): RedirectResponse
    {
        // $this->validate($request, [
        //     'host' => ['required', 'string', Rule::unique('mikrotiks')->ignore($server->slug, 'slug')],
        // ]);

        $this->validate($request, [
            'host' => ['required', 'string'],
            'port' => [
                'required',
                'numeric',
                Rule::unique('mikrotiks')->where(function ($query) use ($request) {
                    return $query->where('host', $request->host);
                })->ignore($server->id),
            ],
        ]);
        if ($request->password) {
            $server->update(['password' => $request->password]);
        }

        // $server->update([
        //     'name' => $request['name'],
        //     'host' => $request['host'],
        //     'username' => $request['username'],
        //     'port' => $request['port'],
        //     'ros_version_id' => $request->ros_version_id
        // ]);

        $updateData = [
            'name' => $request['name'],
            'username' => $request['username'],
            'ros_version_id' => $request->ros_version_id
        ];

        if ($request->host !== $server->host) {
            $updateData['host'] = $request->host;
        }

        if ($request->port != $server->port) {
            $updateData['port'] = $request->port;
        }

        if ($request->password) {
            $updateData['password'] = $request->password;
        }

        $server->update($updateData);

        return redirect()->route('servers.index')
            ->with('success', 'Mikrotik updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Mikrotik $server)
    {
        return view('servers.delete', compact('server'));
    }
    public function destroy(Mikrotik $server): RedirectResponse
    {
        $server->delete();
        return redirect()->route('servers.index')
            ->with('success', 'Server ' . $server->name . ' deleted successfully');
    }

    public function disable(Mikrotik $server)
    {
        $autoisolir = AutoIsolir::where('mikrotik_id', $server->id)->first();
        if ($autoisolir != NULL) {
            $autoisolir->update([
                'disabled' => 'true',
            ]);
        }
        $customers = Customer::where('mikrotik_id', $server->id)->get();
        if ($customers != NULL) {
            foreach ($customers as $customer) {
                $customer->update([
                    'disabled' => 'true',
                ]);
            }
        }

        $pakets = Paket::where('mikrotik_id', $server->id)->get();
        if ($pakets != NULL) {
            foreach ($pakets as $paket) {
                $paket->update([
                    'disabled' => 'true',
                ]);
            }
        }



        $server->update([
            'disabled' => 'true'
        ]);
        return redirect()->back()->with('success', 'Disable server successfully');
    }
    public function enable(Mikrotik $server)
    {

        $autoisolir = AutoIsolir::where('mikrotik_id', $server->id)->first();
        if ($autoisolir != NULL) {
            $autoisolir->update([
                'disabled' => 'false',
            ]);
        }
        $customers = Customer::where('mikrotik_id', $server->id)->get();
        if ($customers != NULL) {
            foreach ($customers as $customer) {
                $customer->update([
                    'disabled' => 'false',
                ]);
            }
        }

        // $pakets = Paket::where('mikrotik_id', $server->id)->get();
        // if ($pakets != NULL) {
        //     foreach ($pakets as $paket) {
        //         $paket->update([
        //             'disabled' => 'false',
        //         ]);
        //     }
        // }
        $server->update([
            'disabled' => 'false'
        ]);
        return redirect()->back()->with('success', 'Enable server successfully');
    }

    public function preview()
    {
        $servers = Mikrotik::all();
        // dd($mikrotiks);
        return view('servers.preview', compact('servers'));
    }

    public function showDataCustomer(Mikrotik $mikrotik)
    {
        $customers = $this->getCustomerDataFromMikrotik($mikrotik);
        $servers = Mikrotik::all();

        if (request()->ajax()) {
            return response()->json([
                'total' => $customers->total(),
                'serverName' => $mikrotik->name,
            ]);
        }

        return view('servers.preview', [
            'servers' => $servers,
            'selectedMikrotik' => $mikrotik,
            'customers' => $customers,
        ]);
    }

    private function getCustomerDataFromMikrotik(Mikrotik $mikrotik)
    {
        return Customer::where('mikrotik_id', $mikrotik->id)
            ->where('disabled', 'false')
            ->orderBy('name')
            ->paginate(50);
    }

    public function importCustomer(Mikrotik $mikrotik)
    {
        $traceId = (string) Str::uuid();
        $message = 'Import secret from database';

        try {
            LogHelper::logInfo(null, [], 200, $message . " started with mikrotik id: " . $mikrotik->id, $traceId);
            dispatch(new MigrateCustomerToRouter($mikrotik, $mikrotik))->onQueue('default');
            LogHelper::logInfo(null, [], 200, $message . " finish with mikrotik id: " . $mikrotik->id, $traceId);

            return response()->json(['message' => 'Import customer successfully started']);
        } catch (\Throwable $e) {
            LogHelper::logError(null, ["error" => $e->getMessage()], 400, "Failed " . $message, $traceId);
            return response()->json(['message' => 'An error occurred while starting the import'], 500);
        }
    }
}
