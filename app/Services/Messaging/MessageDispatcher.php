<?php
// app/Services/Messaging/MessageDispatcher.php

namespace App\Services\Messaging;

use App\Models\Messaging\MessageEvent;
use App\Helpers\LogHelper;
use App\Helpers\FormatReceiver;
use App\Jobs\Whatsapp\SendWhatsappMessageJob;
use App\Services\Messaging\Contracts\MessageActionHandler;
use Throwable;
use Illuminate\Support\Carbon;

class MessageDispatcher
{
    public function __construct(
        private MessageActionRegistry $registry,
        private TemplateEngine $templates
    ) {}

    public function dispatch(
        string $action,
        callable $handlerFactory,
        ?string $queue = 'default',
        ?string $traceId = null,
        ?Carbon $delay = null,
        ?int $eventId = null,
        string $deviceId
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

            // Format receiver
            $formatter = new FormatReceiver();
            $formattedReceiver = $formatter->formatReceiver(
                $built['to'],
                null,
                true
            );

            $payload = [
                'phone'   => $formattedReceiver,
                'message' => $message,
            ];

            // Create / Update event
            if ($eventId) {

                $event = MessageEvent::findOrFail($eventId);

                $event->update([
                    'status'    => 'pending',
                    'stage'     => 'SENDING JOB',
                    'trace_id'  => $traceId,
                    'payload'   => $payload,
                    'device_id' => $deviceId,
                ]);
            } else {

                $event = MessageEvent::create([
                    'customer_id'  => $built['customer_id'] ?? null,
                    'channel'      => 'go_whatsapp',
                    'message_type' => $action,
                    'meta'         => $payload,
                    'status'       => 'pending',
                    'trace_id'     => $traceId,
                    'stage'        => 'SENDING JOB',
                    'device_id'    => $deviceId,
                ]);
            }

            // Dispatch job
            $job = SendWhatsappMessageJob::dispatch(
                $action,
                $formattedReceiver,
                $message,
                $built['customer_id'] ?? null,
                $traceId,
                $event->id,
                $deviceId
            )->onQueue($queue ?? 'default');

            if ($delay) {
                $job->delay($delay);
            }

            // ✅ SUCCESS LOG (RINGKAS & TRACEABLE)
            LogHelper::logInfo(
                null,
                [
                    'trace_id'    => $traceId,
                    'event_id'    => $event->id,
                    'action'      => $action,
                    'channel'     => 'go_whatsapp',
                    'device_id'   => $deviceId,
                    'receiver'    => $formattedReceiver,
                    'queue'       => $queue ?? 'default',
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
                    'trace_id' => $traceId,
                    'action'   => $action,
                    'stage'    => $stage,
                    'device_id' => $deviceId,
                    'error'    => $e->getMessage(),
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
