<?php

namespace App\Http\Controllers\Network;

use App\Http\Controllers\Controller;
use App\Helpers\LogHelper;
use App\Jobs\Network\ScanOltsJob;
use App\Models\Network\OidMapping;
use App\Models\Network\Olt;
use App\Models\Network\Ont;
use App\Services\Networks\Olt\OltDataService;
use App\Services\Networks\Olt\OltScanJobService;
use App\Services\Networks\Olt\OltValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Throwable;

class OLTManagementController extends Controller
{
    /**
     * Menampilkan halaman utama OLT Management.
     */
    public function index()
    {
        $traceId = Str::uuid()->toString();
        $username = Auth::user()?->name ?? 'System';
        LogHelper::logInfo(null, ['user' => $username], 200, '[START] Load OLT Management Index', $traceId);

        try {
            Cache::forget('olt-status-progress');
            Cache::forget('olt-status-all');
            Cache::forget('olt-status-total');

            $entries = 25;
            $olts = OltDataService::getPaginatedOlts($entries, $traceId);
            $statistics = OltDataService::getOltStatistics($traceId);

            $dataForView = array_merge($statistics, [
                'olts' => $olts,
                'lastStatusCheck' => now()->format('H:i:s'),
                'lastUpdate' => now()->format('Y-m-d H:i:s'),
                'oidMappings' => OidMapping::pluck('vendor', 'id'),
            ]);

            LogHelper::logInfo(null, [
                'entries' => $entries,
                'total_olts' => $olts->total(),
                'page' => $olts->currentPage(),
            ], 200, '[SUCCESS] OLT Management data loaded', $traceId);

            return view('networks.olt_managements.index', $dataForView);
        } catch (Throwable $e) {
            LogHelper::logError(null, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, '[FAILED] Load OLT Management Index', $traceId);
            abort(500, 'Failed to load OLT Management.');
        }
    }

    public function create()
    {
        $traceId = Str::uuid()->toString();
        $oidMappings = OidMapping::all()->mapWithKeys(function ($item) {
            return [$item->id => $item->vendor];
        });

        return view('networks.olt_managements.create', ['oidMappings' => $oidMappings]);
    }

    public function store(Request $request)
    {
        $traceId = Str::uuid()->toString();
        $username = Auth::user()?->name ?? 'System';
        LogHelper::logInfo(null, ['user' => $username], 200, '[START] Create OLT', $traceId);

        try {
            $olt = OltDataService::createOlt($request->all());
            LogHelper::logInfo(null, [
                'olt_id' => $olt->id,
                'olt_name' => $olt->name,
                'ip' => $olt->ip,
            ], 200, '[SUCCESS] OLT Created', $traceId);

            OltScanJobService::scanSingleOlt($olt->id, $username, $traceId);
            LogHelper::logInfo(null, ['olt_id' => $olt->id], 200, '[DISPATCH] Single OLT scan job started', $traceId);

            return redirect()->route('olts.index')->with('success', 'OLT stored successfully.');
        } catch (\InvalidArgumentException $e) {
            LogHelper::logWarning(null, [
                'input' => $request->all(),
                'error' => $e->getMessage(),
            ], 422, '[VALIDATION FAILED] OLT Store', $traceId);
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        } catch (Throwable $e) {
            LogHelper::logError(null, [
                'input' => $request->all(),
                'error' => $e->getMessage(),
            ], 500, '[FAILED] OLT Store', $traceId);
            return back()->with('error', 'Failed to store OLT: ' . $e->getMessage());
        }
    }

    public function show($oltId, Request $request)
    {
        $data = OltDataService::getOltDetails($request, $oltId);
        return view('networks.olt_managements.show', $data);
    }

    public function edit($oltId)
    {
        $traceId = Str::uuid()->toString();
        $olt = OltDataService::findOltWithDetails($oltId);
        $oidMappings = OidMapping::pluck('vendor', 'id');

        return view('networks.olt_managements.edit', compact('olt', 'oidMappings'));
    }

