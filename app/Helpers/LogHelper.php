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
        // Khusus logError, jika payload $uri menangkap objek Exception asli, kita ekstrak messagenya
        if ($uri instanceof \Exception) {
            $description = $description . ' | Exception: ' . $uri->getMessage();
            $uri = request()?->path() ?? 'null';
        }

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
        $statusCode  = $statusCode !== null ? (int) $statusCode : null;
        $description = $description ?? 'null';
        $resTime     = $resTime !== null ? (int) $resTime : 0;

        // Convert request object ke array
        if ($body instanceof \Illuminate\Http\Request) {
            $body = $body->all();
        }

        // Jalankan masking data jika payload bertipe array
        if (is_array($body)) {
            $body = self::maskSensitiveFields($body);
        }

        // 🔥 STRATEGI JSON: Menyusun data log ke dalam struktur associative array baku
        $logPayload = [
            'timestamp'    => now()->toIso8601String(), // Standar waktu ISO-8601 (Sangat disukai Elasticsearch)
            'level'        => $level,
            'trace_id'     => $traceId,
            'method'       => request()?->method() ?? 'CLI',
            'status_code'  => $statusCode,
            'response_time' => $resTime,
            'caller'       => $caller,
            'client_ip'    => request()?->ip() ?? 'unknown',
            'user'         => Auth::user()?->name ?? 'guest',
            'url'          => request()?->fullUrl() ?? $uri,
            'description'  => $description,
            'payload'      => $body // Menyimpan data objek/array murni tanpa double json_encode string
        ];

        // Encode seluruh wrapper menjadi single-line JSON string bersih
        return json_encode($logPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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
