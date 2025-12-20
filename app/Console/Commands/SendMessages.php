<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Helpers\LogHelper;
use App\Events\BroadcastMidMonthTriggered;

class SendMessages extends Command
{
    protected $signature   = 'reminder:messages-send';
    protected $description = 'Send WhatsApp reminder messages on the 10th and 15th between 08:00-21:59 WIB every 15 minutes';

    public function handle(): int
    {
        $traceId   = (string) Str::uuid();
        $startTime = microtime(true);
        $now       = Carbon::now('Asia/Jakarta');

        $allowedDays  = [10, 12, 15];
        $allowedHours = range(8, 21);
        $allowTestMode = config('messaging.reminder_allow_test');

        $this->line("──────────────────────────────────────────────");
        $this->info("📨 Starting ReminderMessageJob");
        $this->line("• Date/Time : {$now->toDateTimeString()} WIB");
        $this->line("• Trace ID  : {$traceId}");
        $this->line("• Test Mode : " . ($allowTestMode ? 'ENABLED' : 'DISABLED'));
        $this->line("──────────────────────────────────────────────");

        LogHelper::logInfo(
            'SendMessages',
            [
                'trace_id'  => $traceId,
                'timestamp' => $now->toDateTimeString(),
                'allow_test_mode' => $allowTestMode,
                'step'      => 'start_command'
            ],
            200,
            'Reminder message command started',
            $traceId
        );

        try {
            // ===============================================================
            // STEP 1 — VALIDASI (SKIP SAAT TEST MODE)
            // ===============================================================
            if ($allowTestMode) {
                $this->info("🧪 TEST MODE ENABLED — All date/hour validation skipped");

                LogHelper::logInfo(
                    'SendMessages',
                    [
                        'trace_id' => $traceId,
                        'step' => 'validation_skipped',
                        'reason' => 'test_mode_enabled'
                    ],
                    200,
                    'Reminder validation skipped (test mode enabled)',
                    $traceId
                );
            } else {
                // ---------------- VALIDATE DATE ----------------
                if (!in_array($now->day, $allowedDays)) {
                    $this->warn("⏩ SKIPPED — Today is {$now->day}, allowed only on 10 and 15.");

                    LogHelper::logInfo(
                        'SendMessages',
                        [
                            'trace_id' => $traceId,
                            'step' => 'validate_date',
                            'day' => $now->day,
                            'allowed_days' => $allowedDays
                        ],
                        200,
                        'Skipped — not allowed date',
                        $traceId
                    );

                    return Command::SUCCESS;
                }

                // ---------------- VALIDATE HOUR ----------------
                if (!in_array($now->hour, $allowedHours)) {
                    $this->warn("⏩ SKIPPED — Current hour {$now->hour}:00 is outside allowed range (08–21 WIB).");

                    LogHelper::logInfo(
                        'SendMessages',
                        [
                            'trace_id' => $traceId,
                            'step' => 'validate_hour',
                            'hour' => $now->hour,
                            'allowed_hours' => $allowedHours
                        ],
                        200,
                        'Skipped — outside allowed hour range',
                        $traceId
                    );

                    return Command::SUCCESS;
                }
            }

            // ===============================================================
            // STEP 2 — DISPATCH EVENT
            // ===============================================================
            $this->info("📤 Dispatching BroadcastMidMonthTriggered event...");

            LogHelper::logInfo(
                'SendMessages',
                [
                    'trace_id' => $traceId,
                    'step' => 'dispatch_event'
                ],
                200,
                'Dispatching BroadcastMidMonthTriggered event',
                $traceId
            );

            event(new BroadcastMidMonthTriggered($traceId));

            // ===============================================================
            // STEP 3 — FINISH
            // ===============================================================
            $executionTime = round(microtime(true) - $startTime, 3);
            $dispatchTime  = $now->format('Y-m-d H:i:s');

            $this->info("✅ Reminder messages dispatched successfully");
            $this->line("• Dispatch Time : {$dispatchTime}");
            $this->line("• Exec Time     : {$executionTime}s");
            $this->line("──────────────────────────────────────────────");

            LogHelper::logInfo(
                'SendMessages',
                [
                    'trace_id' => $traceId,
                    'step' => 'finish',
                    'dispatch_time' => $dispatchTime,
                    'execution_time_seconds' => $executionTime,
                    'test_mode' => $allowTestMode
                ],
                200,
                'Reminder message job dispatched',
                $traceId
            );

            return Command::SUCCESS;
        } catch (\Throwable $e) {

            $executionTime = round(microtime(true) - $startTime, 3);

            $this->error("❌ Reminder message job failed: {$e->getMessage()}");
            $this->line("• Exec Time: {$executionTime}s");
            $this->line("──────────────────────────────────────────────");

            LogHelper::logError(
                'SendMessages',
                [
                    'trace_id' => $traceId,
                    'step' => 'exception',
                    'exception_class' => get_class($e),
                    'error_message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'execution_time_seconds' => $executionTime,
                ],
                500,
                'Reminder message job failed',
                $traceId
            );

            return Command::FAILURE;
        }
    }
}
