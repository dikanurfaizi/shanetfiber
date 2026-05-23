<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Helpers\LogHelper;
use App\Events\BroadcastMidMonthTriggered;
use App\Models\Whatsapp\WhatsappFeature;

class SendMessages extends Command
{
    protected $signature   = 'reminder:messages-send';
    protected $description = 'Send WhatsApp mid-month broadcast based on DB schedule';

    public function handle(): int
    {
        $traceId   = (string) Str::uuid();
        $startedAt = microtime(true);
        $testMode  = config('messaging.reminder_allow_test', false);

        // ===============================================================
        // LOAD FEATURE
        // ===============================================================
        $feature = WhatsappFeature::where('alias', 'broadcast_pertengahan_bulan')->first();

        if (
            !$feature ||
            !$feature->is_active ||
            $feature->type !== 'cloud' ||
            !$feature->is_scheduled ||
            empty($feature->schedule)
        ) {
            $this->warn('⏩ Mid-month broadcast skipped (feature disabled / invalid).');
            return Command::SUCCESS;
        }

        // ===============================================================
        // PREPARE SCHEDULE
        // ===============================================================
        $schedule  = $feature->schedule;
        $timezone  = data_get($schedule, 'timezone', 'Asia/Jakarta');
        $days      = data_get($schedule, 'days', []);
        $timeStart = data_get($schedule, 'time_window.start', '08:00');
        $timeEnd   = data_get($schedule, 'time_window.end', '21:00');

        if (empty($days)) {
            $this->warn('⛔ No scheduled days configured.');
            return Command::SUCCESS;
        }

        $now = Carbon::now($timezone);

        $this->info('📨 Mid-Month Reminder Job');
        $this->line("• Time     : {$now->toDateTimeString()} ({$timezone})");
        $this->line("• Trace ID : {$traceId}");
        $this->line("• TestMode : " . ($testMode ? 'ON' : 'OFF'));

        LogHelper::logInfo(
            'BroadcastMidMonth',
            [
                'trace_id'   => $traceId,
                'feature_id' => $feature->id,
                'timestamp'  => $now->toDateTimeString(),
                'test_mode'  => $testMode,
            ],
            200,
            'Mid-month broadcast scheduler started',
            $traceId
        );

        try {
            // ===============================================================
            // DATE & TIME VALIDATION (BEST PRACTICE)
            // ===============================================================
            $lastDay = $now->copy()->endOfMonth()->day;
            $effectiveDays = array_map(fn($d) => min($d, $lastDay), $days);

            if (!$testMode) {
                // 1. Validasi Hari/Tanggal
                if (!in_array($now->day, $effectiveDays, true)) {
                    $this->warn('⏩ Skipped: today not in scheduled days.');
                    return Command::SUCCESS;
                }

                // 2. Validasi Jendela Waktu (Menggunakan Carbon antara start dan end)
                // Contoh: Apakah jam sekarang berada di antara 08:00:00 dan 21:00:00?
                $isInsideWindow = $now->between(
                    $now->copy()->setTimeFromTimeString($timeStart),
                    $now->copy()->setTimeFromTimeString($timeEnd)
                );

                if (!$isInsideWindow) {
                    $this->warn("⏩ Skipped: outside time window ({$timeStart} - {$timeEnd}).");
                    return Command::SUCCESS;
                }
            }

            // ===============================================================
            // DISPATCH EVENT
            // ===============================================================
            event(new BroadcastMidMonthTriggered($traceId));

            $duration = round(microtime(true) - $startedAt, 3);

            $this->info("✅ Mid-month broadcast dispatched ({$duration}s)");

            LogHelper::logInfo(
                'BroadcastMidMonth',
                [
                    'trace_id' => $traceId,
                    'range' => [
                        'days' => $effectiveDays,
                        'time_window' => "{$timeStart}-{$timeEnd}",
                    ],
                    'execution_time' => $duration,
                ],
                200,
                'Mid-month broadcast dispatched',
                $traceId
            );

            return Command::SUCCESS;
        } catch (\Throwable $e) {

            $this->error('❌ Mid-month broadcast job failed.');

            LogHelper::logError(
                'BroadcastMidMonth',
                [
                    'trace_id' => $traceId,
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
                500,
                'Mid-month broadcast scheduler failed',
                $traceId
            );

            return Command::FAILURE;
        }
    }
}
