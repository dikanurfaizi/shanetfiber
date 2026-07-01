<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\Billings\BillingPeriode;
use App\Helpers\LogHelper;
use App\Enums\WhatsAppAction;
use App\Events\MonthlyKickoffBroadcastTriggered;
use App\Helpers\ExecutionTimer;
use App\Models\Whatsapp\WhatsappFeature;

class InitialPayment extends Command
{
    protected $signature   = 'initial-payment:messages-send';
    protected $description = 'Send WhatsApp initial payment broadcast based on DB schedule';

    public function handle(): int
    {
        $traceId   = (string) Str::uuid();
        $timer = new ExecutionTimer();
        $testMode  = config('messaging.initial_payment_allow_test', false);

        // ===============================================================
        // LOAD FEATURE
        // ===============================================================
        $feature = WhatsappFeature::where('alias', 'broadcast_awal_bulan')->first();

        if (!$feature || !$feature->is_active || $feature->type !== 'cloud' || !$feature->is_scheduled) {
            $this->warn('⏩ Initial payment broadcast skipped (feature disabled / invalid).');

            LogHelper::logInfo(
                'InitialPayment',
                [
                    'feature_exists' => (bool)$feature,
                    'is_active' => $feature?->is_active,
                    'type' => $feature?->type,
                    'is_scheduled' => $feature?->is_scheduled,
                ],
                200,
                'Initial payment broadcast skipped: feature is disabled, not cloud, or not scheduled.',
                $traceId,
                $timer->lap()
            );

            return Command::SUCCESS;
        }

        if (empty($feature->schedule)) {
            $this->warn('⛔ Schedule config empty. Job skipped.');

            LogHelper::logInfo(
                'InitialPayment',
                ['feature_id' => $feature->id],
                200,
                'Initial payment broadcast skipped: schedule configuration is empty.',
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
        $days      = data_get($schedule, 'days', []);
        $timeStart = data_get($schedule, 'time_window.start', '08:00');
        $timeEnd   = data_get($schedule, 'time_window.end', '21:00');

        if (empty($days)) {
            $this->warn('⛔ No scheduled days configured.');

            LogHelper::logInfo(
                'InitialPayment',
                ['feature_id' => $feature->id, 'schedule' => $schedule],
                200,
                'Initial payment broadcast skipped: no scheduled days found in config.',
                $traceId,
                $timer->lap()
            );

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
                'schedule_config' => [
                    'days' => $days,
                    'time_start' => $timeStart,
                    'time_end' => $timeEnd,
                    'timezone' => $timezone
                ]
            ],
            200,
            'Initial payment scheduler started',
            $traceId,
            $timer->lap()
        );

        try {
            // ===============================================================
            // DATE & TIME VALIDATION
            // ===============================================================
            $lastDay = $now->copy()->endOfMonth()->day;
            $effectiveDays = array_map(fn($d) => (int) min($d, $lastDay), $days);

            if (!$testMode) {
                if (!in_array($now->day, $effectiveDays, true)) {
                    $this->warn('⏩ Skipped: today not in scheduled days.');

                    LogHelper::logInfo(
                        'InitialPayment',
                        [
                            'current_day' => $now->day,
                            'effective_days' => $effectiveDays,
                        ],
                        200,
                        'Initial payment broadcast skipped: today is not within the scheduled days.',
                        $traceId,
                        $timer->lap()
                    );

                    return Command::SUCCESS;
                }

                $currentTime = $now->format('H:i');
                if ($currentTime < $timeStart || $currentTime > $timeEnd) {
                    $this->warn('⏩ Skipped: outside time window.');

                    LogHelper::logInfo(
                        'InitialPayment',
                        [
                            'current_time' => $currentTime,
                            'time_window' => ['start' => $timeStart, 'end' => $timeEnd],
                        ],
                        200,
                        'Initial payment broadcast skipped: current time is outside the allowed time window.',
                        $traceId,
                        $timer->lap()
                    );

                    return Command::SUCCESS;
                }
            }

            // ===============================================================
            // BILLING PERIOD
            // ===============================================================
            $periodName = $now->format('F Y');
            $billing = BillingPeriode::where('name', $periodName)->first();

            if (!$billing) {
                LogHelper::logError(
                    'InitialPayment', // Disamakan channel-nya agar tidak null
                    [
                        'period_name' => $periodName,
                    ],
                    404,
                    "Billing period '{$periodName}' not found.",
                    $traceId,
                    $timer->lap()
                );
                throw new \RuntimeException("Billing period '{$periodName}' not found.");
            }

            // ===============================================================
            // DATE RANGE & DISPATCH
            // ===============================================================
            [$startDate, $endDate] = $this->getAllowedDateRange($now);

            event(new MonthlyKickoffBroadcastTriggered(
                WhatsAppAction::CREATE_NEW_BILL->value,
                $feature->type,
                $traceId
            ));

            $duration = $timer->lap();

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
                'Initial payment broadcast dispatched successfully',
                $traceId,
                $duration
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
                $traceId,
                $timer->lap()
            );

            return Command::FAILURE;
        }
    }

    private function getAllowedDateRange(Carbon $base): array
    {
        $start = $base->copy()->startOfMonth();
        $end   = $base->copy()->endOfMonth();

        return [$start, $end];
    }
}
