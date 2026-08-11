<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Helpers\LogHelper;
use App\Events\OverdueBroadcastTriggered;
use App\Helpers\ExecutionTimer;
use App\Models\Whatsapp\WhatsappFeature;

class OverDueReminderMessage extends Command
{
    protected $signature   = 'reminder:overdue-messages-send';
    protected $description = 'Send WhatsApp overdue broadcast based on DB schedule';

    public function handle(): int
    {
        $traceId   = (string) Str::uuid();
        $timer     = new ExecutionTimer();
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
        $rawDays   = data_get($schedule, 'days', []);
        $timeStart = data_get($schedule, 'time_window.start', '08:00');
        $timeEnd   = data_get($schedule, 'time_window.end', '21:00');

        if (empty($rawDays)) {
            $this->warn('⛔ No scheduled days configured.');
            return Command::SUCCESS;
        }

        // Memastikan variabel selalu berupa array
        $daysArray = is_array($rawDays) ? $rawDays : [$rawDays];

        $now = Carbon::now($timezone);

        // Menentukan hari terakhir pada bulan ini (antisipasi bulan pendek seperti Februari)
        $lastDay = $now->copy()->endOfMonth()->day;

        // Transformasi nilai hari ke integer & amankan dengan batas maksimum hari bulan berjalan
        $effectiveDays = array_map(fn($d) => min((int)$d, $lastDay), $daysArray);

        $this->info('📨 Overdue Reminder Job');
        $this->line("• Time     : {$now->toDateTimeString()} ({$timezone})");
        $this->line("• Trace ID : {$traceId}");
        $this->line("• TestMode : " . ($testMode ? 'ON' : 'OFF'));

        LogHelper::logInfo(
            'BroadcastOverdue',
            [
                'trace_id'       => $traceId,
                'feature_id'     => $feature->id,
                'timestamp'      => $now->toDateTimeString(),
                'test_mode'      => $testMode,
                'days_raw'       => $rawDays,
                'days_effective' => $effectiveDays,
                'time_window'    => "{$timeStart}-{$timeEnd}"
            ],
            200,
            'Overdue broadcast scheduler started',
            $traceId,
            $timer->lap()
        );

        try {
            // ===============================================================
            // DATE & TIME VALIDATION
            // ===============================================================
            if (!$testMode) {
                // 1. Validasi Hari/Tanggal
                if (!in_array($now->day, $effectiveDays, true)) {
                    $this->warn("⏩ Skipped: today ({$now->day}) not in scheduled days.");
                    return Command::SUCCESS;
                }

                // 2. Validasi Jendela Waktu (Menggunakan Carbon untuk menghindari bug format string jam)
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
            event(new OverdueBroadcastTriggered($traceId));

            // Simpan durasi ke variabel agar konsisten di konsol dan log
            $duration = $timer->lap();

            $this->info("✅ Overdue broadcast dispatched ({$duration}s)");

            LogHelper::logInfo(
                'BroadcastOverdue',
                [
                    'range' => [
                        'days'        => $effectiveDays,
                        'time_window' => "{$timeStart}-{$timeEnd}",
                    ]
                ],
                200,
                'Overdue broadcast dispatched',
                $traceId,
                $duration
            );

            return Command::SUCCESS;
        } catch (\Throwable $e) {

            $duration = $timer->lap();

            $this->error('❌ Overdue broadcast job failed.');

            LogHelper::logError(
                'BroadcastOverdue',
                [
                    'exception' => get_class($e),
                    'message'   => $e->getMessage(),
                    'file'      => $e->getFile(),
                    'line'      => $e->getLine(),
                ],
                500,
                'Overdue broadcast scheduler failed',
                $traceId,
                $duration
            );

            return Command::FAILURE;
        }
    }
}