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

        // ===== BASIC TRACE LOG (1 BARIS SAJA) =====
        LogHelper::logInfo(
            null,
            [
                'trace_id'  => $traceId,
                'event'     => $payload->event,
                'device_id' => $payload->deviceId,
            ],
            200,
            '[Webhook] Event received',
            $traceId
        );

        // ===== DEVICE VALIDATION =====
        if (!$payload->deviceId) {
            LogHelper::logWarning(
                null,
                [
                    'trace_id' => $traceId,
                    'event'    => $payload->event,
                ],
                400,
                '[Webhook] Missing device_id',
                $traceId
            );
            return;
        }

        match ($payload->event) {

            'message.ack' => $this->ackHandler->handle($payload, $traceId),

            'group.participants' => $this->groupHandler->handle($payload, $traceId),

            'message' => $this->handleMessage($payload, $traceId),

            default => $this->logUnhandled($payload, $traceId),
        };
    }

    private function handleMessage(WebhookPayload $payload, string $traceId): void
    {
        if ($payload->isText()) {
            $this->textHandler->handle($payload, $traceId);
            return;
        }

        if (
            $payload->isImage() ||
            $payload->isVideo() ||
            $payload->isDocument()
        ) {
            $this->mediaHandler->handle($payload, $traceId);
            return;
        }

        LogHelper::logWarning(
            null,
            [
                'trace_id'  => $traceId,
                'device_id' => $payload->deviceId,
                'event'     => 'message',
            ],
            400,
            '[Webhook] Unsupported message type',
            $traceId
        );
    }

    private function logUnhandled(WebhookPayload $payload, string $traceId): void
    {
        LogHelper::logWarning(
            null,
            [
                'trace_id'  => $traceId,
                'device_id' => $payload->deviceId,
                'event'     => $payload->event,
            ],
            400,
            '[Webhook] Unhandled event',
            $traceId
        );
    }
}
