<?php

namespace App\Services\Whatsapp\CloudAPI\Messages;

use App\Helpers\LogHelper;
use App\Helpers\WhatsappGraph;
use App\Models\Whatsapp\WhatsappGatewayAccount;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class MessageService
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
     * Kirim request ke endpoint /messages.
     */
    public function send(
        WhatsappGatewayAccount $account,
        string $phoneNumberId,
        array $payload,
        string $traceId
    ): ?Response {
        $url = WhatsappGraph::sendMessage(
            graphUrl: $account->meta_graph_url,
            graphVersion: $account->meta_graph_version,
            phoneNumberId: $phoneNumberId
        );

        $response = Http::withOptions($this->httpOptions)
            ->withToken(decrypt($account->meta_user_access_token))
            ->post($url, $payload);

        if (! $response->successful()) {
            LogHelper::logWarning(null, [
                'account_id' => $account->id,
                'status' => $response->status(),
                'response' => $response->json(),
                'payload' => $payload,
            ], 400, 'WhatsApp Cloud API: Failed to send message.', $traceId);

            return null;
        }

        LogHelper::logInfo(null, [
            'account_id' => $account->id,
            'status' => $response->status(),
        ], 200, 'WhatsApp Cloud API: Message sent successfully.', $traceId);

        return $response;
    }
}
