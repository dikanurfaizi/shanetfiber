<?php

namespace App\Services\WhatsappWebhook\Handlers;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Jobs\Whatsapp\ProcessGroupEventJob;
use App\Services\WhatsappWebhook\DTO\WebhookPayload;

class GroupHandler extends BaseHandler
{
    public function handle(WebhookPayload $payload, string $traceId, ExecutionTimer $timer): void
    {
        $p = $payload->payload ?? [];

        LogHelper::logInfo(null, [
            'type'    => $p['type'] ?? null,
            'chat_id' => $p['chat_id'] ?? null,
            'members' => $p['jids'] ?? [],
        ], 200, '[Webhook] Group Event', $traceId, $timer->lap());

        ProcessGroupEventJob::dispatch($payload->raw, $traceId)->onQueue('notification');
    }
}
