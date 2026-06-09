<?php

namespace App\Services\Messaging;

use App\Models\Messaging\MessageEvent;
use App\Helpers\LogHelper;
use App\Helpers\FormatReceiver;
use App\Helpers\DelayMessage;
use App\Jobs\Whatsapp\SendWhatsappMessageJob;
use Throwable;
use Illuminate\Support\Carbon;

class MessageDispatcher
{
    public function __construct(
        private MessageActionRegistry $registry,
        private TemplateEngine $templates,
        private FormatReceiver $formatter,
        private DelayMessage $delayMessage,
    ) {}

    public function dispatch(
        string $action,
        callable $handlerFactory,
        ?string $queue = 'default',
        ?string $traceId = null,
        ?int $eventId = null,
        string $deviceId,
        int $delaySeconds = 0
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

            $formattedReceiver = $this->formatter->formatReceiver(
                $built['to'],
                null,
                true
            );

            $payload = [
                'phone'   => $formattedReceiver,
                'message' => $message,
            ];

            $queueName = $queue ?? 'notification';

            // 1. Hitung delay aslinya di dalam delayMessage dengan menyertakan rentang detik dari depan
            $finalDelay = $this->delayMessage->calculation($action, $traceId, $delaySeconds, $queueName, $startTime);

            // Create / Update event
            if ($eventId) {
                $event = MessageEvent::findOrFail($eventId);
                $event->update([
                    'status'        => 'pending',
                    'stage'         => 'SENDING JOB',
                    'trace_id'      => $traceId,
                    'meta'          => $payload,
                    'payload'       => $payload,
                    'device_id'     => $deviceId,
                    'scheduled_at'  => $finalDelay,
                ]);
            } else {
                $event = MessageEvent::create([
                    'customer_id'   => $built['customer_id'] ?? null,
                    'channel'       => 'go_whatsapp',
                    'message_type'  => $action,
                    'phone_number'  => $formattedReceiver,
                    'meta'          => $payload,
                    'status'        => 'pending',
                    'trace_id'      => $traceId,
                    'stage'         => 'SENDING JOB',
                    'device_id'     => $deviceId,
                    'scheduled_at'  => $finalDelay,
                ]);
            }

            // === LOG BEFORE DISPATCH ===
            LogHelper::logInfo(
                null,
                [
                    'event_id'      => $event->id,
                    'action'        => $action,
                    'channel'       => 'go_whatsapp',
                    'device_id'     => $deviceId,
                    'receiver'      => $formattedReceiver,
                    'queue'         => $queueName,
                    'delay_seconds' => $finalDelay->diffInSeconds(Carbon::now(), false),
                ],
                200,
                '[MessageDispatcher] Dispatching',
                $traceId,
                round(microtime(true) - $startTime, 4)
            );

            // ✅ Memicu job penanganan pesan utama yang valid
            SendWhatsappMessageJob::dispatch(
                $action,
                $formattedReceiver,
                $message,
                $built['customer_id'] ?? null,
                $traceId,
                $event->id,
                $deviceId
            )
                ->onQueue($queueName)
                ->delay($finalDelay);

            // ✅ SUCCESS LOG
            LogHelper::logInfo(
                null,
                [
                    'event_id'    => $event->id,
                    'action'      => $action,
                    'channel'     => 'go_whatsapp',
                    'device_id'   => $deviceId,
                    'receiver'    => $formattedReceiver,
                    'queue'       => $queueName,
                ],
                200,
                '[MessageDispatcher] Dispatched',
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
