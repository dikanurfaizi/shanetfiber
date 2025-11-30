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
        ?Carbon $delay = null
    ): void {
        $startTime = microtime(true);
        $stage = 'INIT';

        try {
            // === INIT ===
            LogHelper::logInfo(null, [
                'trace_id' => $traceId,
                'action'   => $action,
                'queue'    => $queue,
                'stage'    => $stage,
            ], 200, '[MessageDispatcher] Starting dispatch process', $traceId);

            /** @var MessageActionHandler $handler */
            $handler = $handlerFactory();

            // === VALIDATE ===
            $stage = 'VALIDATE';
            $handler->validate($traceId);
            LogHelper::logInfo(null, [
                'trace_id' => $traceId,
                'action'   => $action,
                'stage'    => $stage,
            ], 200, '[MessageDispatcher] Handler validation completed', $traceId);

            // === BUILD ===
            $stage = 'BUILD';
            $built = $handler->build($traceId);
            LogHelper::logInfo(null, [
                'trace_id'       => $traceId,
                'action'         => $action,
                'built_to'       => $built['to'] ?? null,
                'template_slug'  => $built['template_slug'] ?? null,
                'customer_id'    => $built['customer_id'] ?? null,
                'stage'          => $stage,
            ], 200, '[MessageDispatcher] Message build completed', $traceId);

            // === RENDER ===
            $stage = 'RENDER';
            $message = $this->templates->renderBySlug(
                $built['template_slug'],
                $built['replacements'],
                $traceId
            );
            LogHelper::logInfo(null, [
                'trace_id'      => $traceId,
                'template_slug' => $built['template_slug'],
                'message_len'   => strlen($message ?? ''),
                'stage'         => $stage,
            ], 200, '[MessageDispatcher] Message template rendered', $traceId);

            // === CREATE_EVENT ===
            $stage = 'CREATE_EVENT';

            $formatter = new FormatReceiver();
            $formattedReceiver = $formatter->formatReceiver($built['to'], null, true);

            LogHelper::logInfo(null, [
                'trace_id' => $traceId,
                'action' => $action,
                'stage' => $stage,
                'receiver' => $formattedReceiver,
            ], 200, '[MessageDispatcher] Preparing to create pending MessageEvent', $traceId);

            $event = MessageEvent::create([
                'customer_id'  => $built['customer_id'] ?? null,
                'channel'      => 'whatsapp',
                'message_type' => $action,
                'meta'         => [
                    'phone'   => $formattedReceiver,
                    'message' => $message,
                ],
                'status'       => 'pending',
                'trace_id'     => $traceId,
            ]);

            LogHelper::logInfo(null, [
                'trace_id'     => $traceId,
                'action'       => $action,
                'stage'        => $stage,
                'event_id'     => $event->id,
                'customer_id'  => $event->customer_id,
                'gateway_id'   => $event->gateway_id,
                'status'       => $event->status,
            ], 200, '[MessageDispatcher] Pending MessageEvent created successfully', $traceId);

            // === DISPATCH_JOB ===
            $stage = 'DISPATCH_JOB';

            $job = SendWhatsappMessageJob::dispatch(
                $action,
                $formattedReceiver,
                $message,
                $built['customer_id'] ?? null,
                $traceId,
                $event->id // ✅ kirim ID event
            )->onQueue($queue ?? 'default');

            if ($delay) {
                $job->delay($delay);
            }

            $duration = round(microtime(true) - $startTime, 4);
            LogHelper::logInfo(null, [
                'trace_id' => $traceId,
                'action'   => $action,
                'queue'    => $queue ?? 'default',
                'event_id' => $event->id,
                'stage'    => $stage,
            ], 200, '[MessageDispatcher] WhatsApp message job dispatched successfully', $traceId, $duration);
        } catch (Throwable $e) {
            $duration = round(microtime(true) - $startTime, 4);
            LogHelper::logError(null, [
                'trace_id'       => $traceId,
                'action'         => $action,
                'queue'          => $queue ?? 'default',
                'stage'          => $stage,
                'error_message'  => $e->getMessage(),
                'error_file'     => $e->getFile(),
                'error_line'     => $e->getLine(),
            ], 500, "[MessageDispatcher] Failed at stage: {$stage}", $traceId, $duration);
        }
    }
}
