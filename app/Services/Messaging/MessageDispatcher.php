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
            $finalDelay = $this->delayMessage($action, $traceId, $delaySeconds, $queueName, $startTime);

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
     * Mendukung bypass instan untuk pesan respons interaktif pelanggan.
     */
    private function delayMessage(string $action, string $traceId, int $inputDelaySeconds = 0, string $queue = 'notification', float $startTime = 0): Carbon
    {
        $source         = null;
        $baseTime       = null;
        $safetyInterval = 0;
        $lastJob        = null;
        $lastEvent      = null;

        // ===============================================================
        // 1. CHECK INTERACTIVE BYPASS ACTION (PESAN RESPONS INTERAKTIF)
        // ===============================================================
        $interactiveActions = [
            WhatsAppAction::UNKNOWN_KEYWORD->value,
            WhatsAppAction::UNKNOWN_CUSTOMER->value,
            WhatsAppAction::UNKNOWN_CUSTOMER_INFO->value,
            WhatsAppAction::CALL_ADMIN->value,
            WhatsAppAction::UNKNOWN_CALL_ADMIN->value,
            WhatsAppAction::UNKNOWN_INFO_PACKAGE->value,
            WhatsAppAction::MENU->value,
            WhatsAppAction::STOP->value,
            WhatsAppAction::YA->value,
            WhatsAppAction::THANK_YOU->value,
            WhatsAppAction::TEST_MESSAGE->value,
            WhatsAppAction::ISSUES->value,

            // ✅ Dimasukkan ke bypass agar interaksi transaksi pelanggan langsung instan
            WhatsAppAction::PAYMENT_CHANNEL_UPDATE->value,
            WhatsAppAction::TRANSFER->value,
            WhatsAppAction::CASH->value,
            WhatsAppAction::PAYMENT_GATEWAY->value,
        ];

        if (in_array($action, $interactiveActions, true)) {
            // Untuk pesan interaktif, potong kompas langsung instan + 5 detik sebagai buffer urutan antrean
            $baseTime    = now();
            $final_delay = $baseTime->copy()->addSeconds(5);
            $source      = 'interactive_bypass';
        } else {
            // ===============================================================
            // 2. NORMAL FLOW: AMBIL DATA ANTREAN TERAKHIR (JOBS / EVENTS)
            // ===============================================================

            // Ambil kriteria job terakhir di tabel antrean (Berdasarkan ID terbesar)
            $lastJob = DB::table('jobs')
                ->where('queue', $queue)
                ->orderByDesc('id')
                ->first();

            if ($lastJob) {
                $baseTime = Carbon::createFromTimestamp($lastJob->available_at);
                $source   = 'jobs';
            } else {
                // Fallback ke data riwayat message_events jika tabel jobs kosong
                $lastEvent = MessageEvent::where('channel', 'go_whatsapp')
                    ->orderByDesc('id')
                    ->first();

                if ($lastEvent) {
                    // Parse nilai waktu jika kolom tersebut tidak null
                    $scheduled = $lastEvent->scheduled_at ? Carbon::parse($lastEvent->scheduled_at) : null;
                    $sent      = $lastEvent->sent_at ? Carbon::parse($lastEvent->sent_at) : null;

                    // Skenario A: Jika kedua kolom terisi (Paling sering terjadi pada pesan yang sudah sukses)
                    if ($scheduled && $sent) {
                        $baseTime = $scheduled->greaterThan($sent) ? $scheduled : $sent;
                        $source   = $scheduled->greaterThan($sent) ? 'message_events_scheduled' : 'message_events_sent';
                    }
                    // Skenario B: Jika hanya scheduled_at saja yang terisi (Pesan baru yang masih antre/pending)
                    // ✅ Sesuai logikanya: jika scheduled_at ada & sent_at null, ambil dari scheduled_at untuk penambahan waktu
                    elseif ($scheduled) {
                        $baseTime = $scheduled;
                        $source   = 'message_events_only_scheduled';
                    }
                    // Skenario C: Jika hanya sent_at saja yang terisi (Fallback pengaman data historis)
                    elseif ($sent) {
                        $baseTime = $sent;
                        $source   = 'message_events_only_sent';
                    }
                }
            } // 👈 FIX: Penutup blok 'else fallback' yang sebelumnya salah posisi

            // ===============================================================
            // PENJAGAAN STALE DATA (KOREKSI WAKTU LAMPAU)
            // ===============================================================
            // Jika seluruh antrean bersih/kosong, ATAU waktu acuan dari database 
            // ternyata sudah lewat/lampau, paksa baseTime mulai dari waktu sekarang (now())
            if (!$baseTime || $baseTime->isPast()) {
                $baseTime = now();
                $source   = ($baseTime === null) ? 'fallback_now' : $source . '_adjusted_to_now';
            }

            // ===============================================================
            // 3. KALKULASI RENTANG ANTREAN NORMAL
            // ===============================================================
            $safetyInterval = rand(5, 10); // Jeda pengaman acak agar tidak bertabrakan di detik yang sama
            $final_delay = $baseTime->copy()
                ->addSeconds($inputDelaySeconds)
                ->addSeconds($safetyInterval);
        }

        // ===============================================================
        // 4. LOGGING (SINKRON DENGAN HASIL REALTIME)
        // ===============================================================
        LogHelper::logInfo(
            null,
            [
                'action'                        => $action,
                'source'                        => $source,
                'base_time'                     => $baseTime->toDateTimeString(),
                'input_requested_delay_seconds' => $inputDelaySeconds,
                'safety_interval_seconds'       => $safetyInterval,
                'final_delay'                   => $final_delay->toDateTimeString(),
                'last_job_available_at'         => $lastJob->available_at ?? null,
                'last_event_scheduled_at'       => $lastEvent->scheduled_at ?? null,
            ],
            200,
            "[MessageDispatcher] Dynamic relative delay calculated (" . ($source === 'interactive_bypass' ? 'BYPASS' : 'QUEUED') . ")",
            $traceId,
            round(microtime(true) - $startTime, 4)
        );

        return $final_delay;
    }
}
