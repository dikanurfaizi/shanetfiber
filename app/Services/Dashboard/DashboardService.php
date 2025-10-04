<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\Cache;
use App\Helpers\UserAuth;
use App\Models\AutoIsolir;
use App\Services\Routerboard\TestConnectionService;

class DashboardService
{
    private TestConnectionService $testConnectionService;

    public function __construct(TestConnectionService $testConnectionService)
    {
        $this->testConnectionService = $testConnectionService;
    }

    private function getUserAuthData()
    {
        return UserAuth::getUserDetails();
    }

    public function refreshRoutersSummary($routersEnable, $traceId, $userId): void
    {
        $cacheKey = "dashboard:routers:summary:user:{$userId}";
        $summary = $this->processRouters($routersEnable, $traceId);

        Cache::put(
            $cacheKey,
            $summary,
            now()->addMinutes(config('dashboard.cache_ttl', 10)) // default 10m
        );
    }

    private function processRouters($routersEnable, $traceId): array
    {
        $auth = $this->getUserAuthData();

        $summary = [
            'onlineRouters' => [],
            'countRouterOnline' => 0,
            'countRouterOffline' => 0,
            'sumCustomersOnline' => 0,
            'sumCustomersOffline' => 0,
            'sumCustomersIsolir' => 0,
        ];

        foreach ($routersEnable as $router) {
            try {
                $response = $this->testConnectionService->checkOnline($router, $traceId);

                if ($response['status']) {
                    $summary['onlineRouters'][] = [
                        'id' => $router->id,
                        'host' => $router->host,
                        'data' => $response['data'],
                    ];
                    $summary['countRouterOnline']++;
                } else {
                    $summary['countRouterOffline']++;
                }
            } catch (\Exception $e) {
                $summary['countRouterOffline']++;
            }
        }

        return $summary;
    }
}
