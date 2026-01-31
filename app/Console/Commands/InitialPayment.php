<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\Billings\BillingPeriode;
use App\Helpers\LogHelper;
use App\Enums\WhatsAppAction;
use App\Events\MonthlyKickoffBroadcastTriggered;
use App\Models\Whatsapp\WhatsappFeature;

class InitialPayment extends Command
{
    protected $signature   = 'initial-payment:messages-send';
    protected $description = 'Send WhatsApp initial payment broadcast based on DB schedule';

    public function handle(): int
    {
        $traceId   = (string) Str::uuid();
        $startedAt = microtime(true);
        $testMode  = config('messaging.initial_payment_allow_test', false);

        // ===============================================================
        // LOAD FEATURE
        // ===============================================================
        $feature = WhatsappFeature::where('alias', 'broadcast_awal_bulan')->first();

        if (!$feature || !$feature->is_active || $feature->type !== 'cloud' || !$feature->is_scheduled) {
            $this->warn('⏩ Initial payment broadcast skipped (feature disabled / invalid).');
            return Command::SUCCESS;
        }

        if (empty($feature->schedule)) {
            $this->warn('⛔ Schedule config empty. Job skipped.');
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

        $this->info('📨 Initial Payment Job');
        $this->line("• Time     : {$now->toDateTimeString()} ({$timezone})");
        $this->line("• Trace ID : {$traceId}");
        $this->line("• TestMode : " . ($testMode ? 'ON' : 'OFF'));

        LogHelper::logInfo(
            'InitialPayment',
            [
                'feature_id' => $feature->id,
                'timestamp' => $now->toDateTimeString(),
                'test_mode' => $testMode,
            ],
            200,
            'Initial payment scheduler started',
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
            // BILLING PERIOD
            // ===============================================================
            $periodName = $now->format('F Y');
            $billing = BillingPeriode::where('name', $periodName)->first();

            if (!$billing) {
                throw new \RuntimeException("Billing period '{$periodName}' not found.");
            }

            // ===============================================================
            // DATE RANGE & DISPATCH
            // ===============================================================
            [$startDate, $endDate] = $this->getAllowedDateRange($effectiveDays, $now);

            event(new MonthlyKickoffBroadcastTriggered(
                $startDate,
                $endDate,
                WhatsAppAction::CREATE_NEW_BILL->value,
                $feature->type,
                $traceId
            ));

            $duration = round(microtime(true) - $startedAt, 3);

            $this->info("✅ Broadcast dispatched ({$duration}s)");

            LogHelper::logInfo(
                'InitialPayment',
                [
                    'trace_id' => $traceId,
                    'billing_id' => $billing->id,
                    'range' => [
                        'start' => $startDate->toDateTimeString(),
                        'end' => $endDate->toDateTimeString(),
                    ],
                    'execution_time' => $duration,
                ],
                200,
                'Initial payment broadcast dispatched',
                $traceId
            );

            return Command::SUCCESS;
        } catch (\Throwable $e) {

            $this->error('❌ Initial payment job failed.');

            LogHelper::logError(
                'InitialPayment',
                [
                    'trace_id' => $traceId,
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
                500,
                'Initial payment scheduler failed',
                $traceId
            );

            return Command::FAILURE;
        }
    }

    private function getAllowedDateRange(array $days, Carbon $base): array
    {
        $month = $base->copy()->startOfMonth();

        $start = $month->copy()->day(min($days))->startOfDay();
        $end   = $month->copy()->day(max($days))->addDay()->startOfDay();

        return [$start, $end];
    }
}
