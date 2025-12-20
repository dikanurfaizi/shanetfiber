<?php

namespace App\Http\Controllers;

use App\Models\Customers\Customer;
use App\Models\Mikrotik;
use Illuminate\Http\Request;
use App\Http\Resources\Pppoe\SecretResource;
use App\Http\Resources\Pppoe\SecretActiveResource;
use App\Models\AutoIsolir;
use Illuminate\Support\Str;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\TestConnectionService;
use Jenssegers\Agent\Agent;

class IsolirController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    private SecretService $secretService;
    private TestConnectionService $testConnectionService;
    public function __construct(SecretService $secretService, TestConnectionService $testConnectionService)
    {
        $this->secretService = $secretService;
        $this->testConnectionService = $testConnectionService;
    }
    public function index(Request $request)
    {
        $traceId = (string) Str::uuid();
        $clientIP = $_SERVER['REMOTE_ADDR'];
        //return dd($clientIP);
        //  $clientIP = '10.1.12.235';
        $routers = Mikrotik::all();

        foreach ($routers as $router) {
            try {
                $getSecretActive = $this->secretService->getPppSecretActiveByIp($router, $clientIP, $traceId);
                $secretActive = SecretActiveResource::collection($getSecretActive)->first();
                $getProfileSecret = $this->secretService->getSecretByName($router, $secretActive['name'], $traceId);
                $secret = SecretResource::collection($getProfileSecret)->first();
                $profileIsolir = AutoIsolir::where('mikrotik_id', $router->id)->value('profile_id');
                if ($getSecretActive && $secret['profile'] == $profileIsolir) {
                    $username = $secretActive['name'];
                    $customer = Customer::where('username', $username)->where('mikrotik_id', $router->id)->first();
                    $billings = $customer->billing->where('status', '!=', 'LS');
                    if (count($billings) > 0) {
                        $agent = new Agent();
                        $result = $agent->isMobile();
                        if ($result) {
                            return view('mobile.isolirs.isolir_customer', compact('customer', 'billings'));
                        } else {
                            return view('isolir.isolir_customer', compact('customer', 'billings'));
                        }
                    }
                }
            } catch (\Exception $e) {
            }
        }
        return redirect()->route('customer.dashboard');
    }
}
