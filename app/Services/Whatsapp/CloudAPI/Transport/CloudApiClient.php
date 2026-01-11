<?php

namespace App\Services\Whatsapp\CloudAPI\Transport;

use App\Helpers\LogHelper;
use App\Helpers\WhatsappGraph;
use Illuminate\Support\Facades\Http;
use App\Models\Whatsapp\WhatsappGatewayAccount;
use App\Services\Whatsapp\CloudAPI\Payloads\DTO\TemplateMessageDTO;
use RuntimeException;

class CloudApiClient
{
    public function __construct(private array $httpOptions = []) {}

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

        $response = Http::withOptions($this->httpOptions)
            ->withToken(decrypt($account->meta_user_access_token))
            ->post($url, $payload);

        return [
            'status' => $response->status(),
            'body' => $response->json(),
            'raw' => $response->body(),
        ];
    }
}
