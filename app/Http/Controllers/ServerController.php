<?php

namespace App\Http\Controllers;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Http\Requests\ServerStoreRequest;
use App\Http\Requests\ServerTestRadiusRequest;
use App\Http\Requests\ServerUpdateRequest;
use App\Jobs\ExportMikrotik\ExportCommentToRouter;
use App\Jobs\ExportMikrotik\ExportCustomerToRouter;
use App\Models\AutoIsolir;
use App\Models\Customers\Customer;
use App\Models\Mikrotik;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ServerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:server-list|server-create|server-edit|server-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:server-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:server-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:server-delete', ['only' => ['destroy', 'delete']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $paginate = 15;
        $disabled = $request->disabled;

        $mikrotiks = Mikrotik::query()
            ->when($disabled, fn($query) => $query->where('disabled', $disabled))
            ->orderBy('name', 'ASC')
            ->paginate($paginate);

        return view('servers.index', compact('mikrotiks'))
            ->with('i', (request()->input('page', 1) - 1) * $paginate);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('servers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ServerStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $validated['slug'] = str($validated['name'] . '-' . Str::random(4))->slug();
        $validated['port'] = $validated['port'] ?? 8728;
        $validated['coa_port'] = $validated['coa_port'] ?? 3799;

        Mikrotik::create($validated);

        return redirect()->route('servers.index')
            ->with('success', __('server.message.created'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mikrotik $server): View
    {
        return view('servers.edit', compact('server'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ServerUpdateRequest $request, Mikrotik $server): RedirectResponse
    {
        $validated = array_filter($request->validated(), fn($value) => !is_null($value) && $value !== '');

        if (!empty($validated)) {
            $server->update($validated);
        }

        return redirect()->route('servers.index')
            ->with('success', __('server.message.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Mikrotik $server): View
    {
        return view('servers.delete', compact('server'));
    }

    public function destroy(Mikrotik $server): RedirectResponse
    {
        $serverName = $server->name;
        $server->delete();

        return redirect()->route('servers.index')
            ->with('success', __('server.message.deleted', ['server' => $serverName]));
    }

    /**
     * Nonaktifkan Server, Rule AutoIsolir, dan seluruh Pelanggan via Relasi Eloquent.
     */
    public function disable(Mikrotik $server): RedirectResponse
    {
        $server->autoIsolir()->update(['disabled' => 'true']);
        $server->customers()->update(['is_active' => false]);
        $server->update(['disabled' => 'true']);

        return redirect()->back()->with('success', __('server.message.disabled_success'));
    }

    /**
     * Aktifkan kembali Server, Rule AutoIsolir, dan seluruh Pelanggan via Relasi Eloquent.
     */
    public function enable(Mikrotik $server): RedirectResponse
    {
        $server->autoIsolir()->update(['disabled' => 'false']);
        $server->customers()->update(['is_active' => true]);
        $server->update(['disabled' => 'false']);

        return redirect()->back()->with('success', __('server.message.enabled_success'));
    }

    public function preview(): View
    {
        $servers = Mikrotik::all();
        return view('servers.preview', compact('servers'));
    }

    // ==========================================
    // PREVIEW & IMPORT CUSTOMER
    // ==========================================

    public function showDataCustomer(Request $request, Mikrotik $mikrotik)
    {
        $customers = $this->getCustomerDataFromMikrotik($mikrotik);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'total'      => $customers->total(),
                'serverName' => $mikrotik->name,
            ]);
        }

        $servers = Mikrotik::all();

        return view('servers.preview', [
            'servers'          => $servers,
            'selectedMikrotik' => $mikrotik,
            'customers'        => $customers,
        ]);
    }

    private function getCustomerDataFromMikrotik(Mikrotik $mikrotik)
    {
        return Customer::where('mikrotik_id', $mikrotik->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(50);
    }

    public function importCustomer(Mikrotik $mikrotik)
    {
        $traceId = (string) Str::uuid();
        $timer = new ExecutionTimer();
        $message = '[ServerController] Import secret from database';

        try {
            LogHelper::logInfo(null, [
                'mikrotik_id'   => $mikrotik->id,
                'mikrotik_name' => $mikrotik->name
            ], 200, $message . " started", $traceId, $timer->lap());

            dispatch(new ExportCustomerToRouter($mikrotik, $traceId))->onQueue('mikrotik-heavy');

            LogHelper::logInfo(null, [
                'mikrotik_id'   => $mikrotik->id,
                'mikrotik_name' => $mikrotik->name
            ], 200, $message . " finish", $traceId, $timer->lap());

            return response()->json(['message' => __('server.import.success_text')]);
        } catch (\Throwable $e) {
            $exceptionCode = $e->getCode();
            $statusCode = (is_numeric($exceptionCode) && $exceptionCode >= 400 && $exceptionCode <= 599)
                ? (int) $exceptionCode
                : 500;

            LogHelper::logError(null, [
                'mikrotik_id'     => $mikrotik->id,
                'mikrotik_name'   => $mikrotik->name,
                'error_message'   => $e->getMessage(),
                'exception_class' => get_class($e),
                'file'            => $e->getFile(),
                'line'            => $e->getLine(),
            ], $statusCode, $message . " failed", $traceId, $timer->lap());

            return response()->json(['message' => __('server.import.error_text')], $statusCode);
        }
    }

    /**
     * Uji koneksi RADIUS Secret & CoA Port pada pembuatan router baru (tanpa model binding).
     */
    public function testRadiusConnectionGeneral(ServerTestRadiusRequest $request)
    {
        return $this->testRadiusConnection($request, new Mikrotik());
    }

    /**
     * Uji koneksi RADIUS Secret (Database Sync Check) & CoA Port (Disconnect Test).
     */
    public function testRadiusConnection(ServerTestRadiusRequest $request, Mikrotik $server)
    {
        $validated = $request->validated();

        $host = $validated['host'];
        $radiusSecret = $validated['radius_secret'];
        $coaPort = (int) ($validated['coa_port'] ?? $server->coa_port ?? 3799);

        try {
            // 1. Verifikasi Data pada Tabel 'nas' (FreeRADIUS DB)
            $nas = DB::connection('mysql_radius')->table('nas')->where('nasname', $host)->first();

            if (!$nas) {
                return response()->json([
                    'success' => false,
                    'message' => __('server.test_radius.nas_not_found', ['host' => $host])
                ]);
            }

            if ($nas->secret !== $radiusSecret) {
                return response()->json([
                    'success' => false,
                    'message' => __('server.test_radius.secret_mismatch')
                ]);
            }

            // 2. Uji Respons Server/Router pada Port CoA (Disconnect Request Test)
            $cmd = sprintf(
                'echo "User-Name=ping_test" | radclient -t 2 -r 1 %s:%d disconnect %s 2>&1',
                escapeshellarg($host),
                $coaPort,
                escapeshellarg($radiusSecret)
            );

            exec($cmd, $output, $resultCode);
            $response = implode("\n", $output);

            // Jika Router merespons (Disconnect-NAK / Disconnect-ACK)
            $isCoaResponsive = str_contains($response, 'Disconnect-NAK')
                || str_contains($response, 'Disconnect-ACK')
                || str_contains($response, 'Received response to packet');

            if ($isCoaResponsive) {
                return response()->json([
                    'success' => true,
                    'message' => __('server.test_radius.success_msg', ['port' => $coaPort])
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => __('server.test_radius.timeout_msg', [
                    'port' => $coaPort,
                    'detail' => $response ?: __('server.test_radius.no_response')
                ])
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => __('server.test_radius.error_msg', ['error' => $e->getMessage()])
            ], 500);
        }
    }
}
