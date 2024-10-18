<?php

namespace App\Http\Controllers;

use RouterOS\Query;
use RouterOS\Client;
use RouterOS\Config;

use App\Models\Midtran;
use App\Models\Customer;
use App\Models\Websystem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Billing\Billing;
use App\Models\Mikrotik;
use RouterOS\Exceptions\QueryException;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConfigException;
use Illuminate\Support\Facades\Validator;
use RouterOS\Exceptions\ConnectException;
use RouterOS\Exceptions\BadCredentialsException;
use App\Services\Routerboard\TestConnectionService;
use App\Services\Routerboard\Ppp\SecretService;
use App\Http\Resources\Pppoe\ProfileResource;
use App\Services\Routerboard\QueueService;
use App\Http\Resources\QueueResource;
use App\Services\Routerboard\FirewallService;
use App\Services\Routerboard\SystemService;
use App\Services\Routerboard\ProxyService;

class TestCommandController extends Controller
{
    private TestConnectionService $testConnectionService;
    private SecretService $secretService;
    private QueueService $queueService;
    private SystemService $systemService;
    private FirewallService $firewallService;
    private ProxyService $proxyService;

    public function __construct(SystemService $systemService, ProxyService $proxyService, TestConnectionService $testConnectionService, FirewallService $firewallService, SecretService $secretService, QueueService $queueService)
    {
        $this->testConnectionService = $testConnectionService;
        $this->secretService = $secretService;
        $this->queueService = $queueService;
        $this->firewallService = $firewallService;
        $this->systemService = $systemService;
        $this->proxyService = $proxyService;
    }

    public function index(Request $request)
    {
        /*
        //return dd($request->username);
        $profileName = 'Profile Up to 5 Mbps';
        $routers = Mikrotik::where('disabled', 'false')->get();
        $username = '<pppoe-lilik-kluwih@griyanet>';
        foreach ($routers as $router) {
            $interface = $this->proxyService->getAccessService($router);
            //$profiles = $this->secretService->getPppProfileByName($router, $profileName);
            // $profile = ProfileResource::collection($profiles)->first();
            // $getAllSecretActive = $this->firewallService->getNatService($router);
            // if ($getAllSecretActive == 'error') {
            //    $simpleQueuesCollection = collect([
            //        ['name' => 'No Parent', 'profile' => ''],
            //    ]);
            // return response()->json($simpleQueuesCollection);
            // } else {
            //      $simpleQueuesCollection = QueueResource::collection($getAllSecretActive);
            // }

            //$getAllSecretActive = $this->secretService->getAllPppSecrets($router);
            return dd($interface);
        }
*/
        $config =
            (new Config())
            ->set('host', '10.1.5.1')
            ->set('user', 'customer_management')
            ->set('pass', 'Yoga@2009');

        // Initiate client with config object
        $client = new Client($config);

        // Build query
        $query = new Query('/interface/getall');

        // Send query to RouterOS
        $request = $client->query($query);

        // Read answer from RouterOS
        $response = $client->read();
        return dd($response);
    }
}
