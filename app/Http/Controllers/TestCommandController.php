<?php

namespace App\Http\Controllers;

use RouterOS\Query;
use RouterOS\Client;
use RouterOS\Config;

use Illuminate\Http\Request;
use App\Services\Routerboard\TestConnectionService;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\QueueService;
use App\Services\Routerboard\FirewallService;
use App\Services\Routerboard\SystemService;
use App\Services\Routerboard\ProxyService;

class TestCommandController extends Controller
{
    public function __construct() {}

    public function index(Request $request)
    {
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
