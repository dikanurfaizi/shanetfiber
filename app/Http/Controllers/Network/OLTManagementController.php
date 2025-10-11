<?php

namespace App\Http\Controllers\Network;

use App\Http\Controllers\Controller;
use App\Services\Networks\OltService;
use App\Services\Networks\OltScanService;
use App\Services\Networks\OltValidationService;
use App\Helpers\LogHelper;
use App\Jobs\Network\ScanOltsJob;
use App\Models\Network\OidMapping;
use App\Models\Network\Olt;
use App\Models\Network\Ont;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;

class OLTManagementController extends Controller
{
    public function __construct(
        private OltService $oltService,
        private OltScanService $scanService,
        private OltValidationService $validationService
    ) {}

    public function index()
    {
        $traceId = Str::uuid()->toString();
        Cache::forget('olt-status-progress');
        Cache::forget('olt-status-all');
        Cache::forget('olt-status-total');

        $entries = 25;
        $olts = $this->oltService->getPaginatedOlts($entries, $traceId);
        $statistics = $this->oltService->getOltStatistics($traceId);

        $username = Auth::user()?->name ?? 'System';

        // ScanOltsJob::dispatch($jobId, $username)->onQueue('default');
        // FetchMikrotikInfo::dispatch($jobId)->onQueue('mikrotik-heavy');

        $dataForView = array_merge($statistics, [
            'olts' => $olts,
            'lastStatusCheck' => now()->format('h:i:s A'),
            'lastUpdate' => now()->format('F d, Y \a\t h:i A'),
            'oidMappings' => OidMapping::pluck('vendor', 'id'),
        ]);

        LogHelper::logInfo(null, [
            'username' => $username,
            'entries' => $entries,
            'page' => $olts->currentPage(),
            'per_page' => $olts->perPage(),
        ], 200, "Index OLT Management - Data ready to sent to blade view", $traceId);

        return view('networks.olt_managements.index', $dataForView);
    }

    public function create()
    {
        $oidMappings = OidMapping::all()->mapWithKeys(function ($item) {
            return [$item->id => $item->vendor];
        });

        return view('networks.olt_managements.create', ['oidMappings' => $oidMappings]);
    }

