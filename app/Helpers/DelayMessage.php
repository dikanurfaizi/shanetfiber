<?php

namespace App\Helpers;

use App\Models\WhatsApp\WhatsAppMessage;
use Illuminate\Support\Carbon;

class DelayMessage
{
    /**
     * Menyimpan titik waktu antrean terakhir di memori selama request/runtime berjalan.
     */
    private static ?Carbon $lastScheduledTime = null;

    /**
     * Mengkalkulasi delay dinamis berbasis memori tanpa query antrean ke database.
     * Mengembalikan instance Carbon untuk langsung dikirim ke method ->delay().
     */
    public function calculation(string $action, string $traceId, int $inputDelaySeconds = 0): Carbon
    {
        $source         = null;
        $baseTime       = null;
        $safetyInterval = 0;
        $timer          = new ExecutionTimer();

        // ===============================================================
        // 0. GLOBAL DELAY BYPASS VIA .ENV
        // ===============================================================
        if (filter_var(env('DELAY_MESSAGE_BYPASS', false), FILTER_VALIDATE_BOOLEAN)) {
            $baseTime          = now();
            $finalDelay        = $baseTime->copy();
            $source            = 'env_delay_bypass';
            $safetyInterval    = 0;
            $inputDelaySeconds = 0;
        } else {
            // ===============================================================
            // 1. DYNAMIC CHECK: INTERACTIVE BYPASS
            // ===============================================================
            static $interactiveCache = [];

            if (!isset($interactiveCache[$action])) {
                $interactiveCache[$action] = WhatsAppMessage::where('category', 'INTERACTIVE')
                    ->where('slug', $action)
                    ->exists();
            }

            if ($interactiveCache[$action]) {
                $baseTime   = now();
                $finalDelay = $baseTime->copy()->addSeconds(rand(10, 50));
                $source     = 'interactive_bypass';
            } else {
                // ===============================================================
                // 2. MEMORY-BASED QUEUE TRACKING
                // ===============================================================
                if (self::$lastScheduledTime && self::$lastScheduledTime->isFuture()) {
                    $baseTime = self::$lastScheduledTime->copy();
                    $source   = 'in_memory_queue';
                } else {
                    $baseTime = now();
                    $source   = 'initial_now';
                }

                // ===============================================================
                // 3. KALKULASI DELAY DENGAN JEDA ACAK
                // ===============================================================
                $safetyInterval = rand(60, 270);
                $finalDelay     = $baseTime->copy()
                    ->addSeconds($inputDelaySeconds)
                    ->addSeconds($safetyInterval);

                // Update state titik antrean terakhir untuk iterasi berikutnya
                self::$lastScheduledTime = $finalDelay->copy();
            }
        }

        // ===============================================================
        // 4. LOGGING
        // ===============================================================
        LogHelper::logInfo(
            null,
            [
                'action'                        => $action,
                'source'                        => $source,
                'base_time'                     => $baseTime->toDateTimeString(),
                'input_requested_delay_seconds' => $inputDelaySeconds,
                'safety_interval_seconds'       => $safetyInterval,
                'final_delay'                   => $finalDelay->toDateTimeString(),
            ],
            200,
            "[MessageDispatcher] Memory-based delay calculated (" . ($source === 'env_delay_bypass' ? 'GLOBAL_BYPASS' : ($source === 'interactive_bypass' ? 'BYPASS' : 'QUEUED')) . ")",
            $traceId,
            $timer->lap()
        );

        return $finalDelay;
    }

    /**
     * Opsional: Reset state antrean jika diperlukan (misal pada unit testing/long-running workers).
     */
    public static function resetQueue(): void
    {
        self::$lastScheduledTime = null;
    }
}
