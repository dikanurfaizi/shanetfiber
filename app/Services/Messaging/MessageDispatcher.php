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

            // OPTIMASI 1: Gabungkan pemformatan nomor telepon agar tidak memanggil method 2x secara redundant
            $phoneNumberFormat = $this->formatter->formatReceiver($built['to'], null, false);
            $whatsappFormat = $this->formatter->formatReceiver($phoneNumberFormat, null, true);

            $payload = [
                'phone'   => $whatsappFormat,
                'message' => $message,
            ];

            $queueName = $queue ?? 'notification';
            $finalDelay = $this->delayMessage->calculation($action, $traceId, $delaySeconds, $queueName, $startTime);

            // OPTIMASI 2: DRY (Don't Repeat Yourself) untuk data MessageEvent
            $eventData = [
                'status'       => 'pending',
                'stage'        => 'SENDING JOB',
                'trace_id'     => $traceId,
                'meta'         => $payload,
                'device_id'    => $deviceId,
                'scheduled_at' => $finalDelay,
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

            // OPTIMASI 3: Ekstrak kesamaan data log untuk mengurangi boilerplate code
            $logContext = [
                'event_id'  => $event->id,
                'action'    => $action,
                'channel'   => 'go_whatsapp',
                'device_id' => $deviceId,
                'receiver'  => $phoneNumberFormat,
                'queue'     => $queueName,
            ];

            // === LOG BEFORE DISPATCH ===
            // FIX: Mengganti createFromTimestampPsr7 dengan createFromTimestamp standar yang menerima float/int
            LogHelper::logInfo(
                null,
                array_merge($logContext, [
                    'delay_seconds' => $finalDelay
                ]),
                200,
                '[MessageDispatcher] Dispatching',
                $traceId,
                round(microtime(true) - $startTime, 4)
            );

            // ✅ Memicu job penanganan pesan utama
            SendWhatsappMessageJob::dispatch(
                $action,
                $whatsappFormat,
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
                $logContext,
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
