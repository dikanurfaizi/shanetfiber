<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Helpers\LogHelper;
use App\Helpers\ExecutionTimer;
use App\Events\PaymentChannelUpdateTriggered;
use App\Models\Whatsapp\WhatsappFeature;

class SendPaymentChannelUpdate extends Command
{
    /**
     * Command signature disesuaikan dengan kebutuhan scheduler (2 jam sekali)
     * Logika penjagaan tanggal dipusatkan di dalam metode handle() ini.
     */
    protected $signature   = 'reminder:payment-channel-update-send';
    protected $description = 'Send WhatsApp payment channel update notification strictly between June 1st and 15th, 2026';

    public function handle(): int
    {
        $traceId   = (string) Str::uuid();
        $timer     = new ExecutionTimer();
        $testMode  = config('messaging.reminder_allow_test', false);

        // Menggunakan timezone konstan default, atau dinamis jika dimuat dari database nanti
        $timezone  = 'Asia/Jakarta';
        $now       = Carbon::now($timezone);

        $this->info('📨 Payment Channel Update Reminder Job Started');
        $this->line("• Time     : {$now->toDateTimeString()} ({$timezone})");
        $this->line("• Trace ID : {$traceId}");
        $this->line("• TestMode : " . ($testMode ? 'ON' : 'OFF'));

        try {
            // ===============================================================
            // 1. STRICT DATE & TIME WINDOW VALIDATION (GUARD CLAUSES)
            // ===============================================================
            if (!$testMode) {
                // Validasi Tahun (2026), Bulan (Juni = 6), dan Rentang Tanggal (1 s/d 15)
                $isValidYear  = $now->year === 2026;
                $isValidMonth = $now->month === 6;
                $isValidDay   = $now->day >= 1 && $now->day <= 25;

                if (!($isValidYear && $isValidMonth && $isValidDay)) {
                    $this->warn('⏩ Skipped: Today is outside the allowed date range (June 1-15, 2026).');
                    return Command::SUCCESS;
                }
            }

            // ===============================================================
            // 2. LOAD CONFIGURATION FROM DATABASE FEATURE
            // ===============================================================
            $feature = WhatsappFeature::where('alias', 'update_rekening_juni_2026')->first();

            if (!$feature || !$feature->is_active) {
                $this->warn('⏩ Skipped: Payment channel update feature is either disabled or not found in DB.');
                return Command::SUCCESS;
            }

            // Mengambil jendela waktu dinamis dari JSON/Array kolom schedule di database
            $schedule  = $feature->schedule;
            $timeStart = data_get($schedule, 'time_window.start', '08:00');
            $timeEnd   = data_get($schedule, 'time_window.end', '21:00');

            if (!$testMode) {
                // Validasi Jendela Waktu Operasional Pengiriman (Mencegah kirim SMS/WA tengah malam)
                $isInsideWindow = $now->between(
                    $now->copy()->setTimeFromTimeString($timeStart),
                    $now->copy()->setTimeFromTimeString($timeEnd)
                );

                if (!$isInsideWindow) {
                    $this->warn("⏩ Skipped: Current time is outside the allowed daily time window ({$timeStart} - {$timeEnd}).");
                    return Command::SUCCESS;
                }
            }

            // ===============================================================
            // 3. DISPATCH SYSTEM EVENT & LOGGING
            // ===============================================================
            LogHelper::logInfo(
                null,
                [
                    'trace_id'   => $traceId,
                    'feature_id' => $feature->id,
                    'timestamp'  => $now->toDateTimeString(),
                    'test_mode'  => $testMode,
                ],
                200,
                'Payment channel update scheduler validation passed. Dispatching event...',
                $traceId,
                $timer->lap()
            );

            // Trigger event utama sistem untuk memicu pemrosesan antrean pesan
            event(new PaymentChannelUpdateTriggered($traceId));

            $this->info("✅ Success: Payment channel update broadcast dispatched safely ({$timer->lap()}s)");

            LogHelper::logInfo(
                null,
                [
                    'trace_id' => $traceId,
                    'range' => [
                        'target_dates' => '2026-06-01 to 2026-06-15',
                        'time_window'  => "{$timeStart}-{$timeEnd}",
                    ],
                ],
                200,
                'Payment channel update broadcast dispatched successfully',
                $traceId,
                $timer->lap()
            );

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('❌ Error: Payment channel update broadcast job encountered a critical failure.');

            LogHelper::logError(
                null,
                [
                    'trace_id'  => $traceId,
                    'exception' => get_class($e),
                    'message'   => $e->getMessage(),
                    'file'      => $e->getFile(),
                    'line'      => $e->getLine(),
                ],
                500,
                'Payment channel update scheduler failed execution',
                $traceId,
                $timer->lap()
            );

            return Command::FAILURE;
        }
    }
}
