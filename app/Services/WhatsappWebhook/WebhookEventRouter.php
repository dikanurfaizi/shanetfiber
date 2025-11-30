<?php

namespace App\Services\WhatsappWebhook;

use App\Helpers\LogHelper;
use App\Services\WhatsappWebhook\DTO\WebhookPayload;
use App\Services\WhatsappWebhook\Handlers\MessageAckHandler;
use App\Services\WhatsappWebhook\Handlers\GroupHandler;
use App\Services\WhatsappWebhook\Handlers\TextMessageHandler;
use App\Services\WhatsappWebhook\Handlers\MediaMessageHandler;

class WebhookEventRouter
{
    public function __construct(
        private MessageAckHandler    $ackHandler,
        private GroupHandler         $groupHandler,
        private TextMessageHandler   $textHandler,
        private MediaMessageHandler  $mediaHandler,
    ) {}

    public function route(array $data, string $traceId): void
    {
        $payload = WebhookPayload::fromArray($data);

        // Message ACK
        if ($payload->event === 'message.ack') {
            $this->ackHandler->handle($payload, $traceId);
            return;
        }

        // Group event
        if ($payload->event === 'group.participants') {
            $this->groupHandler->handle($payload, $traceId);
            return;
        }

        // Text message
        if ($payload->text() !== null) {
            $this->textHandler->handle($payload, $traceId);
            return;
        }

        // Media
        if ($payload->media() !== null) {
            $this->mediaHandler->handle($payload, $traceId);
            return;
        }

        LogHelper::logInfo(null, [
            'raw' => $data,
        ], 400, '[Webhook] Unhandled event type', $traceId);
    }
}
