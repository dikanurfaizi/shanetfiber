<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LogHelper
{
    protected static array $sensitiveFields = [
        'password',
        'password_confirmation',
        'new_password',
        'token',
        'authorization',
    ];

    public static function logInfo($uri, $body, $statusCode, $description, $traceId = null, $resTime = null)
    {
        Log::info(self::formatLog('INFO', $uri, $body, $statusCode, $description, $traceId, $resTime));
    }

    public static function logWarning($uri, $body, $statusCode, $description, $traceId = null, $resTime = null)
    {
        Log::warning(self::formatLog('WARNING', $uri, $body, $statusCode, $description, $traceId, $resTime));
    }

    public static function logError($uri, $body, $statusCode, $description, $traceId = null, $resTime = null)
    {
        Log::error(self::formatLog('ERROR', $uri, $body, $statusCode, $description, $traceId, $resTime));
    }

    public static function logDebug($uri, $body, $description, $traceId = null, $resTime = null)
    {
        if (config('app.debug')) {
            Log::debug(self::formatLog('DEBUG', $uri, $body, 200, $description, $traceId, $resTime));
        }
    }

    private static function formatLog($level, $uri, $body, $statusCode, $description, $traceId, $resTime)
    {
        $caller      = self::getCaller();
        $traceId     = $traceId ?? (string) Str::uuid();
        $uri         = $uri ?? request()?->path() ?? 'null';
        $statusCode  = $statusCode ?? 'null';
        $description = $description ?? 'null';
        $resTime     = $resTime ?? 0;

        // Convert request object ke array
        if ($body instanceof \Illuminate\Http\Request) {
            $body = $body->all();
        }

        // Mask sensitive fields
        if (is_array($body)) {
            $body = self::maskSensitiveFields($body);
            $body = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } elseif (!is_string($body)) {
            $body = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        // Tambahan kontekstual
        $userName = Auth::user()?->name ?? 'guest';
        $ip       = request()?->ip() ?? 'unknown';
        $method   = request()?->method() ?? 'CLI';
        $url      = request()?->fullUrl() ?? $uri;

        return "[$level] | $traceId | $method | $statusCode | $resTime | $caller | $ip | $userName | $url | $description | $body";
    }

    private static function getCaller()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 8);

        foreach ($trace as $frame) {
            if (isset($frame['class']) && !str_contains($frame['class'], 'LogHelper')) {
                $class    = $frame['class'];
                $function = $frame['function'] ?? 'UnknownFunction';
                return "$class::$function";
            }
        }

        return 'Unknown::UnknownFunction';
    }

    private static function maskSensitiveFields(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), self::$sensitiveFields)) {
                $data[$key] = '******';
            } elseif (is_array($value)) {
                $data[$key] = self::maskSensitiveFields($value); // rekursif
            }
        }
        return $data;
    }
}
