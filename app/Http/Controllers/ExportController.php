<?php

namespace App\Http\Controllers;

use App\Helpers\ExecutionTimer;
use App\Models\Mikrotik;
use Illuminate\Http\Request;
use RouterOS\Exceptions\QueryException;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Exceptions\ConnectException;
use RouterOS\Exceptions\BadCredentialsException;
use App\Models\Packages\PppProfile;
use App\Helpers\LogHelper;
use App\Http\Requests\ExportToRouterRequest;
use App\Http\Requests\SyncCommentRequest;
use Illuminate\Support\Facades\Bus;
use App\Jobs\ExportMikrotik\CreatePppProfileToRouter;
use App\Jobs\ExportMikrotik\ExportCommentToRouter;
use App\Jobs\ExportMikrotik\MigrateCustomerToRouter;
use Illuminate\Support\Str;

class ExportController extends Controller
{
    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws QueryException
     * @throws BadCredentialsException
     * @throws ConfigException
     */
    public function __construct()
    {
        $this->middleware('permission:secret-create|secret-disable|permission:secret-edit|permission:secret-delete', ['only' => ['index', 'exportToRouter']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $mikrotiks = Mikrotik::where('disabled', 'false')->orderBy('name', 'ASC')->pluck('name', 'id');
        return view('exports.index', compact('mikrotiks'));
    }

    public function exportToRouter(ExportToRouterRequest $request)
    {
        $timer = new ExecutionTimer();
        $traceId = (string) Str::uuid();
        $uri = $request->path();

        // 1. Ambil data server (karena sudah lolos validasi Form Request)
        $sourceMikrotik = Mikrotik::findOrFail($request->source_server);
        $targetMikrotik = Mikrotik::findOrFail($request->destination_server);

        LogHelper::logInfo($uri, [
            'request_data' => $request->only(['source_server', 'destination_server']),
            'source_router' => $sourceMikrotik->only(['id', 'name', 'host']),
            'target_router' => $targetMikrotik->only(['id', 'name', 'host']),
        ], 200, '[ExportToRouter] Request received and routers loaded', $traceId, $timer->lap());

        // $profilesToExport = PppProfile::where('disabled', 'false')->get();
        // $totalProfiles = $profilesToExport->count();

        // LogHelper::logInfo(
        //     $uri,
        //     [
        //         'total_profiles_to_export' => $totalProfiles,
        //     ],
        //     200,
        //     '[ExportToRouter] Profiles collected',
        //     $traceId,
        //     $timer->lap()
        // );

        // if ($totalProfiles === 0) {
        //     LogHelper::logWarning(
        //         $uri,
        //         [],
        //         200,
        //         '[ExportToRouter] No active PPP profiles to export',
        //         $traceId,
        //         $timer->lap()
        //     );
        //     return back()->withErrors('Tidak ada PPP Profile yang bisa di-export.');
        // }

        // // Build Profile Jobs
        // $profileJobs = [];
        // foreach ($profilesToExport as $profile) {
        //     $profileJobs[] = new CreatePppProfileToRouter(
        //         $targetMikrotik,
        //         $profile,
        //         $traceId
        //     );
        // }

        // LogHelper::logInfo(
        //     $uri,
        //     [
        //         'job_count' => count($profileJobs) + 1, // include migration job
        //     ],
        //     200,
        //     '[ExportToRouter] Queue jobs prepared',
        //     $traceId,
        //     $timer->lap()
        // );

        $profileJobs = [];

        // 2. Eksekusi Antrean Job
        try {
            Bus::chain(array_merge(
                $profileJobs,
                [new MigrateCustomerToRouter($sourceMikrotik, $targetMikrotik, $traceId)]
            ))->dispatch();

            LogHelper::logInfo($uri, ['job_count' => count($profileJobs) + 1], 200, '[ExportToRouter] Dispatch SUCCESS', $traceId, $timer->lap());

            return back()->with('success', "Migrasi customer sedang diproses untuk router {$targetMikrotik->name}.");
        } catch (\Throwable $e) {
            LogHelper::logError($uri, [
                'error' => ['code' => $e->getCode(), 'message' => $e->getMessage()],
                'trace' => $e->getTraceAsString(),
            ], 500, '[ExportToRouter] Dispatch FAILED', $traceId, $timer->lap());

            return back()->withErrors('Terjadi kesalahan saat mengirim data ke router.');
        }
    }

    public function syncComment(SyncCommentRequest $request)
    {
        $traceId = (string) Str::uuid();

        ExportCommentToRouter::dispatch($request->mikrotik_id, $traceId);

        return back()->with('success', 'Sinkronisasi comment router sedang diproses di background queue.');
    }
}
