<?php

namespace App\Http\Controllers\Network;

use App\Http\Controllers\Controller;
use App\Models\Network\Olt;
use App\Models\Network\Onu;
use App\Models\Network\OidMapping;
use App\Models\Network\OltUpdateLog;
use App\Services\Networks\SnmpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\LogHelper;
use App\Helpers\SnmpHelper;
use App\Jobs\Network\ScanOltsJob;
use App\Jobs\Network\CheckOltStatusJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Auth;

class OLTManagementController extends Controller
{
    public function index()
    {
        $entries = 25; // default entries per page, bisa diganti atau diambil dari request
        $query = Olt::with('onus', 'latestUpdateLog')->orderBy('id', 'asc');

        // Paginate langsung
        $olts = $query->paginate($entries);

        // Hitung status aktif/inaktif dari collection paginasi
        $activeOlts = $olts->filter(fn($olt) => $olt->display_status === 'Active')->count();
        $inActiveOlts = $olts->filter(fn($olt) => $olt->display_status === 'Inactive')->count();

        $totalOlts = Olt::count(); // total OLT di DB
        $totalOnus = Onu::count();

        $lastStatusCheck = now()->format('h:i:s A');
        $lastUpdate = now()->format('F d, Y \a\t h:i A');

        $oidMappings = OidMapping::pluck('vendor', 'id');

        // 🔹 Buat job untuk scan ulang (async)
        $jobId = Str::uuid()->toString();
        Cache::put("scan_progress_{$jobId}", [
            'current' => 0,
            'total'   => $totalOlts,
            'percent' => 0,
            'status'  => 'queued',
            'currentOlt' => null,
        ], 600);

        $username = Auth::user()?->name ?? 'System'; // username user yang login (nullable)

        $dataForView = [
            'totalOlts'       => $totalOlts,
            'activeOlts'      => $activeOlts,
            'inActiveOlts'    => $inActiveOlts,
            'totalOnus'       => $totalOnus,
            'olts'            => $olts,
            'lastStatusCheck' => $lastStatusCheck,
            'lastUpdate'      => $lastUpdate,
            'jobId'           => $jobId,
            'oidMappings'     => $oidMappings,
        ];

        // Log semua data yang dikirim ke view
        LogHelper::logInfo(null, $dataForView, 200, "Data yang dikirim ke view");

        ScanOltsJob::dispatch($jobId, $username);

        return view('networks.olt_managements.index', $dataForView);
    }

    public function create()
    {
        $oidMappings = OidMapping::all()->mapWithKeys(function ($item) {
            return [$item->vendor => $item->description]; // key=vendor, value=description
        });
        return view('networks.olt_managements.create', ['oidMappings' => $oidMappings]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'olt_name'     => 'required|string|max:255',
            'ip_address'   => 'required|string',
            'port'         => 'nullable|numeric',
            'community'    => 'nullable|string',
            'olt_type'     => [
                'required',
                'string',
                Rule::in(OidMapping::pluck('vendor')->toArray()),
            ],
        ]);

        // Default values
        $reqPort = $request->port ?? 161;
        $reqCommunity = $request->community ?? 'public';

        $oltVendor = OidMapping::where('vendor', $request->olt_type)->first();

        if (!$oltVendor) {
            return redirect()->back()
                ->withErrors(['olt_type' => 'Selected OLT type is invalid.'])
                ->withInput();
        }

