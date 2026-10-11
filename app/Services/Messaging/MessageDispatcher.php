<?php

namespace App\Services\Messaging;

use App\Models\Messaging\MessageEvent;
use App\Helpers\LogHelper;
use App\Helpers\FormatReceiver;
use App\Services\WhatsApp\WhatsAppMessageService;
use Throwable;

class MessageDispatcher
{
    public function __construct(
        private MessageActionRegistry $registry,
        private TemplateEngine $templates,
        private FormatReceiver $formatter,
        private WhatsAppMessageService $service
    ) {}

    public function dispatch(
        string $action,
        callable $handlerFactory,
        ?string $traceId = null,
        ?int $eventId = null,
        string $deviceId = ''
    ): void {

        $startTime = microtime(true);
        $stage = 'INIT';

        try {
            // Resolve handler
            $category = $this->registry->getCategory($action);
            $handler = $handlerFactory($category);

            // Validate & build
            $stage = 'BUILD';
            $handler->validate($traceId);
            $built = $handler->build($traceId);

            // Render message
            $message = $this->templates->renderBySlug(
                $built['template_slug'],
                $built['replacements'],
                $traceId
            );

            // Pemformatan nomor telepon
            $phoneNumberFormat = $this->formatter->formatReceiver($built['to'], null, false);
            $whatsappFormat = $this->formatter->formatReceiver($phoneNumberFormat, null, true);

            $payload = [
                'phone'   => $whatsappFormat,
                'message' => $message,
            ];

            // Data MessageEvent untuk audit log
            $eventData = [
                'status'       => 'pending',
                'stage'        => 'SENDING MESSAGE',
                'trace_id'     => $traceId,
                'meta'         => $payload,
                'device_id'    => $deviceId,
                'scheduled_at' => now(), // Mencatat timestamp saat dispatcher memproses
            ];

            if ($eventId) {
                $event = MessageEvent::findOrFail($eventId);
                $event->update(array_merge($eventData, ['payload' => $payload]));
            } else {
                $event = MessageEvent::create(array_merge($eventData, [
                    'customer_id'  => $built['customer_id'] ?? null,
                    'channel'      => 'go_whatsapp',
                    'message_type' => $action,
                    'phone_number' => $phoneNumberFormat,
                ]));
            }

            $logContext = [
                'event_id'  => $event->id,
                'action'    => $action,
                'channel'   => 'go_whatsapp',
                'device_id' => $deviceId,
                'receiver'  => $phoneNumberFormat,
            ];

            // LOG BEFORE SENDING
            LogHelper::logInfo(
                null,
                $logContext,
                200,
                '[MessageDispatcher] Executing WhatsApp API Service Send',
                $traceId,
                round(microtime(true) - $startTime, 4)
            );

            // ✅ Langsung Eksekusi Service API WhatsApp tanpa Job lagi!
            $this->service->send(
                messageType: $action,
                receiver: $whatsappFormat,
                message: $message,
                customerId: $built['customer_id'] ?? null,
                traceId: $traceId,
                messageEventId: $event->id,
                deviceId: $deviceId,
            );

            // ✅ SUCCESS LOG
            LogHelper::logInfo(
                null,
                $logContext,
                200,
                '[MessageDispatcher] Message Sent Successfully',
                $traceId,
                round(microtime(true) - $startTime, 4)
            );
        } catch (Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'trace_id'  => $traceId,
                    'action'    => $action,
                    'stage'     => $stage,
                    'device_id' => $deviceId,
                    'error'     => $e->getMessage(),
                ],
                500,
                '[MessageDispatcher] Dispatch failed',
                $traceId,
                round(microtime(true) - $startTime, 4)
            );

            throw $e;
        }
    }
}
