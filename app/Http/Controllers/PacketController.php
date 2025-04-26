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
use App\Models\Paket\PppProfile;
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
        $pakets = Paket::orderBy('name', 'ASC')->paginate(20);
        $allPaketsCount = count($pakets);
        return view('packets.index', [
            'pakets' =>  $pakets,
            'paketscount' => $allPaketsCount,
        ])
            ->with('i', (request()->input('page', 1) - 1) * 20);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //return dd($request->mikrotik_id);
        // $mikrotiks = Mikrotik::orderBy('name', 'ASC')->pluck('name', 'id');
        $pppprofiles = PppProfile::where('disabled', 'false')
            ->orderBy('profile_name')
            ->get(['id', 'profile_name']);

        return view('packets.create', [
            // 'mikrotiks' => $mikrotiks,
            'ppp_profiles' => $pppprofiles,

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
                'name' => 'required|unique:pakets,name'
            ],
            [
                'name.unique' => 'Name paket ' . $request->name . ' already exist.'
            ]
        );

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
        //$router = Mikrotik::where('id', $packet->mikrotik_id)->first();
        try {
            //$this->testConnectionService->checkOnline($router);
            //$profiles = $this->secretService->getPppProfile($router);
            // $pppprofiles = ProfileResource::collection($profiles);
            $pppprofiles = PppProfile::where('disabled', 'false')
                ->orderBy('profile_name') // optional: biar urut
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->profile_name,
                    ];
                });
            // dd($pppprofiles);
            return view('packets.edit', [
                'packet' => $packet,
                'pppprofiles' => $pppprofiles,
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['msg' => 'Please check again your profiles']);
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
                'name' => 'required|unique:pakets,name,' . $packet->id
            ],
            [
                'name.unique' => 'Name paket ' . $request->name . ' already exists.'
            ]
        );

        $packet->update($request->all());

        return redirect()->route('packets.index')
            ->with('success', 'Packet updated successfully');
    }

    public function enable(Paket $packet)
    {
        // dd($packet);
        $packet->disabled = 'false';
        $packet->save();

        return redirect()->back()->with('success', 'Paket enabled successfully.');
    }

    public function disable(Paket $packet)
    {
        $packet->disabled = 'true';
        $packet->save();

        return redirect()->back()->with('success', 'Paket disabled successfully.');
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
        $packet->forceDelete();
        return redirect()->route('packets.index')
            ->with('success', 'Packet ' . $packet->name . ' deleted successfully.');
    }
}
