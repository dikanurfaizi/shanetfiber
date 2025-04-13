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
use Exception;
use App\Helpers\LogHelper;

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
        try {
            $spec = $this->systemService->getAllResources($mikrotik);
            $resources = RouterboardResource::collection($spec);
            $logBody = [
                'mikrotik_id' => $mikrotik->id,
                'spec' => $spec,
                'resources' => $resources
            ];
            // LogHelper::logInfo(null, $logBody, 200, 'Successfully to get mikrotik interface');
            return response()->json([
                'resources' => $resources,
            ]);
        } catch (Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 503, 'Failed to retrieve Mikrotik resources');

            return response()->json([
                'error' => 'Failed to retrieve Mikrotik resources',
                'message' => $e->getMessage(),
            ]);
        }
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
        try {
            $response = $this->systemService->monitoringTraffic($mikrotik);
            // LogHelper::logInfo(null, ['mikrotik' => $mikrotik->id, 'response' => $response], 200, 'Successfully to retrieve mikrotik traffic interface');
            return $response;
        } catch (Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 503, 'Failed to retrieve mikrotik traffic interface');
            return response()->json([
                'error' => 'Failed to retrieve Mikrotik traffic interface',
                'message' => $e->getMessage(),
            ]);
        }
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
        try {
            $tm = $this->systemService->getTime($mikrotik);
            $time = RouterboardTimeResource::collection($tm);

            $logBody = [
                'mikrotik_id' => $mikrotik->id,
                'time' => $time,
            ];

            // LogHelper::logInfo(null, $logBody, 200, 'Successfully retrieved Mikrotik time');

            return response()->json([
                'time' => $time,
            ]);
        } catch (Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 503, 'Failed to retrieve Mikrotik time');

            return response()->json([
                'error' => 'Failed to retrieve Mikrotik time',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
