<?php

namespace App\Http\Controllers\Paket;

use App\Models\Paket\Paket;
use App\Models\Paket\PaketCategory;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\PaketStoreRequest;
use App\Http\Requests\PaketUpdateRequest;
use App\Models\Paket\PppProfile;
use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;

class PacketController extends Controller
{

    public function __construct()
    {
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
        $traceId = (string) Str::uuid();
        $pakets = Paket::orderBy('name', 'ASC')->paginate(20);
        $allPaketsCount = count($pakets);
        LogHelper::logInfo(null, [
            'pakets' => $pakets,
            'all_paket_count' => $allPaketsCount
        ], 200, 'Successfully get Index data', $traceId);
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
        $traceId = (string) Str::uuid();
        $pppprofiles = PppProfile::where('disabled', 'false')
            ->orderBy('profile_name')
            ->get(['id', 'profile_name']);
        $category = PaketCategory::where('is_active', true)->get();
        LogHelper::logInfo(null, [
            'pppProfiles' => $pppprofiles,
            'category' => $category
        ], 200, 'Successfully get data for create paket', $traceId);
        return view('packets.create', [
            'ppp_profiles' => $pppprofiles,
            'category' => $category
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaketStoreRequest $request)
    {
        $traceId = (string) Str::uuid();
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
        $data = $request->all();

        if ($request->has('price_formatted')) {
            $data['price'] = str_replace('.', '', $request->price_formatted);
        }

        $data['installation_fee'] = str_replace('.', '', $request->input('installation_fee_formatted', 0));

        unset($data['price_formatted']);
        unset($data['installation_fee_formatted']);

        Paket::create($data);

        LogHelper::logInfo(null, [
            'paket' => $data
        ], 200, 'Successfully create new paket', $traceId);
        return redirect()->route('packets.index')
            ->with('success', 'Paket created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Paket $packet)
    {
        $traceId = (string) Str::uuid();
        $packet->load(['paketCategory', 'billingCycle']);
        LogHelper::logInfo(null, [
            'paket' => $packet
        ], 200, 'Successfully show paket', $traceId);
        return view('packets.show', compact('packet'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Paket $packet)
    {
        $traceId = (string) Str::uuid();
        try {
            $pppprofiles = PppProfile::where('disabled', 'false')
                ->orderBy('profile_name')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->profile_name,
                    ];
                });
            $category = PaketCategory::where('is_active', true)->get();
            $packet = Paket::with('subscriptionDurations')->where('id', $packet->id)->firstOrFail();
            LogHelper::logInfo(null, [
                'paket' => $packet,
                'ppp_profiles' => $pppprofiles,
                'category' => $category
            ], 200, 'Successfully show paket', $traceId);
            return view('packets.edit', [
                'packet' => $packet,
                'pppprofiles' => $pppprofiles,
                'category' => $category
            ]);
        } catch (\Exception $e) {
            LogHelper::logError(null, [
                'paket' => $packet,
            ], 200, 'Failed to show paket', $traceId);
            return redirect()->back()->withErrors(['msg' => 'Please check again your profiles']);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PaketUpdateRequest $request, Paket $packet)
    {
        // dd($request, $packet);
        $traceId = (string) Str::uuid();

        $this->validate(
            $request,
            [
                'name' => 'required|unique:pakets,name,' . $packet->id,
                'price_formatted' => 'nullable|string',
                'installation_fee_formatted' => 'nullable|string'
            ],
            [
                'name.unique' => 'Name paket ' . $request->name . ' already exists.'
            ]
        );

        try {
            $data = $request->except(['price_formatted', 'installation_fee_formatted']);

            // Handle price conversion
            $data['price'] = $request->filled('price_formatted')
                ? (int)str_replace('.', '', $request->price_formatted)
                : 0;

            // Handle installation fee conversion
            $data['installation_fee'] = $request->filled('installation_fee_formatted')
                ? (int)str_replace('.', '', $request->installation_fee_formatted)
                : 0;

            // Validasi nilai minimal 0
            $data['price'] = max(0, $data['price']);
            $data['installation_fee'] = max(0, $data['installation_fee']);

            $packet->update($data);

            LogHelper::logInfo($request, [
                'packet_id' => $packet->id,
                'changes' => $packet->getChanges(),
                'updated_values' => [
                    'price' => $data['price'],
                    'installation_fee' => $data['installation_fee']
                ]
            ], 200, 'Successfully updated packet', $traceId);

            return redirect()->route('packets.index')
                ->with('success', 'Packet updated successfully');
        } catch (\Exception $e) {
            LogHelper::logError($request, [
                'packet_id' => $packet->id,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'input_data' => $request->except('_token', '_method')
            ], 500, 'Failed to update packet', $traceId);

            return redirect()->back()
                ->withInput()
                ->withErrors(['msg' => 'Failed to update packet. Please try again.']);
        }
    }

    public function enable(Paket $packet)
    {
        $traceId = (string) Str::uuid();
        $packet->disabled = 'false';
        $packet->save();
        LogHelper::logInfo(null, [
            'paket' => $packet
        ], 200, 'Successfully enable paket', $traceId);

        return redirect()->back()->with('success', 'Paket enabled successfully.');
    }

    public function disable(Paket $packet)
    {
        $traceId = (string) Str::uuid();
        $packet->disabled = 'true';
        $packet->save();
        LogHelper::logInfo(null, [
            'paket' => $packet
        ], 200, 'Successfully disable paket', $traceId);

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
        $traceId = (string) Str::uuid();
        $packet->forceDelete();
        LogHelper::logInfo(null, [
            'paket' => $packet
        ], 200, 'Successfully destroy paket', $traceId);
        return redirect()->route('packets.index')
            ->with('success', 'Packet ' . $packet->name . ' deleted successfully.');
    }
}
