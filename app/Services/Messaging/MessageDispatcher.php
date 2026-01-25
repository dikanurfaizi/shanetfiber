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
        $stage = 'INIT';
        $startTime = microtime(true);

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

            // Create MessageEvent (audit source of truth)
            $formatter = new FormatReceiver();
            $formattedReceiver = $formatter->formatReceiver(
                $built['to'],
                null,
                true
            );

            $event = MessageEvent::create([
                'customer_id'  => $built['customer_id'] ?? null,
                'channel'      => 'go_whatsapp',
                'message_type' => $action,
                'meta'         => [
                    'phone' => $formattedReceiver,
                ],
                'status'       => 'pending',
                'trace_id'     => $traceId,
            ]);

            // Dispatch job
            $job = SendWhatsappMessageJob::dispatch(
                $action,
                $formattedReceiver,
                $message,
                $built['customer_id'] ?? null,
                $traceId,
                $event->id
            )->onQueue($queue ?? 'default');

            if ($delay) {
                $job->delay($delay);
            }
        } catch (Throwable $e) {

            $duration = round(microtime(true) - $startTime, 4);

            // 🔔 LOG HANYA ERROR
            LogHelper::logError(
                null,
                [
                    'action' => $action,
                    'queue'  => $queue ?? 'default',
                    'stage'  => $stage,
                    'trace_id' => $traceId,
                    'error'  => $e->getMessage(),
                ],
                500,
                '[MessageDispatcher] Dispatch failed',
                $traceId,
                $duration
            );

            throw $e;
        }
    }
}
