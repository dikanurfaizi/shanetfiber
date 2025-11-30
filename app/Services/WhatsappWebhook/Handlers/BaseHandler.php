<?php

namespace App\Services\WhatsappWebhook\Handlers;

use App\Services\WhatsAppWebhook\DTO\WebhookPayload;

abstract class BaseHandler
{
    abstract public function handle(WebhookPayload $payload, string $traceId): void;
}
