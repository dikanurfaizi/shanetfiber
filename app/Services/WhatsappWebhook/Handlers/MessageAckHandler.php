<?php

namespace App\Services\WhatsAppWebhook\Handlers;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Jobs\WhatsApp\ProcessMessageAckJob;
use App\Services\WhatsAppWebhook\DTO\WebhookPayload;

class MessageAckHandler extends BaseHandler
{
    public function handle(WebhookPayload $payload, string $traceId, ExecutionTimer $timer): void
    {
        $p = $payload->payload ?? [];

        LogHelper::logInfo(null, [
            'receipt_type' => $p['receipt_type'] ?? '-',
            'ids'          => $p['ids'] ?? [],
            'chat_id'      => $p['chat_id'] ?? '-',
        ], 200, '[Webhook] Message ACK', $traceId, $timer->lap());

        // ✅ BERSIH: Cukup kirim data mentah array ($payload->raw) dan string ($traceId) ke Job
        ProcessMessageAckJob::dispatch($payload->raw, $traceId)->onQueue('notification');
    }
}
