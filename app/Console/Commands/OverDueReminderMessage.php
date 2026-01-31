<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Helpers\LogHelper;
use App\Events\OverdueBroadcastTriggered;
use App\Models\Whatsapp\WhatsappFeature;

class OverDueReminderMessage extends Command
{
    protected $signature   = 'reminder:overdue-messages-send';
    protected $description = 'Send WhatsApp overdue broadcast based on DB schedule';

    public function handle(): int
    {
        $traceId   = (string) Str::uuid();
        $startedAt = microtime(true);
        $testMode  = config('messaging.reminder_allow_test', false);

        // ===============================================================
        // LOAD FEATURE
        // ===============================================================
        $feature = WhatsappFeature::where('alias', 'broadcast_akhir_bulan')->first();

        if (
            !$feature ||
            !$feature->is_active ||
            $feature->type !== 'cloud' ||
            !$feature->is_scheduled ||
            empty($feature->schedule)
        ) {
            $this->warn('⏩ Overdue broadcast skipped (feature disabled / invalid).');
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

        $this->info('📨 Overdue Reminder Job');
        $this->line("• Time     : {$now->toDateTimeString()} ({$timezone})");
        $this->line("• Trace ID : {$traceId}");
        $this->line("• TestMode : " . ($testMode ? 'ON' : 'OFF'));

        LogHelper::logInfo(
            'BroadcastOverdue',
            [
                'trace_id'   => $traceId,
                'feature_id' => $feature->id,
                'timestamp'  => $now->toDateTimeString(),
                'test_mode'  => $testMode,
            ],
            200,
            'Overdue broadcast scheduler started',
            $traceId
        );

        try {
            // ===============================================================
            // DATE & TIME VALIDATION
            // ===============================================================
            $lastDay = $now->copy()->endOfMonth()->day;
            $effectiveDays = array_map(fn($d) => min($d, $lastDay), $days);

            if (!$testMode) {
                if (!in_array($now->day, $effectiveDays, true)) {
                    $this->warn('⏩ Skipped: today not in scheduled days.');
                    return Command::SUCCESS;
                }

                $currentTime = $now->format('H:i');
                if ($currentTime < $timeStart || $currentTime > $timeEnd) {
                    $this->warn('⏩ Skipped: outside time window.');
                    return Command::SUCCESS;
                }
            }

            // ===============================================================
            // DISPATCH EVENT
            // ===============================================================
            event(new OverdueBroadcastTriggered($traceId));

            $duration = round(microtime(true) - $startedAt, 3);

            $this->info("✅ Overdue broadcast dispatched ({$duration}s)");

            LogHelper::logInfo(
                'BroadcastOverdue',
                [
                    'trace_id' => $traceId,
                    'range' => [
                        'days' => $effectiveDays,
                        'time_window' => "{$timeStart}-{$timeEnd}",
                    ],
                    'execution_time' => $duration,
                ],
                200,
                'Overdue broadcast dispatched',
                $traceId
            );

            return Command::SUCCESS;
        } catch (\Throwable $e) {

            $this->error('❌ Overdue broadcast job failed.');

            LogHelper::logError(
                'BroadcastOverdue',
                [
                    'trace_id' => $traceId,
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
                500,
                'Overdue broadcast scheduler failed',
                $traceId
            );

            return Command::FAILURE;
        }
    }
}
