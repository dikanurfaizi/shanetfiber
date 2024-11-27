<?php

namespace App\Http\Controllers\Mikrotik;

use App\Http\Controllers\Controller;
use App\Http\Resources\RouterboardResource;
use App\Http\Resources\RouterboardTimeResource;
use App\Models\Mikrotik;
use App\Services\Routerboard\SystemService;
use RouterOS\Exceptions\BadCredentialsException;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Exceptions\ConnectException;
use RouterOS\Exceptions\QueryException;

class SystemController extends Controller
{
    private SystemService $systemService;

    public function __construct(SystemService $systemService)
    {
        $this->systemService = $systemService;
    }

    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws BadCredentialsException
     * @throws QueryException
     * @throws ConfigException
     */
    public function routerResource(Mikrotik $mikrotik)
    {
        $spec = $this->systemService->getAllResources($mikrotik);
        $resources = RouterboardResource::collection($spec);
        return response()->json([
            'resources' => $resources,
        ]);
    }

    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws BadCredentialsException
     * @throws QueryException
     * @throws ConfigException
     */
    public function mikrotikTrafficInterface(Mikrotik $mikrotik)
    {
        return $this->systemService->monitoringTraffic($mikrotik);
    }

    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws QueryException
     * @throws BadCredentialsException
     * @throws ConfigException
     */
    public function getTimeRouter(Mikrotik $mikrotik)
    {
        $tm = $this->systemService->getTime($mikrotik);
        $time = RouterboardTimeResource::collection($tm);
        return response()->json([
            'time' => $time,
        ]);
    }
}
