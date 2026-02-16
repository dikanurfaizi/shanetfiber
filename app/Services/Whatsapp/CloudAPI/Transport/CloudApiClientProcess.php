<?php

namespace App\Services\Whatsapp\CloudAPI\Transport;

use App\Helpers\WhatsappGraph;
use App\Models\Whatsapp\WhatsappGatewayAccount;
use App\Services\Whatsapp\CloudAPI\Payloads\DTO\TemplateMessageDTO;
use RuntimeException;

class CloudApiClientProcess
{
    public function __construct(protected CloudApiClient $apiClient) {}

    public function send(WhatsappGatewayAccount $account, TemplateMessageDTO $dto): array
    {
        $payload = $dto->toArray();

        $phoneNumberId = $account->defaultNumber?->meta_phone_number_id;

        if (!$phoneNumberId) {
            throw new RuntimeException("WhatsApp default number not configured for account {$account->id}");
        }

        $url = WhatsappGraph::sendMessage(
            graphUrl: $account->meta_graph_url,
            graphVersion: $account->meta_graph_version,
            phoneNumberId: $phoneNumberId
        );

        $response = $this->apiClient->handle($url, $account->meta_user_access_token, $payload);

        if (!$response['ok']) {
            throw new RuntimeException(
                "Cloud API request failed with status {$response['status']}: {$response['raw']}"
            );
        }

        return [
            'status' => $response['status'],
            'body' => $response['json'],
            'raw' => $response['raw'],
        ];
    }
}
