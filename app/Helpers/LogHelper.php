<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class LogHelper
{
    public static function logInfo($uri, $body, $statusCode, $description)
    {
        Log::info(self::formatLog($uri, $body, $statusCode, $description));
    }

    public static function logWarning($uri, $body, $statusCode, $description)
    {
        Log::warning(self::formatLog($uri, $body, $statusCode, $description));
    }

    public static function logError($uri, $body, $statusCode, $description)
    {
        Log::error(self::formatLog($uri, $body, $statusCode, $description));
    }

    private static function formatLog($uri, $body, $statusCode, $description)
    {
        $caller = self::getCaller();

        $uri = $uri ?? 'null';
        $body = $body !== null ? json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : 'null';
        $statusCode = $statusCode ?? 'null';
        $description = $description ?? 'null';

        return "$caller | $uri | $body | $statusCode | $description";
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