        // ✅ Cek duplikasi berdasarkan IP + Port
        $exists = Olt::where('ip', $request->ip_address)
            ->where('port', $reqPort)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withErrors([
                    'ip_address' => "An OLT with IP {$request->ip_address} and port {$reqPort} already exists."
                ])
                ->withInput();
        }

        // Insert baru
        Olt::create([
            'name'      => $request->olt_name,
            'ip'        => $request->ip_address,
            'port'      => $reqPort,
            'community' => $reqCommunity,
            'version'   => 'v2c',
            'vendor'    => $oltVendor->vendor,
        ]);

        return redirect()
            ->route('olts.index')
            ->with('success', 'OLT stored successfully.');
    }

    public function show($oltId)
    {
        $olt = Olt::findOrFail($oltId);
        $onus = Onu::where('olt_id', $oltId)->get(['onu_index', 'name', 'status', 'rx_power', 'mac_address', 'last_updated']);
        $histories = OltUpdateLog::where('olt_id', $oltId)
            ->orderBy('updated_at', 'desc')
            ->get();

        // Hitungan summary
        $totalOnus   = $onus->count();
        $onlineOnus  = $onus->where('status', 'Up')->count();
        $offlineOnus = $onus->where('status', 'Down')->count();

        // Ambil last_updated terbaru
        $lastUpdateOnus = $onus->max('last_updated'); // setara dengan ORDER BY last_updated DESC LIMIT 1

        return view('networks.olt_managements.show', compact(
            'olt',
            'onus',
            'totalOnus',
            'onlineOnus',
            'offlineOnus',
            'lastUpdateOnus',
            'histories'
        ));
    }

    public function edit($oltId)
    {
        $olt = Olt::with('onus', 'latestUpdateLog')->where('id', $oltId)->first();
        $oidMappings = OidMapping::all()->mapWithKeys(function ($item) {
            return [$item->vendor => $item->description]; // key=vendor, value=description
        });
        // dd($olt);
        return view('networks.olt_managements.edit', [
            'olt' => $olt,
            'oidMappings' => $oidMappings
        ]);
    }

    public function update(Request $request, Olt $oltmanagement)
    {
        $request->validate([
            'olt_name'   => 'required|string|max:255',
            'ip_address' => 'required|string',
            'port'       => 'nullable|numeric',
            'community'  => 'nullable|string',
            'latitude'   => 'nullable|numeric',
            'longitude'  => 'nullable|numeric',
            'olt_type'   => [
                'required',
                'string',
                Rule::in(OidMapping::pluck('vendor')->toArray()),
            ],
        ]);

        if (!$oltmanagement->exists) {
            return redirect()->route('olts.index')->with('error', 'OLT not found.');
        }

        $reqPort = $request->port ?? 161;

        // ✅ Cek duplikasi (kecuali dirinya sendiri)
        $exists = Olt::where('ip', $request->ip_address)
            ->where('port', $reqPort)
            ->where('id', '!=', $oltmanagement->id)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withErrors([
                    'ip_address' => "Another OLT with IP {$request->ip_address} and port {$reqPort} already exists."
                ])
                ->withInput();
        }

        // Update fields
        $oltmanagement->fill([
            'name'      => $request->olt_name,
            'ip'        => $request->ip_address,
            'port'      => $reqPort,
            'community' => $request->community ?? 'public',
            'vendor'    => $request->olt_type,
            'latitude'  => $request->latitude,
            'longitude' => $request->longitude,
            'version'   => 'v2c',
        ]);

        try {
            $oltmanagement->save();
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Failed to update OLT: ' . $e->getMessage());
        }

        return redirect()->route('olts.index')->with('success', 'OLT updated successfully.');
    }

    public function delete(Olt $oltmanagement) {}

    public function destroy(Olt $oltmanagement)
    {
        try {
            // Hapus OLT
            $oltmanagement->delete();

            // Ambil semua OLT setelah delete
            $olts = Olt::with('onus', 'latestUpdateLog')->orderBy('id', 'asc')->get();

            // Hitung OLT aktif
            $activeOlts = $olts->filter(fn($olt) => $olt->display_status === 'Active')->count();

            // Total ONU
            $totalOnus = Onu::count();

            return response()->json([
                'success' => true,
                'message' => 'OLT deleted successfully.',
                'onlineOlts' => $activeOlts,
                'totalOnus' => $totalOnus,
                'totalOlts' => $olts->count()
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete OLT: ' . $e->getMessage()
            ], 500);
        }
    }

    public function filter(Request $request)
    {
        $search   = $request->search ?? null;
        $olt_type = $request->olt_type ?? null;
        $status   = strToUpper($request->status) ?? null;
        $entries  = $request->entries ?? 25;

        $query = Olt::with('onus', 'latestUpdateLog');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('ip', 'like', "%$search%");
            });
        }

        if ($olt_type) {
            $vendorName = OidMapping::find($olt_type)?->vendor;
            if ($vendorName) {
                $query->where('vendor', $vendorName);
            }
        }

        if ($status && $status != 0) {
            $statusMapping = match ($status) {
                Olt::STRING_STATUS_INACTIVE => Olt::STATUS_INACTIVE,
                Olt::STRING_STATUS_ACTIVE   => Olt::STATUS_ACTIVE,
                Olt::STRING_STATUS_SCANNING => Olt::STATUS_SCANNING,
            };
            $query->where('status', $statusMapping);
        }

        // Paginasi langsung
        $olts = $query->paginate($entries);
        $agent = new Agent();

        if ($agent->isMobile() || $agent->isTablet()) {
            $table = view('networks.partials.olts_table_mobile', compact('olts'))->render();
        } else {
            $table = view('networks.partials.olts_table_desktop', compact('olts'))->render();
        }

        return response()->json([
            'table'   => $table,
            'summary' => view('networks.partials.olts_summary', compact('olts'))->render(),
        ]);
    }

    public function testSNMP(Request $request)
    {
        $username = Auth::user()?->name ?? 'System'; // username user yang login (nullable)
        LogHelper::logInfo(null, ['request' => $request->all()], 200, 'Request data for OLT validation');

        // Ambil list vendor dari DB
        $vendors = OidMapping::pluck('vendor')->toArray();
        LogHelper::logInfo(null, ['vendors' => $vendors], 200, 'Valid vendors');

        $validator = Validator::make($request->all(), [
            'olt_name'   => 'required|string|max:255',
            'ip' => 'required|string',
            'port'       => 'nullable|numeric',
            'community'  => 'nullable|string',
            'olt_type'   => [
                'required',
                'string',
                Rule::in($vendors),
            ],
        ]);

        if ($validator->fails()) {
            LogHelper::logWarning(null, ['validator_error' => $validator->errors()->toArray()], 422, 'OLT validation failed');
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        LogHelper::logInfo(null, ['request' => $request->all()], 200, 'OLT validation passed');

        try {
            $totalOnu = 0;
            $oltVendor = OidMapping::where('vendor', $request->olt_type)->first();

            if (empty($oltVendor)) {
                return response()->json([
                    'success' => false,
                    'error'   => 'OID mapping not found.',
                ]);
            }

            // bikin object sederhana untuk dikirim ke service
            $olt = (object) [
                'name'      => $request->olt_name,
                'ip'        => $request->ip,
                'port'      => $request->port,
                'community' => $request->community,
                'vendor'    => $oltVendor->vendor,
                'version'   => 'v2c'
            ];

            $healthCheck = SnmpService::checkOltStatus($olt, $username);

            if ($healthCheck['status'] === 'Up') {
                $snmpData = SnmpService::getOnus($olt);
                $totalOnu = isset($snmpData['oid_name']) && is_array($snmpData['oid_name'])
                    ? count($snmpData['oid_name'])
                    : 0;

                LogHelper::logInfo(null, [
                    'result' => 'success',
                    'device_info' => $olt->vendor,
                    'onu_count' => $totalOnu
                ], 200, 'SNMP | Success Response');

                return response()->json([
                    'success'     => true,
                    'device_info' => $olt->vendor,
                    'onu_count'   => $totalOnu,
                ]);
            }

            LogHelper::logInfo(null, [
                'result' => 'failed',
            ], 200, 'SNMP | Failed Response');

            return response()->json([
                'success' => false,
                'device_info' => $olt->vendor,
                'onu_count'   => $totalOnu,
                'error'   => 'Could not establish SNMP connection to the device',
            ]);
        } catch (\Throwable $e) {
            LogHelper::logInfo(null, [
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ], 500, 'SNMP | Exception Caught');

            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function startScan(Request $request)
    {
        $jobId = Str::uuid()->toString();

        $query = Olt::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('ip_address', 'like', "%$search%");
            });
        }

        if ($request->filled('olt_type')) {
            $query->where('vendor', $request->olt_type);
        }

        if ($request->filled('status') && $request->status != "0") {
            $query->where('status', $request->status);
        }

        // entries tidak dipakai untuk filter data OLT, skip

        $total = $query->count();

        Cache::put("scan_progress_{$jobId}", [
            'current' => 0,
            'total'   => $total,
            'percent' => 0,
            'status'  => 'queued',
            'currentOlt' => null,
        ], 600);

        // Dispatch job dengan parameter filter
        ScanOltsJob::dispatch($jobId, $request->all());

        return response()->json(['jobId' => $jobId]);
    }

    public function scanStatus($jobId)
    {
        return response()->json(
            Cache::get("scan_progress_{$jobId}", ['status' => 'not_found'])
        );
    }

    public function checkAllStatusJob(Request $request)
    {
        // hapus cache lama
        Cache::forget('olt-status-progress');
        Cache::forget('olt-status-all');
        Cache::forget('olt-status-total');

        // ambil semua OLT
        $olts = Olt::all();

        // set semua OLT ke status Scanning dulu
        $allStatus = $olts->mapWithKeys(fn($olt) => [
            $olt->id => ['status' => 'Scanning', 'name' => $olt->name]
        ])->toArray();

        Cache::put('olt-status-all', $allStatus, now()->addMinutes(5));
        Cache::put('olt-status-total', count($olts), now()->addMinutes(5));
        Cache::put('olt-status-progress', [
            'current' => 0,
            'total' => count($olts),
            'statuses' => $allStatus,
            'currentOltName' => $olts->first()->name ?? null
        ], now()->addMinutes(5));

        // dispatch job untuk setiap OLT
        foreach ($olts as $olt) {
            LogHelper::logInfo(null, ['olts' => $olt], 200, "olt desc");
            CheckOltStatusJob::dispatch($olt);
        }

        return response()->json([
            'message' => 'Status check job dispatched',
        ]);
    }

    public function getOltStatusProgress()
    {
        $progress = Cache::get('olt-status-progress', [
            'current' => 0,
            'total' => Olt::count(),
            'statuses' => [],
            'currentOltName' => null, // default kalau cache belum ada
        ]);

        return response()->json($progress);
    }

    public function scanSingle($oltId)
    {
        return $this->handleOltCheck($oltId, includeOnus: true, action: 'OLT Scan');
    }

    public function healthCheckSingle($oltId)
    {
        return $this->handleOltCheck($oltId, includeOnus: false, action: 'OLT Health Check');
    }

    /**
     * Helper untuk scan atau health check OLT
     */
    private function handleOltCheck(int $oltId, bool $includeOnus = false, string $action = 'Check')
    {
        $username = Auth::user()?->name ?? 'System'; // username user yang login (nullable)
        $olt = Olt::find($oltId);

        if (!$olt) {
            return response()->json([
                'success' => false,
                'olt_name' => null,
                'status' => 'Scanning',
                'total_onu' => 0,
                'message' => "OLT ID {$oltId} tidak ditemukan"
            ], 404);
        }

        try {
            $healthCheck = SnmpService::checkOltStatus($olt, $username);
            $status = SnmpHelper::mapSnmpStatus($healthCheck['status']);

            $response = [
                'success' => true,
                'olt_name' => $olt->name,
                'status' => $status,
                'message' => "Successfully get {$action}"
            ];

            if ($status['status'] === 'Up' && $includeOnus) {
                $snmpData = SnmpService::getOnus($olt);
                SnmpService::updateOnus($olt, $snmpData['snmpData'], $snmpData['duration'], $username);
                $response['total_onu'] = Onu::where('olt_id', $oltId)->count();
            }

            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'olt_name' => $olt->name,
                'status' => 'Inactive',
                'total_onu' => 0,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
