<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;

class LogHelper
{
    protected static array $sensitiveFields = [
        'password',
        'password_confirmation',
        'new_password',
        'token'
    ];

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
        $traceId = $traceId ?? 'no-trace-id';
        $statusCode = $statusCode ?? 'null';
        $description = $description ?? 'null';

        // Convert request object to array if needed
        if ($body instanceof \Illuminate\Http\Request) {
            $body = $body->all();
        }

        // Mask sensitive fields
        if (is_array($body)) {
            $body = self::maskSensitiveFields($body);
        }

        $body = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // Tambahan kontekstual
        $userName = Auth::user()?->name ?? 'guest';
        $ip = request()?->ip() ?? 'unknown';

        return "$caller | $traceId | $ip | $userName | $uri | $body | $statusCode | $description";
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

    private static function maskSensitiveFields(array $data): array
    {
        foreach (self::$sensitiveFields as $field) {
            if (Arr::has($data, $field)) {
                $data[$field] = '******';
            }
        }
        return $data;
    }
}
