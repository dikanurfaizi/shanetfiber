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
        ?int $statusCode = null,
        ?string $description = null,
        ?string $traceId = null,
        ?int $resTime = null
    ): void {
        Log::info(self::formatLog($uri, $body, $statusCode, $description, $traceId, $resTime));
    }

    public static function logWarning(
        mixed $uri,
        mixed $body,
        ?int $statusCode = null,
        ?string $description = null,
        ?string $traceId = null,
        ?int $resTime = null
    ): void {
        Log::warning(self::formatLog($uri, $body, $statusCode, $description, $traceId, $resTime));
    }

    public static function logError(
        mixed $uri,
        mixed $body,
        ?int $statusCode = null,
        ?string $description = null,
        ?string $traceId = null,
        ?int $resTime = null
    ): void {
        if ($uri instanceof Throwable) {
            $description = ($description ? $description . ' | ' : '') . 'Exception: ' . $uri->getMessage();
            $uri = request()?->path() ?? 'null';
        }

        Log::error(self::formatLog($uri, $body, $statusCode, $description, $traceId, $resTime));
    }

    public static function logDebug(
        mixed $uri,
        mixed $body,
        ?int $statusCode = null,
        ?string $description = null,
        ?string $traceId = null,
        ?int $resTime = null
    ): void {
        if (config('app.debug')) {
            Log::debug(self::formatLog($uri, $body, $statusCode, $description, $traceId, $resTime));
        }
    }

    private static function formatLog(
        mixed $uri,
        mixed $body,
        ?int $statusCode,
        ?string $description,
        ?string $traceId,
        ?int $resTime
    ): string {
        $caller  = self::getCaller();
        $traceId = Str::substr($traceId ?? (string) Str::uuid(), 0, 8); // Cukup 8 karakter agar tidak memenuhi layar

        if ($body instanceof \Illuminate\Http\Request) {
            $body = $body->all();
        }

        if (is_array($body)) {
            $body = self::maskSensitiveFields($body);
        }

        // Susun JSON yang hanya berisi field bermakna
        $logPayload = array_filter([
            'trace_id' => $traceId,
            'caller'   => $caller,
            'desc'     => $description,
            'status'   => $statusCode,
            'ms'       => ($resTime && $resTime > 0) ? $resTime : null, // Hanya muncul jika ada durasi
            'user'     => Auth::user()?->name ?? 'guest',
            'ip'       => (request()?->ip() && request()->ip() !== '127.0.0.1') ? request()->ip() : null, // Sembunyikan 127.0.0.1
            'url'      => (is_string($uri) && str_starts_with($uri, 'http') && !str_contains($uri, 'localhost')) ? $uri : null, // Sembunyikan localhost
            'payload'  => !empty($body) ? $body : null,
        ], fn($value) => $value !== null);

        return json_encode($logPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private static function getCaller(): string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 8);

        foreach ($trace as $frame) {
            if (isset($frame['class']) && !str_contains($frame['class'], 'LogHelper')) {
                // Ambil ClassName pendek (misal: MikrotikService) tanpa namespace panjang
                $class    = class_basename($frame['class']);
                $function = $frame['function'] ?? 'Unknown';
                return "{$class}::{$function}";
            }
        }

        return 'Unknown';
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
