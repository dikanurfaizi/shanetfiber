<?php

namespace App\Http\Controllers;

use App\Models\Mikrotik;
use Illuminate\Http\Request;
use RouterOS\Exceptions\QueryException;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Exceptions\ConnectException;
use RouterOS\Exceptions\BadCredentialsException;
use App\Models\Packages\PppProfile;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\Bus;
use App\Jobs\ExportMikrotik\CreatePppProfileToRouter;
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

    public function exportToRouter(Request $request)
    {
        $startTime = microtime(true);
        $traceId = (string) Str::uuid();
        $uri = request()->path();

        $stepStart = microtime(true);
        LogHelper::logInfo(
            $uri,
            [
                'request_data' => $request->only(['source_server', 'destination_server']),
                'elapsed_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'step_ms' => round((microtime(true) - $stepStart) * 1000, 2),
            ],
            200,
            '[ExportToRouter] Request received',
            $traceId,
            round((microtime(true) - $stepStart) * 1000, 2)
        );

        // Validate input
        $this->validate($request, [
            'source_server' => ['required'],
            'destination_server' => ['required'],
        ]);

        $sourceMikrotik = Mikrotik::findOrFail($request->source_server);
        $targetMikrotik = Mikrotik::findOrFail($request->destination_server);

        $stepStart = microtime(true);
        LogHelper::logInfo(
            $uri,
            [
                'source_router' => $sourceMikrotik->only(['id', 'name', 'host']),
                'target_router' => $targetMikrotik->only(['id', 'name', 'host']),
                'elapsed_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'step_ms' => round((microtime(true) - $stepStart) * 1000, 2),
            ],
            200,
            '[ExportToRouter] Routers loaded',
            $traceId,
            round((microtime(true) - $stepStart) * 1000, 2)
        );

        $profilesToExport = PppProfile::where('disabled', 'false')->get();
        $totalProfiles = $profilesToExport->count();

        $stepStart = microtime(true);
        LogHelper::logInfo(
            $uri,
            [
                'total_profiles_to_export' => $totalProfiles,
                'elapsed_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'step_ms' => round((microtime(true) - $stepStart) * 1000, 2),
            ],
            200,
            '[ExportToRouter] Profiles collected',
            $traceId,
            round((microtime(true) - $stepStart) * 1000, 2)
        );

        if ($totalProfiles === 0) {
            $stepStart = microtime(true);
            LogHelper::logWarning(
                $uri,
                [
                    'elapsed_ms' => round((microtime(true) - $startTime) * 1000, 2),
                    'step_ms' => round((microtime(true) - $stepStart) * 1000, 2),
                ],
                200,
                '[ExportToRouter] No active PPP profiles to export',
                $traceId,
                round((microtime(true) - $stepStart) * 1000, 2)
            );
            return back()->withErrors('Tidak ada PPP Profile yang bisa di-export.');
        }

        // Build Profile Jobs
        $profileJobs = [];
        foreach ($profilesToExport as $profile) {
            $profileJobs[] = new CreatePppProfileToRouter(
                (int) $targetMikrotik->id,
                (int) $profile->id,
                $traceId
            );
        }

        $stepStart = microtime(true);
        LogHelper::logInfo(
            $uri,
            [
                'job_count' => count($profileJobs) + 1, // include migration job
                'elapsed_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'step_ms' => round((microtime(true) - $stepStart) * 1000, 2),
            ],
            200,
            '[ExportToRouter] Queue jobs prepared',
            $traceId,
            round((microtime(true) - $stepStart) * 1000, 2)
        );

        try {
            Bus::chain(array_merge(
                $profileJobs,
                [
                    new MigrateCustomerToRouter(
                        (int) $sourceMikrotik->id,
                        (int) $targetMikrotik->id,
                        $traceId
                    )
                ]
            ))->dispatch();

            LogHelper::logInfo(
                $uri,
                [
                    'job_count' => count($profileJobs) + 1,
                ],
                200,
                '[ExportToRouter] Dispatch SUCCESS',
                $traceId,
                round((microtime(true) - $startTime) * 1000, 2)
            );
        } catch (\Throwable $e) {
            LogHelper::logError(
                $uri,
                [
                    'error' => ['code' => $e->getCOde(), 'message' => $e->getMessage()],
                    'trace' => $e->getTraceAsString(),
                ],
                500,
                '[ExportToRouter] Dispatch FAILED',
                $traceId,
                round((microtime(true) - $startTime) * 1000, 2)
            );

            return back()->withErrors('Terjadi kesalahan saat mengirim data ke router.');
        }

        return back()->with('success', "Export profile dan migrasi customer sedang diproses untuk router {$targetMikrotik->name}.");
    }
}
