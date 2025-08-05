<?php

namespace App\Http\Controllers\Mikrotik;

use App\Models\Mikrotik;
use App\Http\Controllers\Controller;
use App\Services\Routerboard\SystemService;
use App\Services\Routerboard\TestConnectionService;
use App\Helpers\LogHelper;
use Illuminate\Support\Str;

class MikrotikController extends Controller
{
    private SystemService $systemService;
    private TestConnectionService $testConnectionService;

    public function __construct(SystemService $systemService, TestConnectionService $testConnectionService)
    {
        $this->systemService = $systemService;
        $this->testConnectionService = $testConnectionService;
    }

    public function show(Mikrotik $mikrotik)
    {
        $traceId = (string) Str::uuid();
        try {
            $interfaces = $this->systemService->mikrotikInterface($mikrotik, $traceId);
            LogHelper::logInfo(null, [
                'mikrotik' => $mikrotik->toArray(),
                'interface' => $interfaces
            ], 200, 'Successfully to get mikrotik interface', $traceId);
            return view('mikrotiks.show', compact('mikrotik', 'interfaces'));
        } catch (\Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 503, 'Failed to get mikrotik interface', $traceId);
            return redirect()->back()->withErrors(['msg' => 'Router offline, please check your router.']);
        }
    }
}
