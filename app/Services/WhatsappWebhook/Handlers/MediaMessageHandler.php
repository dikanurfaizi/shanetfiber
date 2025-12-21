<?php

namespace App\Services\WhatsappWebhook\Handlers;

use App\Helpers\LogHelper;
use App\Jobs\WhatsApp\ProcessIncomingMediaMessageJob;
use App\Services\WhatsappWebhook\DTO\WebhookPayload;

class MediaMessageHandler extends BaseHandler
{
    public function handle(WebhookPayload $payload, string $traceId): void
    {
        $media = $payload->media();
        if ($media === null) {
            return;
        }

        LogHelper::logInfo(null, [
            'media_path' => $media['media_path'] ?? '',
            'mime_type'  => $media['mime_type'] ?? '',
        ], 200, '[Webhook] Media Message Received', $traceId);

        ProcessIncomingMediaMessageJob::dispatch($payload->raw, $traceId)->onQueue('notification');
    }
}
