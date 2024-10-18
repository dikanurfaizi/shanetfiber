<?php

namespace App\Http\Controllers;

use App\Models\Mikrotik;
use Illuminate\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\ServerStoreRequest;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\ServerUpdateRequest;
use App\Models\AutoIsolir;
use App\Models\Customer;
use App\Models\Paket;
use App\Services\Routerboard\SystemService;



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
        $this->validate($request, [
            'host' => ['required', 'string', Rule::unique('mikrotiks')->ignore($server->slug, 'slug')],
        ]);
        if ($request->password) {
            $server->update(['password' => $request->password]);
        }

        $server->update([
            'name' => $request['name'],
            'host' => $request['host'],
            'username' => $request['username'],
            'port' => $request['port'],
        ]);
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

        $pakets = Paket::where('mikrotik_id', $server->id)->get();
        if ($pakets != NULL) {
            foreach ($pakets as $paket) {
                $paket->update([
                    'disabled' => 'false',
                ]);
            }
        }
        $server->update([
            'disabled' => 'false'
        ]);
        return redirect()->back()->with('success', 'Enable server successfully');
    }
}
