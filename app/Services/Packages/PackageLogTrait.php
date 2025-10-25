<?php

namespace App\Services\Packages;

use App\Helpers\LogHelper;
use Illuminate\Support\Str;

trait PackageLogTrait
{
    protected function startTrace()
    {
        return [(string) Str::uuid(), microtime(true)];
    }

    protected function endTraceSuccess($request, $traceId, $start, $data, $message)
    {
        $duration = round((microtime(true) - $start) * 1000, 2);
        LogHelper::logInfo($request, array_merge($data, [
            'execution_time_ms' => $duration
        ]), 200, $message, $traceId, $duration);
    }

    protected function endTraceError($request, $traceId, $start, $e, $message)
    {
        $duration = round((microtime(true) - $start) * 1000, 2);
        LogHelper::logError($request, [
            'error' => [
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ],
            'execution_time_ms' => $duration
        ], 500, $message, $traceId, $duration);
    }
}
