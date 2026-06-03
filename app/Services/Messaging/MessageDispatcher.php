<?php

namespace App\Services\Messaging;

use App\Enums\WhatsAppAction;
use App\Models\Messaging\MessageEvent;
use App\Helpers\LogHelper;
use App\Helpers\FormatReceiver;
use App\Jobs\Whatsapp\SendWhatsappMessageJob;
use Throwable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
        ?int $eventId = null,
        string $deviceId,
        ?Carbon $delay = null // 👈 1. Tambahkan parameter delay opsional di bagian akhir (default null)
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

            // 👈 2. Jika parameter $delay tidak null, gunakan nilai tersebut. Jika null, jalankan delayMessage().
            $finalDelay = $delay ?? $this->delayMessage($action);

            // Create / Update event
            if ($eventId) {

                $event = MessageEvent::findOrFail($eventId);

                $event->update([
                    'status'        => 'pending',
                    'stage'         => 'SENDING JOB',
                    'trace_id'      => $traceId,
                    'payload'       => $payload,
                    'device_id'     => $deviceId,
                    'scheduled_at'  => $finalDelay, // 👈 Ganti jadi $finalDelay
                ]);
            } else {

                $event = MessageEvent::create([
                    'customer_id'   => $built['customer_id'] ?? null,
                    'channel'       => 'go_whatsapp',
                    'message_type'  => $action,
                    'meta'          => $payload,
                    'status'        => 'pending',
                    'trace_id'      => $traceId,
                    'stage'         => 'SENDING JOB',
                    'device_id'     => $deviceId,
                    'scheduled_at'  => $finalDelay, // 👈 Ganti jadi $finalDelay
                ]);
            }

            $queue = $queue ?? 'notification';

            // === LOG BEFORE DISPATCH (RINGKAS & TRACEABLE) ===
            LogHelper::logInfo(
                null,
                [
                    'event_id'      => $event->id,
                    'action'        => $action,
                    'channel'       => 'go_whatsapp',
                    'device_id'     => $deviceId,
                    'receiver'      => $formattedReceiver,
                    'queue'         => $queue,
                    'delay_seconds' => $finalDelay->diffInSeconds(Carbon::now()), // 👈 Ganti jadi $finalDelay
                ],
                200,
                '[MessageDispatcher] Dispatching',
                $traceId,
                round(microtime(true) - $startTime, 4)
            );

            SendWhatsappMessageJob::dispatch(
                $action,
                $formattedReceiver,
                $message,
                $built['customer_id'] ?? null,
                $traceId,
                $event->id,
                $deviceId
            )
                ->onQueue($queue)
                ->delay($finalDelay); // 👈 Ganti jadi $finalDelay

            // ✅ SUCCESS LOG (RINGKAS & TRACEABLE)
            LogHelper::logInfo(
                null,
                [
                    'event_id'    => $event->id,
                    'action'      => $action,
                    'channel'     => 'go_whatsapp',
                    'device_id'   => $deviceId,
                    'receiver'    => $formattedReceiver,
                    'queue'       => $queue,
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

    private function delayMessage($type)
    {
        $source = null;

        // 1. Ambil job terakhir di queue notification
        $lastJob = DB::table('jobs')
            ->where('queue', 'notification')
            ->orderByDesc('available_at')
            ->first();

        if ($lastJob) {
            $baseTime = Carbon::createFromTimestamp($lastJob->available_at);
            $source = 'jobs';
        } else {
            // 2. fallback ke message_events
            $lastEvent = MessageEvent::where('channel', 'go_whatsapp')
                ->orderByDesc('created_at')
                ->first();

            if ($lastEvent && ($lastEvent->scheduled_at || $lastEvent->sent_at)) {
                $scheduled = $lastEvent->scheduled_at ? Carbon::parse($lastEvent->scheduled_at) : null;
                $sent = $lastEvent->sent_at ? Carbon::parse($lastEvent->sent_at) : null;

                if ($scheduled && $sent) {
                    $baseTime = $scheduled->greaterThan($sent) ? $scheduled : $sent;
                    $source = $scheduled->greaterThan($sent) ? 'message_events_scheduled' : 'message_events_sent';
                } else {
                    $baseTime = $scheduled ?? $sent;
                    $source = $scheduled ? 'message_events_scheduled' : 'message_events_sent';
                }

                if (now()->diffInMinutes($baseTime) >= 1) {
                    $baseTime = now();
                    $source = 'fallback_stale_data';
                }
            } else {
                $baseTime = now();
                $source = 'fallback_now';
            }
        }

        $interval = rand(60, 300);
        $final_delay = $baseTime->copy()->addSeconds($interval);

        LogHelper::logInfo(
            null,
            [
                'source' => $source,
                'base_time' => $baseTime->toDateTimeString(),
                'interval_seconds' => $interval,
                'final_delay' => $final_delay->toDateTimeString(),
                'last_job_available_at' => $lastJob?->available_at,
                'last_event_sent_at' => $lastEvent?->sent_at ?? null,
            ],
            200,
            '[MessageDispatcher] Calculated delay',
        );

        return $final_delay;
    }
}
