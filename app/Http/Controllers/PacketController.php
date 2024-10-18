<?php

namespace App\Http\Controllers;

use App\Models\Paket;
//use App\Models\Packet;
use App\Models\Mikrotik;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Requests\PaketStoreRequest;
use App\Http\Requests\PaketUpdateRequest;
use App\Http\Resources\Pppoe\SecretResource;
use App\Http\Resources\Pppoe\ProfileResource;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\TestConnectionService;

class PacketController extends Controller
{
    private SecretService $secretService;
    private TestConnectionService $testConnectionService;

    public function __construct(SecretService $secretService, TestConnectionService $testConnectionService)
    {
        $this->secretService = $secretService;
        $this->testConnectionService = $testConnectionService;
        $this->middleware('permission:packet-list|packet-create|packet-edit|packet-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:packet-create', ['only' => ['create', 'store', 'setting', 'exportToRouter']]);
        $this->middleware('permission:packet-edit', ['only' => ['edit', 'update', 'setting', 'exportToRouter', 'enable', 'disable']]);
        $this->middleware('permission:packet-delete', ['only' => ['destroy', 'delete']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pakets = Paket::orderBy('name', 'ASC')->paginate(10);
        $allPaketsCount = count($pakets);
        return view('packets.index', [
            'pakets' =>  $pakets,
            'paketscount' => $allPaketsCount,
        ])
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //return dd($request->mikrotik_id);
        $mikrotiks = Mikrotik::orderBy('name', 'ASC')->pluck('name', 'id');

        return view('packets.create', [
            'mikrotiks' => $mikrotiks,

        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaketStoreRequest $request)
    {
        $this->validate(
            $request,
            [
                // 'customer_id' => 'required|unique:billings,customer_id,NULL,name,profile,' . $month,
                'name' => 'required|unique:pakets,name,null,null,mikrotik_id,' . $request->mikrotik_id
            ],
            [
                'name.unique' => 'Name paket ' . $request->name . ' already exist in server '

            ]
        );
        $request->request->add(['mikrotik_id' => $request->mikrotik_id]);
        $request->request->add(['slug' => str($request->name . '-' . Str::random(4))->slug()]);
        Paket::create($request->all());

        return redirect()->route('packets.index')
            ->with('success', 'Paket created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Paket $packet)
    {
        return view('packets.show', compact('packet'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Paket $packet)
    {
        $router = Mikrotik::where('id', $packet->mikrotik_id)->first();
        try {
            //$this->testConnectionService->checkOnline($router);
            $profiles = $this->secretService->getPppProfile($router);
            $pppprofiles = ProfileResource::collection($profiles);
            return view('packets.edit', compact('packet'), [
                'pppprofiles' => $pppprofiles,
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['msg' => 'Router offline, please check your router.', 'Server ' . $router->name . ' Offline']);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PaketUpdateRequest $request, Paket $packet)
    {
        $this->validate(
            $request,
            [
                'name' => 'required|unique:pakets,name,' . $packet->id . ',id,mikrotik_id,' . $packet->mikrotik_id
            ],

            [
                'name.unique' => 'Name paket ' . $request->name . ' already exist in server '

            ]
        );

        $packet->update($request->all());
        return redirect()->route('packets.index')
            ->with('success', 'Packet updated successfully');
    }

    public function enable(Paket $packet)
    {
        $packet->update([
            'disabled' => 'false',
        ]);
        return redirect()->back()->with('success', 'Paket enable');
    }

    public function disable(Paket $packet)
    {
        $packet->update([
            'disabled' => 'true',
        ]);
        return redirect()->back()->with('success', 'Paket disable');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Paket $packet)
    {

        return view('packets.delete', compact('packet'));
    }
    public function destroy(Paket $packet)
    {
        $packet->delete();
        return redirect()->route('packets.index')
            ->with('success', 'Packet ' . $packet->name . ' deleted successfully.');
    }
}
