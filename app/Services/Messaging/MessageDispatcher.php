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

            $queueName = $queue ?? 'notification';

            // 👈 2. Hitung delay aslinya di dalam delayMessage dengan menyertakan rentang detik dari depan
            $finalDelay = $this->delayMessage($traceId, $delaySeconds, $queueName, $startTime);

            // Create / Update event
            if ($eventId) {
                $event = MessageEvent::findOrFail($eventId);
                $event->update([
                    'status'        => 'pending',
                    'stage'         => 'SENDING JOB',
                    'trace_id'      => $traceId,
                    'payload'       => $payload,
                    'device_id'     => $deviceId,
                    'scheduled_at'  => $finalDelay,
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

    /**
     * Mengkalkulasi delay dinamis: Titik Akhir Antrean + Input Detik dari Depan + Jeda Pengaman
     */
    private function delayMessage(string $traceId, int $inputDelaySeconds = 0, string $queue = 'notification', float $startTime = 0): Carbon
    {
        $source = null;
        $baseTime = null;

        // 1. Ambil kriteria job terakhir di tabel antrean
        $lastJob = DB::table('jobs')
            ->where('queue', $queue)
            ->orderByDesc('available_at')
            ->first();

        if ($lastJob) {
            $baseTime = Carbon::createFromTimestamp($lastJob->available_at);
            $source = 'jobs';
        } else {
            // 2. Fallback ke data riwayat message_events
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
            }
        }

        // 3. Jika seluruh antrean bersih/kosong, atau waktu terakhirnya sudah lewat (isPast)
        // Kita jadikan waktu sekarang (now()) sebagai titik dasar kalkulasi
        if (!$baseTime || $baseTime->isPast()) {
            $baseTime = now();
            $source = ($baseTime === null) ? 'fallback_now' : $source . '_adjusted_to_now';
        }

        // 4. Kalkulasi Rentang: Waktu Dasar + Detik Permintaan Depan + Jeda Pengaman Acak (1-2 Menit)
        $safetyInterval = rand(60, 100);
        $final_delay = $baseTime->copy()
            ->addSeconds($inputDelaySeconds)
            ->addSeconds($safetyInterval);

        LogHelper::logInfo(
            null,
            [
                'source' => $source,
                'base_time' => $baseTime->toDateTimeString(),
                'input_requested_delay_seconds' => $inputDelaySeconds,
                'safety_interval_seconds' => $safetyInterval,
                'final_delay' => $final_delay->toDateTimeString(),
                'last_job_available_at' => $lastJob?->available_at ?? null,
                'last_event_scheduled_at' => isset($lastEvent) ? $lastEvent->scheduled_at : null,
            ],
            200,
            '[MessageDispatcher] Dynamic relative delay calculated',
            $traceId,
            round(microtime(true) - $startTime, 4)
        );

        return $final_delay;
    }
}
