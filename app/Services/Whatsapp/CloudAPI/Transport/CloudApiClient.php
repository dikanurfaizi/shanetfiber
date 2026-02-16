<?php

namespace App\Services\Whatsapp\CloudAPI\Transport;

use Illuminate\Support\Facades\Http;

class CloudApiClient
{
    public function __construct(private array $httpOptions = []) {}

    public function handle(string $url, string $meta_user_access_token, array $payload): array
    {
        $response = Http::withOptions($this->httpOptions)
            ->withToken(decrypt($meta_user_access_token))
            ->post($url, $payload);

        return [
            'status' => $response->status(),
            'body' => $response->json(),
            'raw' => $response->body(),
        ];
    }
}
