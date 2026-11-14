<?php

namespace App\Services\WhatsAppWebhook\Handlers;

use App\Events\SingleNotification\IncomingWhatsAppMessageEvent;
use App\Helpers\ExecutionTimer;
use App\Services\WhatsAppWebhook\DTO\WebhookPayload;

class TextMessageHandler extends BaseHandler
{
    public function handle(WebhookPayload $payload, string $traceId, ExecutionTimer $timer): void
    {
        IncomingWhatsAppMessageEvent::dispatch($payload, $traceId);
    }
}