    public function update(Request $request, Olt $oltmanagement)
    {
        $traceId = Str::uuid()->toString();
        try {
            OltDataService::updateOlt($oltmanagement, $request->all());
            LogHelper::logInfo(null, [
                'input' => $request->all(),
                'olt' => $oltmanagement
            ], 200, '[SUCCESS] OLT Updated', $traceId);
            return redirect()->route('olts.index')->with('success', 'OLT updated successfully.');
        } catch (\InvalidArgumentException $e) {
            LogHelper::logError(null, [
                'input' => $request->all(),
                'olt' => $oltmanagement,
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            ], 500, '[VALIDATION FAILED] OLT Update', $traceId);
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'input' => $request->all(),
                'olt' => $oltmanagement,
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            ], 500, '[FAILED] OLT Update', $traceId);
            return redirect()->back()
                ->with('error', 'Failed to update OLT: ' . $e->getMessage());
        }
    }

    public function destroy(Olt $olt)
    {
        $traceId = Str::uuid()->toString();
        $username = Auth::user()?->name ?? 'System';
        LogHelper::logInfo(null, [
            'user' => $username,
            'olt_id' => $olt->id,
        ], 200, '[START] Delete OLT', $traceId);

        try {
            OltDataService::deleteOlt($olt);
            $olts = Olt::with('onts', 'latestUpdateLog')->get();

            LogHelper::logInfo(null, [
                'olt_id' => $olt->id,
                'remaining_olts' => $olts->count(),
            ], 200, '[SUCCESS] OLT Deleted', $traceId);

            return response()->json([
                'success' => true,
                'message' => 'OLT deleted successfully.',
                'totalOlts' => $olts->count(),
                'totalOnts' => Ont::count(),
                'onlineOlts' => $olts->where('display_status', 'Active')->count(),
            ]);
        } catch (Throwable $e) {
            LogHelper::logError(null, [
                'olt_id' => $olt->id,
                'error' => $e->getMessage(),
            ], 500, '[FAILED] Delete OLT', $traceId);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function filter(Request $request)
    {
        $traceId = Str::uuid()->toString();
        $olts = OltDataService::filterOlts($request->all());
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
        LogHelper::logInfo(null, [
            'user' => $username,
            'ip' => $request->ip_address,
        ], 200, '[START] Test SNMP Connection', $traceId);

        try {
            $result = OltValidationService::testSnmpConnection($request->all(), $username);
            LogHelper::logInfo(null, ['result' => $result], 200, '[SUCCESS] Test SNMP Connection', $traceId);
            return response()->json($result);
        } catch (\InvalidArgumentException $e) {
            LogHelper::logWarning(null, [
                'request' => $request->all(),
                'error' => $e->getMessage(),
            ], 422, '[VALIDATION FAILED] Test SNMP Connection', $traceId);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            LogHelper::logError(null, [
                'request' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, '[FAILED] Test SNMP Connection', $traceId);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function startScan(Request $request)
    {
        $traceId = Str::uuid()->toString();
        $username = Auth::user()?->name ?? 'System';
        LogHelper::logInfo(null, ['user' => $username], 200, '[START] Dispatch Bulk Scan Job', $traceId);

        try {
            $jobId = Str::uuid()->toString();
            ScanOltsJob::dispatch($jobId, $username);
            LogHelper::logInfo(null, ['job_id' => $jobId], 200, '[DISPATCHED] ScanOltsJob', $traceId);
            return response()->json(['message' => 'Scan job dispatched successfully', 'job_id' => $jobId]);
        } catch (Throwable $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, '[FAILED] Dispatch Scan Job', $traceId);
            return response()->json(['error' => 'Failed to start scan job.'], 500);
        }
    }

    public function scanStatus($jobId)
    {
        $traceId = Str::uuid()->toString();
        LogHelper::logInfo(null, ['jobId' => $jobId], 200, '[START] Get Scan Status', $traceId);

        try {
            $cacheData = OltScanJobService::getScanProgress($jobId);
            LogHelper::logInfo(null, ['cacheData' => $cacheData], 200, '[SUCCESS] Scan Status Retrieved', $traceId);
            return response()->json($cacheData);
        } catch (Throwable $e) {
            LogHelper::logError(null, [
                'jobId' => $jobId,
                'error' => $e->getMessage(),
            ], 500, '[FAILED] Get Scan Status', $traceId);
            return response()->json(['error' => 'Failed to get scan status'], 500);
        }
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
        LogHelper::logInfo(null, ['olt_id' => $oltId, 'user' => $username], 200, '[START] Scan Single OLT', $traceId);

        try {
            $result = OltScanJobService::scanSingleOlt($oltId, $username, $traceId);
            LogHelper::logInfo(null, ['result' => $result], 200, '[SUCCESS] Scan Single OLT', $traceId);
            return response()->json($result);
        } catch (Throwable $e) {
            LogHelper::logError(null, [
                'olt_id' => $oltId,
                'error' => $e->getMessage(),
            ], 500, '[FAILED] Scan Single OLT', $traceId);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function healthCheckSingle($oltId)
    {
        $traceId = Str::uuid()->toString();
        $username = Auth::user()?->name ?? 'System';
        LogHelper::logInfo(null, ['olt_id' => $oltId], 200, '[START] Health Check Single OLT', $traceId);

        try {
            $result = OltScanJobService::healthCheckSingleOlt($oltId, $username, $traceId);
            LogHelper::logInfo(null, ['result' => $result], 200, '[SUCCESS] Health Check Single OLT', $traceId);
            return response()->json($result);
        } catch (Throwable $e) {
            LogHelper::logError(null, [
                'olt_id' => $oltId,
                'error' => $e->getMessage(),
            ], 500, '[FAILED] Health Check Single OLT', $traceId);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function clearCache($jobId)
    {
        $traceId = Str::uuid()->toString();
        OltScanJobService::clearScanCache($jobId);

        return response()->json([
            'message' => "Cache cleared for job {$jobId}",
        ]);
    }
}
