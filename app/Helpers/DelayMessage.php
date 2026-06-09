<?php

namespace App\Helpers;

use App\Models\Messaging\MessageEvent;
use App\Models\Whatsapp\WhatsappMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DelayMessage
{
    /**
     * Mengkalkulasi delay dinamis: Titik Akhir Antrean + Input Detik dari Depan + Jeda Pengaman
     * Mendukung bypass instan untuk pesan respons interaktif pelanggan berbasis Database.
     */
    public function calculation(string $action, string $traceId, int $inputDelaySeconds = 0, string $queue = 'notification', float $startTime = 0): Carbon
    {
        $source         = null;
        $baseTime       = null;
        $safetyInterval = 0;
        $lastJob        = null;
        $lastEvent      = null;

        // ===============================================================
        // 1. DYNAMIC CHECK: INTERACTIVE BYPASS VIA DATABASE
        // ===============================================================
        // Gunakan cache static internal agar kueri ke DB tidak duplikat di dalam looping massal
        static $interactiveCache = [];

        if (!isset($interactiveCache[$action])) {
            // Menggunakan nama model konfigurasi pesan Anda (misal: WhatsappMessage)
            // Pastikan namespace model ini sudah di-use di bagian atas file class
            $interactiveCache[$action] = WhatsappMessage::where('category', 'INTERACTIVE')
                ->where('slug', $action)
                ->exists();
        }

        // 👈 FIX: Sekarang kondisi if mengecek langsung hasil kueri dinamis database
        if ($interactiveCache[$action]) {
            // Untuk pesan interaktif, potong kompas langsung instan + 5 detik sebagai buffer urutan antrean
            $baseTime    = now();
            $final_delay = $baseTime->copy()->addSeconds(rand(10, 50));
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
                    $scheduled = $lastEvent->scheduled_at ? Carbon::parse($lastEvent->scheduled_at) : null;
                    $sent      = $lastEvent->sent_at ? Carbon::parse($lastEvent->sent_at) : null;

                    // Skenario A: Jika kedua kolom terisi
                    if ($scheduled && $sent) {
                        $baseTime = $scheduled->greaterThan($sent) ? $scheduled : $sent;
                        $source   = $scheduled->greaterThan($sent) ? 'message_events_scheduled' : 'message_events_sent';
                    }
                    // Skenario B: Jika hanya scheduled_at saja yang terisi (Pesan masih antre)
                    elseif ($scheduled) {
                        $baseTime = $scheduled;
                        $source   = 'message_events_only_scheduled';
                    }
                    // Skenario C: Jika hanya sent_at saja yang terisi
                    elseif ($sent) {
                        $baseTime = $sent;
                        $source   = 'message_events_only_sent';
                    }
                }
            }

            // ===============================================================
            // PENJAGAAN STALE DATA (KOREKSI WAKTU LAMPAU)
            // ===============================================================
            if (!$baseTime || $baseTime->isPast()) {
                $baseTime = now();
                $source   = ($baseTime === null) ? 'fallback_now' : $source . '_adjusted_to_now';
            }

            // ===============================================================
            // 3. KALKULASI RENTANG ANTREAN NORMAL
            // ===============================================================
            $safetyInterval = rand(60, 270); // Jeda pengiriman acak
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
                'last_job_available_at'         => $lastJob ? $lastJob->available_at : null,
                'last_event_scheduled_at'       => $lastEvent ? $lastEvent->scheduled_at : null,
            ],
            200,
            "[MessageDispatcher] Dynamic relative delay calculated (" . ($source === 'interactive_bypass' ? 'BYPASS' : 'QUEUED') . ")",
            $traceId,
            round(microtime(true) - $startTime, 4)
        );

        return $final_delay;
    }
}
