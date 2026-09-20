<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Helpers\LogHelper;
use App\Helpers\ExecutionTimer;
use App\Events\BroadcastMidMonthTriggered;
use App\Models\Whatsapp\WhatsappFeature;

class SendMessages extends Command
{
    protected $signature   = 'reminder:messages-send';
    protected $description = 'Send WhatsApp mid-month broadcast based on DB schedule';

    public function handle(): int
    {
        $traceId   = (string) Str::uuid();
        $timer = new ExecutionTimer();
        $testMode  = config('messaging.reminder_allow_test', false);

        // ===============================================================
        // LOAD FEATURE
        // ===============================================================
        $feature = WhatsappFeature::where('alias', 'broadcast_pertengahan_bulan')->first();

        if (!$feature || !$feature->is_active) {
            $this->warn('⏩ Mid-month broadcast skipped (feature disabled or not found).');

            LogHelper::logInfo(
                'BroadcastMidMonth',
                [
                    'feature_found' => (bool) $feature,
                    'is_active'     => $feature->is_active ?? false,
                ],
                200,
                'Mid-month broadcast skipped (feature disabled or not found)',
                $traceId,
                $timer->lap()
            );

            return Command::SUCCESS;
        }

        // ===============================================================
        // PREPARE SCHEDULE
        // ===============================================================
        $schedule  = $feature->schedule;
        $timezone  = data_get($schedule, 'timezone', 'Asia/Jakarta');

        // Get days & paksa konversi ke integer
        $rawDays   = data_get($schedule, 'days', []);
        $days      = !empty($rawDays)
            ? array_map('intval', (array) $rawDays)
            : [15, 16, 17, 18, 19, 20];

        $timeStart = data_get($schedule, 'time_window.start', '08:00');
        $timeEnd   = data_get($schedule, 'time_window.end', '20:00');

        $now = Carbon::now($timezone);

        // ===============================================================
        // OUTPUT TERMINAL & APPLICATION LOG
        // ===============================================================
        $this->info('📨 Mid-Month Reminder Job');
        $this->line("• Time         : {$now->toDateTimeString()} ({$timezone})");
        $this->line("• TestMode     : " . ($testMode ? 'ON' : 'OFF'));
        $this->line("• Feature ID   : {$feature->id}");
        $this->line("• Days (Raw DB): " . json_encode($rawDays));
        $this->line("• Days (Parsed): " . json_encode($days));
        $this->line("• Time Window  : {$timeStart} - {$timeEnd}");

        LogHelper::logInfo(
            'BroadcastMidMonth',
            [
                'feature_id'     => $feature->id,
                'timestamp'      => $now->toDateTimeString(),
                'test_mode'      => $testMode,
                'days_raw'       => $rawDays,
                'days_effective' => $days,
                'time_window'    => "{$timeStart}-{$timeEnd}",
            ],
            200,
            'Mid-month broadcast scheduler started',
            $traceId,
            $timer->lap()
        );

        if (empty($days)) {
            $this->warn('⛔ No scheduled days configured.');
            return Command::SUCCESS;
        }

        try {
            // ===============================================================
            // DATE & TIME VALIDATION
            // ===============================================================
            $lastDay = $now->copy()->endOfMonth()->day;
            $effectiveDays = array_map(fn($d) => min((int)$d, $lastDay), $days);

            if (!$testMode) {
                // 1. Validasi Hari/Tanggal
                if (!in_array($now->day, $effectiveDays, true)) {
                    $this->warn("⏩ Skipped: today ({$now->day}) not in scheduled days.");
                    return Command::SUCCESS;
                }

                // 2. Validasi Jendela Waktu
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

            $this->info("✅ Mid-month broadcast dispatched ({$timer->lap()}s)");

            LogHelper::logInfo(
                null,
                [
                    'range' => [
                        'days'        => $effectiveDays,
                        'time_window' => "{$timeStart}-{$timeEnd}",
                    ]
                ],
                200,
                'Mid-month broadcast dispatched',
                $traceId,
                $timer->lap()
            );

            return Command::SUCCESS;
        } catch (\Throwable $e) {

            $this->error('❌ Mid-month broadcast job failed.');

            LogHelper::logError(
                null,
                [
                    'exception' => get_class($e),
                    'message'   => $e->getMessage(),
                    'file'      => $e->getFile(),
                    'line'      => $e->getLine(),
                ],
                500,
                'Mid-month broadcast scheduler failed',
                $traceId,
                $timer->lap()
            );

            return Command::FAILURE;
        }
    }
}
