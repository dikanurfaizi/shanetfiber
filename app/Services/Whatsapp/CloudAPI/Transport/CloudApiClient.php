<?php

namespace App\Services\WhatsApp\CloudAPI\Transport;

use App\Helpers\LogHelper;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CloudApiClient
{
    public function __construct(private array $httpOptions = []) {}

    public function handle(string $url, string $meta_user_access_token, array $payload): array
    {
        $traceId = Str::random(8);

        LogHelper::logInfo(null, [
            'url' => $url,
            'payload' => $payload,
        ], 200, "[CloudApiClient::handle] [WA-HTTP-OUT] Sending request to WhatsApp Cloud API", $traceId);

        try {
            $token = decrypt($meta_user_access_token);
        } catch (\Throwable $e) {
            LogHelper::logInfo(null, [
                'error' => $e->getMessage(),
            ], 500, "[CloudApiClient::handle] [WA-HTTP-ERR] Failed to decrypt access token", $traceId);

            throw $e;
        }

        $response = Http::withOptions($this->httpOptions)
            ->withToken($token)
            ->post($url, $payload);

        $result = [
            'ok'     => $response->successful(),
            'status' => $response->status(),
            'json'   => $response->json(),
            'raw'    => $response->body(),
        ];

        if ($response->successful()) {
            LogHelper::logInfo(null, [
                'status' => $response->status(),
                'response' => $result['json'] ?? $result['raw'],
            ], $response->status(), "[CloudApiClient::handle] [status:{$response->status()}] [WA-HTTP-IN] Request successful", $traceId);
        } else {
            // Menggunakan $response->status() asli (misal: 403) agar log konsisten
            LogHelper::logInfo(null, [
                'status' => $response->status(),
                'response' => $result['json'] ?? $result['raw'],
            ], $response->status(), "[CloudApiClient::handle] [status:{$response->status()}] [WA-HTTP-ERR] Request failed", $traceId);
        }

        return $result;
    }
}
