<?php

namespace App\Http\Controllers\Mikrotik;

use App\Models\Mikrotik;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Routerboard\SystemService;
use App\Services\Routerboard\TestConnectionService;

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
        try {
            $interfaces = $this->systemService->mikrotikInterface($mikrotik);
            return view('mikrotiks.show', compact('mikrotik', 'interfaces'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['msg' => 'Router offline, please check your router.']);
        }
    }
}
