<?php

namespace App\Services\WhatsappWebhook\Handlers;

use App\Helpers\ExecutionTimer;
use App\Services\WhatsappWebhook\DTO\WebhookPayload;

abstract class BaseHandler
{
    abstract public function handle(WebhookPayload $payload, string $traceId, ExecutionTimer $timer): void;
}
