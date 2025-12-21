<?php

namespace App\Services\WhatsappWebhook\Handlers;

use App\Helpers\LogHelper;
use App\Jobs\Whatsapp\ProcessMessageAckJob;
use App\Services\WhatsappWebhook\DTO\WebhookPayload;

class MessageAckHandler extends BaseHandler
{
    public function handle(WebhookPayload $payload, string $traceId): void
    {
        $p = $payload->payload ?? [];

        LogHelper::logInfo(null, [
            'receipt_type' => $p['receipt_type'] ?? '-',
            'ids'          => $p['ids'] ?? [],
            'chat_id'      => $p['chat_id'] ?? '-',
        ], 200, '[Webhook] Message ACK', $traceId);

        // Offload ke job (opsional tapi sehat untuk scale)
        ProcessMessageAckJob::dispatch($payload->raw, $traceId)->onQueue('notification');
    }
}
