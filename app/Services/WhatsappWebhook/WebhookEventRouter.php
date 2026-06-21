<?php

namespace App\Services\WhatsappWebhook;

use App\Helpers\ExecutionTimer;
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
        $timer = new ExecutionTimer();
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
            $traceId,
            $timer->lap()
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
                $traceId,
                $timer->lap()
            );
            return;
        }

        match ($payload->event) {

            'message.ack' => $this->ackHandler->handle($payload, $traceId, $timer),

            'group.participants' => $this->groupHandler->handle($payload, $traceId, $timer),

            'message' => $this->handleMessage($payload, $traceId, $timer),

            default => $this->logUnhandled($payload, $traceId, $timer),
        };
    }

    private function handleMessage(WebhookPayload $payload, string $traceId, ExecutionTimer $timer): void
    {
        if ($payload->isGroupMessage()) {
            LogHelper::logInfo(null, [
                'trace_id' => $traceId,
                'chat_id'  => $payload->payload['chat_id'] ?? null,
                'sender'   => $payload->sender()
            ], 200, '[Webhook] Group message skipped. Bot auto-reply is muted for groups.', $traceId, $timer->lap());

            return;
        }

        if ($payload->isText()) {
            $this->textHandler->handle($payload, $traceId, $timer);
            return;
        }

        if ($payload->hasMedia()) {
            $this->mediaHandler->handle($payload, $traceId, $timer);
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
            $traceId,
            $timer->lap()
        );
    }

    private function logUnhandled(WebhookPayload $payload, string $traceId, ExecutionTimer $timer): void
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
