<?php

namespace App\Services\WhatsAppWebhook\Handlers;

use App\Helpers\ExecutionTimer;
use App\Services\WhatsAppWebhook\DTO\WebhookPayload;

abstract class BaseHandler
{
    abstract public function handle(WebhookPayload $payload, string $traceId, ExecutionTimer $timer): void;
}
