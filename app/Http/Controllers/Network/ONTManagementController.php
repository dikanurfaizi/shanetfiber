<?php

namespace App\Http\Controllers\Network;

use App\Http\Controllers\Controller;
use App\Models\Network\Olt;
use App\Models\Network\Ont;
use App\Models\Network\SignalThreshold;
use Illuminate\Http\Request;
use App\Services\Networks\Ont\OntDataService;

class ONTManagementController extends Controller
{
    public function index(Request $request)
    {
        // dd($request->all());
        $perPage = $request->get('perPage', 64);
        $olts = Olt::orderBy('id')->pluck('name', 'id');
        $signal = SignalThreshold::sorted()->pluck('description', 'id');
        $summary = OntDataService::summaryCounts();

        $data = [
            'status' => $request->status ?? '',
            'olt_id' => $request->olt_id ?? '',
            'signal_id' => $request->signal_id ?? '',
            'search' => $request->search ?? ''
        ];

        $onts = OntDataService::fetchWithRelationsPaginated($perPage, $data);

        // Kirim ke view
        return view('networks.ont_managements.index', compact(
            'olts',
            'signal',
            'summary',
            'onts'
        ));
    }

    public function create() {}
    public function store(Request $request) {}
    public function show(ONT $ont, Request $request) {}
    public function edit(ONT $ont) {}
    public function update(Request $request, ONT $ont) {}
    public function delete(ONT $ont) {}
    public function destroy(ONT $ont) {}
}
