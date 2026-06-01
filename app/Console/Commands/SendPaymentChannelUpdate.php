<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Helpers\LogHelper;
use App\Events\PaymentChannelUpdateTriggered;
use App\Models\Whatsapp\WhatsappFeature;

class SendPaymentChannelUpdate extends Command
{
    // Command signature disesuaikan dengan konteks fitur
    protected $signature   = 'reminder:payment-channel-update-send';
    protected $description = 'Send WhatsApp payment channel update notification on June 1st & 2nd, 2026';

    public function handle(): int
    {
        $traceId   = (string) Str::uuid();
        $startedAt = microtime(true);
        $testMode  = config('messaging.reminder_allow_test', false);

        // ===============================================================
        // LOAD FEATURE
        // ===============================================================
        $feature = WhatsappFeature::where('alias', 'update_rekening_juni_2026')->first();

        if (
            !$feature ||
            !$feature->is_active ||
            $feature->type !== 'cloud' ||
            empty($feature->schedule)
        ) {
            $this->warn('⏩ Payment channel update broadcast skipped (feature disabled / invalid).');
            return Command::SUCCESS;
        }

        // ===============================================================
        // PREPARE SCHEDULE
        // ===============================================================
        $schedule  = $feature->schedule;
        $timezone  = data_get($schedule, 'timezone', 'Asia/Jakarta');
        $timeStart = data_get($schedule, 'time_window.start', '08:00');
        $timeEnd   = data_get($schedule, 'time_window.end', '21:00');

        $now = Carbon::now($timezone);

        $this->info('📨 Payment Channel Update Reminder Job');
        $this->line("• Time     : {$now->toDateTimeString()} ({$timezone})");
        $this->line("• Trace ID : {$traceId}");
        $this->line("• TestMode : " . ($testMode ? 'ON' : 'OFF'));

        LogHelper::logInfo(
            'PaymentChannelUpdate',
            [
                'trace_id'   => $traceId,
                'feature_id' => $feature->id,
                'timestamp'  => $now->toDateTimeString(),
                'test_mode'  => $testMode,
            ],
            200,
            'Payment channel update scheduler started',
            $traceId
        );

        try {
            // ===============================================================
            // EXACT DATE & TIME WINDOW VALIDATION (STRICT 1 & 2 JUNE 2026)
            // ===============================================================
            if (!$testMode) {
                // 1. Validasi Tahun, Bulan (Juni), dan Tanggal (1 atau 2)
                $allowedDays = [1];
                if ($now->year !== 2026 || $now->month !== 6 || !in_array($now->day, $allowedDays, true)) {
                    $this->warn('⏩ Skipped: Today is not June 1st or 2nd, 2026.');
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
            event(new PaymentChannelUpdateTriggered($traceId));

            $duration = round(microtime(true) - $startedAt, 3);

            $this->info("✅ Payment channel update broadcast dispatched ({$duration}s)");

            LogHelper::logInfo(
                'PaymentChannelUpdate',
                [
                    'trace_id' => $traceId,
                    'range' => [
                        'target_dates' => ['2026-06-01', '2026-06-02'],
                        'time_window'  => "{$timeStart}-{$timeEnd}",
                    ],
                    'execution_time' => $duration,
                ],
                200,
                'Payment channel update broadcast dispatched',
                $traceId
            );

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('❌ Payment channel update broadcast job failed.');

            LogHelper::logError(
                'PaymentChannelUpdate',
                [
                    'trace_id' => $traceId,
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
                500,
                'Payment channel update scheduler failed',
                $traceId
            );

            return Command::FAILURE;
        }
    }
}
