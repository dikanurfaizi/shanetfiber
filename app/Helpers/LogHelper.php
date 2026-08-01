<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;

class LogHelper
{
    protected static array $sensitiveFields = [
        'password',
        'password_confirmation',
        'new_password',
        'token',
        'authorization',
        'secret',
        'api_key',
    ];

    public static function logInfo(
        mixed $uri,
        mixed $body,
        ?int $statusCode,
        ?string $description,
        ?string $traceId = null,
        ?int $resTime = null
    ): void {
        Log::info(self::formatLog('INFO', $uri, $body, $statusCode, $description, $traceId, $resTime));
    }

    public static function logWarning(
        mixed $uri,
        mixed $body,
        ?int $statusCode,
        ?string $description,
        ?string $traceId = null,
        ?int $resTime = null
    ): void {
        Log::warning(self::formatLog('WARNING', $uri, $body, $statusCode, $description, $traceId, $resTime));
    }

    public static function logError(
        mixed $uri,
        mixed $body,
        ?int $statusCode,
        ?string $description,
        ?string $traceId = null,
        ?int $resTime = null
    ): void {
        // Khusus logError, jika $uri adalah Exception/Throwable, ekstrak pesannya
        if ($uri instanceof Throwable) {
            $description = ($description ? $description . ' | ' : '') . 'Exception: ' . $uri->getMessage();
            $uri = request()?->path() ?? 'null';
        }

        Log::error(self::formatLog('ERROR', $uri, $body, $statusCode, $description, $traceId, $resTime));
    }

    public static function logDebug(
        mixed $uri,
        mixed $body,
        ?string $description,
        ?string $traceId = null,
        ?int $resTime = null
    ): void {
        if (config('app.debug')) {
            Log::debug(self::formatLog('DEBUG', $uri, $body, 200, $description, $traceId, $resTime));
        }
    }

    private static function formatLog(
        string $level,
        mixed $uri,
        mixed $body,
        ?int $statusCode,
        ?string $description,
        ?string $traceId,
        ?int $resTime
    ): string {
        $caller      = self::getCaller();
        $traceId     = $traceId ?? (string) Str::uuid();
        $statusCode  = $statusCode !== null ? (int) $statusCode : null;
        $description = $description ?? 'null';

        if ($resTime === null && defined('LARAVEL_START')) {
            $resTime = (int) round((microtime(true) - LARAVEL_START) * 1000);
        } else {
            $resTime = $resTime ?? 0;
        }

        if (is_string($uri) && (str_starts_with($uri, 'http://') || str_starts_with($uri, 'https://'))) {
            $logUrl = $uri;
        } else {
            $logUrl = request()?->fullUrl() ?? (is_string($uri) ? $uri : 'null');
        }

        if ($body instanceof \Illuminate\Http\Request) {
            $body = $body->all();
        }

        if (is_array($body)) {
            $body = self::maskSensitiveFields($body);
        }

        $logPayload = [
            'timestamp'     => now()->toIso8601String(),
            'level'         => $level,
            'trace_id'      => $traceId,
            'method'        => request()?->method() ?? 'CLI',
            'status_code'   => $statusCode,
            'response_time' => $resTime,
            'caller'        => $caller,
            'client_ip'     => request()?->ip() ?? 'unknown',
            'user'          => Auth::user()?->name ?? 'guest',
            'url'           => $logUrl,
            'action'        => is_string($uri) && !str_starts_with($uri, 'http') ? $uri : 'API_CALL',
            'description'   => $description,
            'payload'       => $body,
        ];

        return json_encode($logPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private static function getCaller(): string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 8);

        foreach ($trace as $frame) {
            if (isset($frame['class']) && !str_contains($frame['class'], 'LogHelper')) {
                $class    = $frame['class'];
                $function = $frame['function'] ?? 'UnknownFunction';
                return "{$class}::{$function}";
            }
        }

        return 'Unknown::UnknownFunction';
    }

    private static function maskSensitiveFields(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array(strtolower((string) $key), self::$sensitiveFields, true)) {
                $data[$key] = '******';
            } elseif (is_array($value)) {
                $data[$key] = self::maskSensitiveFields($value);
            }
        }
        return $data;
    }
}
