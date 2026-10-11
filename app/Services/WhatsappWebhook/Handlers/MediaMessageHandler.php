<?php

namespace App\Services\WhatsAppWebhook\Handlers;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Jobs\WhatsApp\ProcessIncomingMediaMessageJob;
use App\Services\WhatsAppWebhook\DTO\WebhookPayload;

class MediaMessageHandler extends BaseHandler
{
    public function handle(WebhookPayload $payload, string $traceId, ExecutionTimer $timer): void
    {
        $media = $payload->media();
        if ($media === null) {
            return;
        }

        LogHelper::logInfo(null, [
            'media_path' => $media['media_path'] ?? '',
            'mime_type'  => $media['mime_type'] ?? '',
        ], 200, '[Webhook] Media Message Received', $traceId, $timer->lap());

        ProcessIncomingMediaMessageJob::dispatch($payload->raw, $traceId)->onQueue('notification');
    }
}
