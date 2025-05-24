<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class LogHelper
{
    public static function logInfo($uri, $body, $statusCode, $description, $traceId = null)
    {
        Log::info(self::formatLog($uri, $body, $statusCode, $description, $traceId));
    }

    public static function logWarning($uri, $body, $statusCode, $description, $traceId = null)
    {
        Log::warning(self::formatLog($uri, $body, $statusCode, $description, $traceId));
    }

    public static function logError($uri, $body, $statusCode, $description, $traceId = null)
    {
        Log::error(self::formatLog($uri, $body, $statusCode, $description, $traceId));
    }

    private static function formatLog($uri, $body, $statusCode, $description, $traceId)
    {
        $caller = self::getCaller();

        $uri = $uri ?? 'null';
        $body = $body !== null ? json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : 'null';
        $statusCode = $statusCode ?? 'null';
        $description = $description ?? 'null';
        $traceId = $traceId ?? 'no-trace-id';

        return "$caller | $traceId | $uri | $body | $statusCode | $description";
    }

    private static function getCaller()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);

        foreach ($trace as $frame) {
            if (isset($frame['class']) && !str_contains($frame['class'], 'LogHelper')) {
                $class = $frame['class'];
                $function = $frame['function'] ?? 'UnknownFunction';
                return "$class::$function";
            }
        }

        return 'Unknown::UnknownFunction';
    }
}
