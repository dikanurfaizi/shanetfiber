<?php

namespace App\Services\Whatsapp\CloudAPI\Templates;

use App\Helpers\LogHelper;
use App\Helpers\WhatsappGraph;
use App\Models\Whatsapp\WhatsappGatewayAccount;
use Illuminate\Support\Facades\Http;

class GetTemplateByIdService
{
    private array $httpOptions;

    public function __construct()
    {
        $this->httpOptions = [
            'timeout' => config('services.whatsapp.timeout', 10),
            'verify' => config('services.whatsapp.ssl_verify', ! app()->environment('local')),
        ];
    }

    /**
     * Fetch a WhatsApp message template by its ID.
     */
    public function fetch(WhatsappGatewayAccount $account, string $templateId, string $traceId): ?array
    {
        $url = WhatsappGraph::templateById(
            graphUrl: $account->meta_graph_url,
            graphVersion: $account->meta_graph_version,
            templateId: $templateId
        );

        $response = Http::withOptions($this->httpOptions)
            ->withToken($account->meta_user_access_token)
            ->get($url);

        if (!$response->successful()) {
            LogHelper::logWarning(null, [
                'account_id' => $account->id,
                'status' => $response->status(),
                'response' => $response->body(),
            ], 400, 'WhatsApp Cloud API: Failed to fetch template by ID.', $traceId);

            return null;
        }

        LogHelper::logInfo(null, [
            'account_id' => $account->id,
            'status' => $response->status(),
        ], 200, 'WhatsApp Cloud API: Fetched template by ID successfully.', $traceId);

        return $response->json();
    }
}