    public function store(Request $request)
    {
        $traceId = Str::uuid()->toString();
        $username = Auth::user()?->name ?? 'System';
        try {
            $olt = $this->oltService->createOlt($request->all());
            $this->scanService->scanSingleOlt($olt->id, $username, $traceId);

            return redirect()
                ->route('olts.index')
                ->with('success', 'OLT stored successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function show($oltId, Request $request)
    {
        $data = $this->oltService->getOltDetails($request, $oltId);
        return view('networks.olt_managements.show', $data);
    }

    public function edit($oltId)
    {
        $olt = $this->oltService->findOltWithDetails($oltId);
        $oidMappings = OidMapping::pluck('vendor', 'id');

        return view('networks.olt_managements.edit', compact('olt', 'oidMappings'));
    }

    public function update(Request $request, Olt $oltmanagement)
    {
        try {
            $this->oltService->updateOlt($oltmanagement, $request->all());

            return redirect()->route('olts.index')->with('success', 'OLT updated successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Failed to update OLT: ' . $e->getMessage());
        }
    }

    public function destroy(Olt $oltmanagement)
    {
        try {
            $this->oltService->deleteOlt($oltmanagement);

            $olts = Olt::with('onts', 'latestUpdateLog')->orderBy('id', 'asc')->get();
            $activeOlts = $olts->filter(fn($olt) => $olt->display_status === 'Active')->count();
            $totalOnts = Ont::count();

            return response()->json([
                'success' => true,
                'message' => 'OLT deleted successfully.',
                'onlineOlts' => $activeOlts,
                'totalOnts' => $totalOnts,
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
        $olts = $this->oltService->filterOlts($request->all());
        $agent = new Agent();

        if ($agent->isMobile() || $agent->isTablet()) {
            $table = view('networks.partials.olts_table_mobile', compact('olts'))->render();
        } else {
            $table = view('networks.partials.olts_table_desktop', compact('olts'))->render();
        }

        return response()->json([
            'table' => $table,
            'summary' => view('networks.partials.olts_summary', compact('olts'))->render(),
        ]);
    }

    public function testSNMP(Request $request)
    {
        $traceId = Str::uuid()->toString();
        $username = Auth::user()?->name ?? 'System';

        try {
            $result = $this->validationService->testSnmpConnection($request->all(), $username);
            LogHelper::logInfo(null, ['result' => $result], 200, "Successfully test SNMP Connection", $traceId);
            return response()->json($result);
        } catch (\InvalidArgumentException $e) {
            LogHelper::logError(null, [
                'request' => $request->all(),
                'success' => false,
                'errors' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ],
            ], 500, "Failed test SNMP Connection", $traceId);
            return response()->json([
                'success' => false,
                'errors' => ['general' => $e->getMessage()],
            ], 422);
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'request' => $request->all(),
                'success' => false,
                'errors' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            ], 500, 'SNMP | Exception Caught');

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function startScan(Request $request)
    {
        $username = Auth::user()?->name ?? 'System';
        $jobId = Str::uuid()->toString();

        ScanOltsJob::dispatch($jobId, $username);

        return response()->json([
            'message'   => 'Scan job dispatched successfully',
            'job_id'    => $jobId,
        ]);
    }

    public function scanStatus($jobId)
    {
        $cacheData = $this->scanService->getScanProgress($jobId);

        LogHelper::logInfo(
            "scan_status_{$jobId}",
            ['jobId' => $jobId, 'cacheData' => $cacheData],
            200,
            "Scan status diambil dari cache"
        );

        return response()->json($cacheData);
    }

    public function checkAllStatusJob(Request $request)
    {
        $jobId = Str::uuid()->toString();
        $username = Auth::user()?->name ?? 'System';

        // Dispatch background job dengan jobId
        ScanOltsJob::dispatch($jobId, $username);

        return response()->json([
            'message'   => 'Status check job dispatched successfully',
            'job_id'    => $jobId,
        ]);
    }

    public function scanSingle($oltId)
    {
        $traceId = Str::uuid()->toString();
        $username = Auth::user()?->name ?? 'System';

        try {
            $result = $this->scanService->scanSingleOlt($oltId, $username, $traceId);
            LogHelper::logInfo(null, ['result' => $result], 200, "Successfully scanSingle", $traceId);
            return response()->json($result);
        } catch (\InvalidArgumentException $e) {
            LogHelper::logInfo(null, [
                'success' => false,
                'olt_name' => null,
                'status' => 'Scanning',
                'total_ont' => 0,
                'message' => $e->getMessage()
            ], 500, "Failed scanSingle", $traceId);
            return response()->json([
                'success' => false,
                'olt_name' => null,
                'status' => 'Scanning',
                'total_ont' => 0,
                'message' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            LogHelper::logInfo(null, [
                'success' => false,
                'olt_name' => null,
                'status' => 'Inactive',
                'total_ont' => 0,
                'message' => $e->getMessage()
            ], 500, "Failed scanSingle: unknown response", $traceId);
            return response()->json([
                'success' => false,
                'olt_name' => null,
                'status' => 'Inactive',
                'total_ont' => 0,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function healthCheckSingle($oltId)
    {
        $traceId = Str::uuid()->toString();
        $username = Auth::user()?->name ?? 'System';

        try {
            $result = $this->scanService->healthCheckSingleOlt($oltId, $username, $traceId);
            LogHelper::logInfo(null, ['result' => $result], 200, "Successfully healthCheckSingle", $traceId);
            return response()->json($result);
        } catch (\InvalidArgumentException $e) {
            LogHelper::logInfo(null, [
                'success' => false,
                'olt_name' => null,
                'status' => 'Scanning',
                'total_ont' => 0,
                'message' => $e->getMessage()
            ], 500, "Failed healthCheckSingle", $traceId);
            return response()->json([
                'success' => false,
                'olt_name' => null,
                'status' => 'Scanning',
                'total_ont' => 0,
                'message' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            LogHelper::logInfo(null, [
                'success' => false,
                'olt_name' => null,
                'status' => 'Inactive',
                'total_ont' => 0,
                'message' => $e->getMessage()
            ], 500, "Failed healthCheckSingle: unknown response", $traceId);
            return response()->json([
                'success' => false,
                'olt_name' => null,
                'status' => 'Inactive',
                'total_ont' => 0,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function clearCache($jobId)
    {
        $this->scanService->clearScanCache($jobId);

        return response()->json([
            'message' => "Cache cleared for job {$jobId}",
        ]);
    }
}
