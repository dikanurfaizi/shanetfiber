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
        'secret',
        'api_key', // Tambahan field sensitif yang sering ada di API pihak ketiga
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
        $statusCode  = $statusCode !== null ? (int) $statusCode : null;
        $description = $description ?? 'null';
        $resTime     = $resTime !== null ? (int) $resTime : 0;

        // 1. Tentukan URL secara dinamis (Internal Request vs Third-Party API)
        if (is_string($uri) && (str_starts_with($uri, 'http://') || str_starts_with($uri, 'https://'))) {
            // Jika $uri adalah URL penuh (pihak ketiga), pakai langsung
            $logUrl = $uri;
        } else {
            // Jika $uri berupa action name / null, gunakan fullUrl request internal saat ini sebagai fallback
            $logUrl = request()?->fullUrl() ?? ($uri ?? 'null');
        }

        // Convert request object ke array
        if ($body instanceof \Illuminate\Http\Request) {
            $body = $body->all();
        }

        // Jalankan masking data jika payload bertipe array
        if (is_array($body)) {
            $body = self::maskSensitiveFields($body);
        }

        // STRATEGI JSON: Menyusun data log ke dalam struktur associative array baku
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
            'url'           => $logUrl, // Menggunakan variabel dinamis hasil filter di atas
            'action'        => is_string($uri) && !str_starts_with($uri, 'http') ? $uri : 'API_CALL', // Tambahan info penanda aksi internal
            'description'   => $description,
            'payload'       => $body
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
