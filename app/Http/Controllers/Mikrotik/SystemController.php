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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

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
        $cacheKey = "router:resources:{$mikrotik->id}";

        return response()->json([
            'resources' => Cache::get($cacheKey, []),
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
        $traceId = (string) Str::uuid();

        try {
            LogHelper::logInfo(null, [
                'mikrotik_id' => $mikrotik->id,
                'mikrotik_name' => $mikrotik->name
            ], 200, 'Starting to retrieve Mikrotik traffic interface', $traceId);

            $response = $this->systemService->monitoringTraffic($mikrotik, $traceId);

            LogHelper::logInfo(null, [
                'mikrotik_id' => $mikrotik->id,
                'mikrotik_name' => $mikrotik->name,
                'response_type' => gettype($response),
                'response_size' => is_array($response) ? count($response) : 'N/A'
            ], 200, 'Successfully retrieved Mikrotik traffic interface', $traceId);

            return $response;
        } catch (Exception $e) {
            LogHelper::logError(null, [
                'mikrotik_id' => $mikrotik->id,
                'mikrotik_name' => $mikrotik->name,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ], 503, 'Failed to retrieve Mikrotik traffic interface', $traceId);

            return response()->json([
                'error' => 'Failed to retrieve Mikrotik traffic interface',
                'message' => $e->getMessage(),
            ], 503);
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
        $traceId = (string) Str::uuid();

        try {
            LogHelper::logInfo(null, [
                'mikrotik_id' => $mikrotik->id,
                'mikrotik_name' => $mikrotik->name
            ], 200, 'Starting to retrieve Mikrotik time', $traceId);

            $tm = $this->systemService->getTime($mikrotik, $traceId);
            $time = RouterboardTimeResource::collection($tm);

            LogHelper::logInfo(null, [
                'mikrotik_id' => $mikrotik->id,
                'mikrotik_name' => $mikrotik->name,
                'time_data' => $time,
                'time_count' => count($time)
            ], 200, 'Successfully retrieved Mikrotik time', $traceId);

            return response()->json([
                'time' => $time,
            ]);
        } catch (Exception $e) {
            LogHelper::logError(null, [
                'mikrotik_id' => $mikrotik->id,
                'mikrotik_name' => $mikrotik->name,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ], 503, 'Failed to retrieve Mikrotik time', $traceId);

            return response()->json([
                'error' => 'Failed to retrieve Mikrotik time',
                'message' => $e->getMessage(),
            ], 503);
        }
    }

    public function updateCache(Mikrotik $mikrotik)
    {
        $traceId = (string) Str::uuid();
        $cacheKey = "router:resources:{$mikrotik->id}";

        try {
            $spec = $this->systemService->getAllResources($mikrotik, $traceId);
            $resources = RouterboardResource::collection($spec);

            // simpan cache (misal 1 menit aja)
            Cache::put($cacheKey, $resources, now()->addMinutes(1));

            return response()->json([
                'status' => 'success',
                'message' => 'Cache updated successfully',
                'resources' => $resources,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'reources' => 0
            ], 200);
        }
    }
}
