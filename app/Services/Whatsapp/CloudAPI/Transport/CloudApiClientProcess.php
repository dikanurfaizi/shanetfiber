<?php

namespace App\Services\WhatsApp\CloudAPI\Transport;

use App\Helpers\WhatsAppGraph;
use App\Models\WhatsApp\WhatsAppGatewayAccount;
use App\Services\WhatsApp\CloudAPI\Payloads\DTO\TemplateMessageDTO;

class CloudApiClientProcess
{
    public function __construct(protected CloudApiClient $apiClient) {}

    public function send(WhatsAppGatewayAccount $account, TemplateMessageDTO $dto): array
    {
        $payload = $dto->toArray();

        $phoneNumberId = $account->defaultNumber?->meta_phone_number_id;

        if (!$phoneNumberId) {
            return [
                'success' => false,
                'status'  => 400,
                'message' => "WhatsApp default number not configured for account {$account->id}",
                'error'   => null,
                'body'    => null,
                'raw'     => null,
            ];
        }

        $url = WhatsAppGraph::sendMessage(
            graphUrl: $account->meta_graph_url,
            graphVersion: $account->meta_graph_version,
            phoneNumberId: $phoneNumberId
        );

        $response = $this->apiClient->handle($url, $account->meta_user_access_token, $payload);

        $jsonResponse = $response['json'] ?? [];

        if (!$response['ok']) {
            // Ambil pesan error spesifik dari response Meta jika ada
            $apiErrorMessage = $jsonResponse['error']['message'] ?? "Cloud API request failed with status {$response['status']}";

            return [
                'success' => false,
                'status'  => $response['status'] ?? 500,
                'message' => $apiErrorMessage,
                'error'   => $jsonResponse['error'] ?? null, // Mengembalikan struktur objek error Meta utuh
                'body'    => $jsonResponse,
                'raw'     => $response['raw'] ?? null,
            ];
        }

        return [
            'success' => true,
            'status'  => $response['status'] ?? 200,
            'message' => 'Message sent successfully via Cloud API',
            'error'   => null,
            'body'    => $jsonResponse,
            'raw'     => $response['raw'] ?? null,
        ];
    }
}
