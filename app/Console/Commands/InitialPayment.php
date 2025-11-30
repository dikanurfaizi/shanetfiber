<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\Billing\BillingPeriode;
use App\Helpers\LogHelper;
use App\Enums\WhatsAppAction;
use App\Events\MonthlyKickoffBroadcastTriggered;

class InitialPayment extends Command
{
    protected $signature   = 'initial-payment:messages-send';
    protected $description = 'Send WhatsApp messages on 1-3 each month between 08:00–21:59 WIB for initial payment reminders';

    public function handle(): int
    {
        $traceId    = (string) Str::uuid();
        $startTime  = microtime(true);
        $now        = Carbon::now('Asia/Jakarta');

        $allowedDays   = [1, 2, 3];
        $allowedHours  = range(8, 21); // 08:00–21:59
        $allowTestMode = config('messaging.initial_payment_allow_test', false);

        $this->line("──────────────────────────────────────────────");
        $this->info("📨 Initial Payment Job Started");
        $this->line("• Date/Time : {$now->toDateTimeString()} WIB");
        $this->line("• Trace ID  : {$traceId}");
        $this->line("• Test Mode : " . ($allowTestMode ? 'ENABLED' : 'DISABLED'));
        $this->line("──────────────────────────────────────────────");

        LogHelper::logInfo(
            'InitialPayment',
            [
                'trace_id'        => $traceId,
                'timestamp'       => $now->toDateTimeString(),
                'step'            => 'start_command',
                'allow_test_mode' => $allowTestMode,
            ],
            200,
            'Initial payment command started',
            $traceId
        );

        try {
            // ===============================================================
            // STEP 1 — VALIDASI TANGGAL & JAM (SKIP JIKA TEST MODE)
            // ===============================================================
            if (! $allowTestMode) {
                // ---- Validasi tanggal ----
                if (! in_array($now->day, $allowedDays, true)) {
                    $this->warn("⏩ Skipped: today ({$now->day}) not in allowed days [1,2,3].");

                    LogHelper::logInfo(
                        'InitialPayment',
                        [
                            'trace_id'     => $traceId,
                            'step'         => 'validate_date',
                            'day'          => $now->day,
                            'allowed_days' => $allowedDays,
                        ],
                        200,
                        'Skipped — not an allowed date',
                        $traceId
                    );

                    return Command::SUCCESS;
                }

                // ---- Validasi jam ----
                if (! in_array($now->hour, $allowedHours, true)) {
                    $this->warn("⏩ Skipped: current hour {$now->hour}:00 outside range 08–21 WIB.");

                    LogHelper::logInfo(
                        'InitialPayment',
                        [
                            'trace_id'      => $traceId,
                            'step'          => 'validate_hour',
                            'hour'          => $now->hour,
                            'allowed_hours' => $allowedHours,
                        ],
                        200,
                        'Skipped — outside allowed hour range',
                        $traceId
                    );

                    return Command::SUCCESS;
                }
            } else {
                $this->warn('🧪 TEST MODE ENABLED — skipping date/hour validation.');

                LogHelper::logInfo(
                    'InitialPayment',
                    [
                        'trace_id' => $traceId,
                        'step'     => 'validation_skipped_test_mode',
                    ],
                    200,
                    'Initial payment date/hour validation skipped (test mode)',
                    $traceId
                );
            }

            // ===============================================================
            // STEP 2 — CEK PERIODE BILLING
            // ===============================================================
            $currentPeriod = $now->format('F Y');
            $billing       = BillingPeriode::where('name', $currentPeriod)->first();

            if (! $billing) {
                $this->error("No billing period found for {$currentPeriod} — job aborted.");

                LogHelper::logError(
                    'InitialPayment',
                    [
                        'trace_id' => $traceId,
                        'step'     => 'check_billing_period',
                        'period'   => $currentPeriod,
                    ],
                    404,
                    'Billing period not found',
                    $traceId
                );

                return Command::FAILURE;
            }

            LogHelper::logInfo(
                'InitialPayment',
                [
                    'trace_id' => $traceId,
                    'step'     => 'billing_period_found',
                    'period'   => $currentPeriod,
                    'billing_id' => $billing->id,
                ],
                200,
                'Billing period found for initial payment',
                $traceId
            );

            // ===============================================================
            // STEP 3 — HITUNG RANGE 1–3
            // ===============================================================
            [$startDate, $endDate] = $this->getAllowedDateRange($allowedDays, $now);

            LogHelper::logInfo(
                'InitialPayment',
                [
                    'trace_id'   => $traceId,
                    'step'       => 'calculate_date_range',
                    'start_date' => $startDate->toDateTimeString(),
                    'end_date'   => $endDate->toDateTimeString(),
                ],
                200,
                'Calculated allowed date range for broadcast',
                $traceId
            );

            // ===============================================================
            // STEP 4 — DISPATCH EVENT
            // ===============================================================
            $this->info('📤 Dispatching MonthlyKickoffBroadcastTriggered event...');

            LogHelper::logInfo(
                'InitialPayment',
                [
                    'trace_id' => $traceId,
                    'step'     => 'dispatch_event',
                ],
                200,
                'Dispatching MonthlyKickoffBroadcastTriggered event',
                $traceId
            );

            event(new MonthlyKickoffBroadcastTriggered(
                $startDate,
                $endDate,
                WhatsAppAction::CREATE_NEW_BILL->value,
                $traceId
            ));

            // ===============================================================
            // STEP 5 — FINISH
            // ===============================================================
            $execTime = round(microtime(true) - $startTime, 3);

            $this->info("✅ WhatsApp broadcast dispatched successfully in {$execTime}s");
            $this->line("──────────────────────────────────────────────");

            LogHelper::logInfo(
                'InitialPayment',
                [
                    'trace_id'        => $traceId,
                    'step'            => 'finish',
                    'period'          => $currentPeriod,
                    'execution_time'  => $execTime,
                    'start_range'     => $startDate->toDateTimeString(),
                    'end_range'       => $endDate->toDateTimeString(),
                    'allow_test_mode' => $allowTestMode,
                ],
                200,
                'Initial payment broadcast dispatched',
                $traceId
            );

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $execTime = round(microtime(true) - $startTime, 3);

            $this->error("❌ Initial payment job failed: {$e->getMessage()}");
            $this->line("• Exec Time: {$execTime}s");
            $this->line("──────────────────────────────────────────────");

            LogHelper::logError(
                'InitialPayment',
                [
                    'trace_id'       => $traceId,
                    'step'           => 'exception',
                    'exception'      => get_class($e),
                    'error_message'  => $e->getMessage(),
                    'file'           => $e->getFile(),
                    'line'           => $e->getLine(),
                    'execution_time' => $execTime,
                    'trace'          => $e->getTraceAsString(),
                ],
                500,
                'Initial payment job failed',
                $traceId
            );

            return Command::FAILURE;
        }
    }

    private function getAllowedDateRange(array $allowedDays, Carbon $base): array
    {
        $month = $base->copy()->startOfMonth();
        $start = $month->copy()->day(min($allowedDays))->startOfDay();
        // end: hari max + 1 di pukul 00:00 (range [1,3] → [1, 4))
        $end   = $month->copy()->day(max($allowedDays))->addDay()->startOfDay();

        return [$start, $end];
    }
}
