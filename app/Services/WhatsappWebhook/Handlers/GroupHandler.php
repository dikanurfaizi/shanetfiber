<?php

namespace App\Services\WhatsappWebhook\Handlers;

use App\Helpers\LogHelper;
use App\Jobs\WhatsApp\ProcessGroupEventJob;
use App\Services\WhatsAppWebhook\DTO\WebhookPayload;

class GroupHandler extends BaseHandler
{
    public function handle(WebhookPayload $payload, string $traceId): void
    {
        $p = $payload->payload ?? [];

        LogHelper::logInfo(null, [
            'type'    => $p['type'] ?? null,
            'chat_id' => $p['chat_id'] ?? null,
            'members' => $p['jids'] ?? [],
        ], 200, '[Webhook] Group Event', $traceId);

        ProcessGroupEventJob::dispatch($payload->raw, $traceId)->onQueue('whatsapp-webhook');
    }
}
