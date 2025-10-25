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
use App\Jobs\CreatePppProfileToRouter;
use App\Jobs\MigrateCustomerToRouter;
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
        $traceId = (string) Str::uuid();
        $uri = request()->path();
        $this->validate($request, [
            'source_server' => ['required'],
            'destination_server' => ['required'],
        ]);

        $sourceMikrotik = Mikrotik::findOrFail($request->source_server);
        $targetMikrotik = Mikrotik::findOrFail($request->destination_server);

        $profilesToExport = PppProfile::where('disabled', 'false')->get();

        $profileJobs = $profilesToExport->map(function ($profile) use ($targetMikrotik) {
            return new CreatePppProfileToRouter($targetMikrotik, $profile);
        })->toArray();

        // Chain: export profile ke target, lalu migrasi customer dari source ke target
        try {
            Bus::chain(array_merge(
                $profileJobs,
                [new MigrateCustomerToRouter($sourceMikrotik, $targetMikrotik, $traceId)]
            ))->dispatch();
        } catch (\Throwable $e) {
            LogHelper::logWarning(
                $uri,
                [
                    'message' => $e->getMessage(),
                ],
                400,
                'Export to router failed'
            );
            return redirect()->back()->withErrors('Terjadi kesalahan saat mengirim data ke router.');
        }

        return redirect()->back()->with('success', "Export profile dan migrasi customer sedang diproses untuk router {$targetMikrotik->name}.");
    }
}
