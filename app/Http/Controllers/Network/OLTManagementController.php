<?php

namespace App\Http\Controllers\Network;

use App\Http\Controllers\Controller;
use App\Services\Networks\OltService;
use App\Services\Networks\OltScanService;
use App\Services\Networks\OltValidationService;
use App\Helpers\LogHelper;
use App\Jobs\Network\ScanOltsJob;
use App\Jobs\Network\CheckOltStatusJob;
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
        Cache::forget('olt-status-progress');
        Cache::forget('olt-status-all');
        Cache::forget('olt-status-total');

        $entries = 25;
        $olts = $this->oltService->getPaginatedOlts($entries);
        $statistics = $this->oltService->getOltStatistics();

        $jobId = Str::uuid()->toString();
        $username = Auth::user()?->name ?? 'System';

        ScanOltsJob::dispatch($jobId, $username);

        // dd($olts);

        $dataForView = array_merge($statistics, [
            'olts' => $olts,
            'lastStatusCheck' => now()->format('h:i:s A'),
            'lastUpdate' => now()->format('F d, Y \a\t h:i A'),
            'jobId' => $jobId,
            'oidMappings' => OidMapping::pluck('vendor', 'id'),
        ]);

        LogHelper::logInfo($jobId, [
            'username' => $username,
            'entries' => $entries,
            'statistics' => $statistics,
            'page' => $olts->currentPage(),
            'per_page' => $olts->perPage(),
        ], 200, "Index OLT Management - Data siap dikirim ke view");

        ScanOltsJob::dispatch($jobId, $username);

        return view('networks.olt_managements.index', $dataForView);
    }

    public function create()
    {
        $oidMappings = OidMapping::all()->mapWithKeys(function ($item) {
            return [$item->vendor => $item->description];
        });

        return view('networks.olt_managements.create', ['oidMappings' => $oidMappings]);
    }

    public function store(Request $request)
    {
        try {
            $this->oltService->createOlt($request->all());

            return redirect()
                ->route('olts.index')
                ->with('success', 'OLT stored successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function show($oltId)
    {
        $data = $this->oltService->getOltDetails($oltId);
        return view('networks.olt_managements.show', $data);
    }

    public function edit($oltId)
    {
        $olt = $this->oltService->findOltWithDetails($oltId);
        $oidMappings = OidMapping::all()->mapWithKeys(function ($item) {
            return [$item->vendor => $item->description];
        });

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
        $username = Auth::user()?->name ?? 'System';

        try {
            $result = $this->validationService->testSnmpConnection($request->all(), $username);
            return response()->json($result);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'errors' => ['general' => $e->getMessage()],
            ], 422);
        } catch (\Throwable $e) {
            LogHelper::logInfo(null, [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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

        $jobId = $this->scanService->startBulkScan($request->all());

        ScanOltsJob::dispatch($jobId, $username);

        return response()->json(['jobId' => $jobId]);
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
        $username = Auth::user()?->name ?? 'System';
        $this->scanService->initializeStatusCheck();

        $olts = Olt::all();
        foreach ($olts as $olt) {
            CheckOltStatusJob::dispatch($olt, $username);
        }

        return response()->json([
            'message' => 'Status check job dispatched',
        ]);
    }

    public function getOltStatusProgress()
    {
        $progress = $this->scanService->getStatusCheckProgress();
        return response()->json($progress);
    }

    public function scanSingle($oltId)
    {
        $username = Auth::user()?->name ?? 'System';

        try {
            $result = $this->scanService->scanSingleOlt($oltId, $username);
            return response()->json($result);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'olt_name' => null,
                'status' => 'Scanning',
                'total_ont' => 0,
                'message' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
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
        $username = Auth::user()?->name ?? 'System';

        try {
            $result = $this->scanService->healthCheckSingleOlt($oltId, $username);
            return response()->json($result);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'olt_name' => null,
                'status' => 'Scanning',
                'total_ont' => 0,
                'message' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'olt_name' => null,
                'status' => 'Inactive',
                'total_ont' => 0,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
