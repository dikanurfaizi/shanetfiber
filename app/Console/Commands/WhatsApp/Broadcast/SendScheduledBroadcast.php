<?php

namespace App\Console\Commands\WhatsApp\Broadcast;

use App\Enums\WhatsAppAction;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\WhatsApp\WhatsAppFeature;
use App\Services\Notification\WhatsApp\Broadcast\BroadcastManagerService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SendScheduledBroadcast extends Command
{
    /**
     * Argument langsung di-map ke value WhatsAppAction (misal: initial_broadcast, due_date_broadcast, overdue_broadcast)
     */
    protected $signature = 'whatsApp:broadcast-send {action : Action type slug from WhatsAppAction enum}';
    protected $description = 'Send scheduled WhatsApp broadcast based on DB schedule configuration';

    public function handle(BroadcastManagerService $broadcastManagerService): int
    {
        // 1. Validasi Enum & Pastikan Action Adalah Tipe Broadcast
        $actionInput = (string) $this->argument('action');
        $actionEnum  = WhatsAppAction::tryFrom($actionInput);

        if (!$actionEnum || !$actionEnum->isBroadcast()) {
            $validBroadcasts = implode(', ', array_map(fn($case) => $case->value, WhatsAppAction::broadcastCases()));
            $this->error("❌ Invalid broadcast action: '{$actionInput}'. Valid broadcast options: {$validBroadcasts}");

            return Command::FAILURE;
        }

        $traceId = (string) Str::uuid();
        $timer   = new ExecutionTimer();

        $isTestMode = config('messaging.broadcast_test_mode', true);

        // ===============================================================
        // 2. LOAD FEATURE DIRECTLY BY ACTION VALUE / ALIAS
        // ===============================================================
        $feature = WhatsAppFeature::where('alias', $actionEnum->value)->first();

        if (!$feature || !$feature->is_active || !$feature->is_scheduled || empty($feature->schedule)) {
            $this->warn("⏩ Broadcast '{$actionEnum->value}' skipped (feature disabled or invalid).");

            LogHelper::logInfo(
                null,
                [
                    'action_arg'     => $actionEnum->value,
                    'feature_exists' => (bool) $feature,
                    'is_active'      => $feature?->is_active,
                    'is_scheduled'   => $feature?->is_scheduled,
                    'gateway_type'   => $feature?->type,
                ],
                200,
                "Broadcast '{$actionEnum->value}' skipped: feature is disabled or not scheduled.",
                $traceId,
                $timer->lap()
            );

            return Command::SUCCESS;
        }

        // ===============================================================
        // 3. PREPARE SCHEDULE & TIME WINDOW
        // ===============================================================
        $schedule  = $feature->schedule;
        $timezone  = data_get($schedule, 'timezone', 'Asia/Jakarta');
        $rawDays   = data_get($schedule, 'days', []);
        $timeStart = data_get($schedule, 'time_window.start', '08:00');
        $timeEnd   = data_get($schedule, 'time_window.end', '21:00');

        if (empty($rawDays)) {
            $this->warn("⛔ No scheduled days configured for broadcast '{$actionEnum->value}'.");
            return Command::SUCCESS;
        }

        $daysArray = (array) $rawDays;
        $now       = Carbon::now($timezone);
        $lastDay   = $now->copy()->endOfMonth()->day;

        $effectiveDays = array_map(fn($d) => min((int) $d, $lastDay), $daysArray);

        $this->info("📨 Running Broadcast: {$actionEnum->value}");
        $this->line("• Time     : {$now->toDateTimeString()} ({$timezone})");
        $this->line("• Gateway  : " . strtoupper($feature->type ?? 'ONPREM'));
        $this->line("• Trace ID : {$traceId}");
        $this->line("• TestMode : " . ($isTestMode ? 'ON' : 'OFF'));

        LogHelper::logInfo(
            null,
            [
                'action_arg'     => $actionEnum->value,
                'feature_id'     => $feature->id,
                'gateway_type'   => $feature->type,
                'timestamp'      => $now->toDateTimeString(),
                'test_mode'      => $isTestMode,
                'days_effective' => $effectiveDays,
                'time_window'    => "{$timeStart}-{$timeEnd}",
            ],
            200,
            "Broadcast '{$actionEnum->value}' scheduler started",
            $traceId,
            $timer->lap()
        );

        try {
            // ===============================================================
            // 4. DATE & TIME VALIDATION
            // ===============================================================
            if (!$isTestMode) {
                if (!in_array($now->day, $effectiveDays, true)) {
                    $this->warn("⏩ Skipped: today ({$now->day}) not in scheduled days.");
                    return Command::SUCCESS;
                }

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
            // 5. EXECUTE SERVICE DIRECTLY
            // ===============================================================
            $broadcastManagerService->sendBroadcast(
                action: $actionEnum,
                isTestMode: $isTestMode,
                traceId: $traceId
            );

            $this->info("✅ Broadcast '{$actionEnum->value}' executed successfully ({$timer->lap()}s)");

            LogHelper::logInfo(
                null,
                ['action_arg' => $actionEnum->value, 'feature_id' => $feature->id],
                200,
                "Broadcast '{$actionEnum->value}' executed successfully",
                $traceId,
                $timer->lap()
            );

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("❌ Broadcast '{$actionEnum->value}' failed: {$e->getMessage()}");

            LogHelper::logError(
                null,
                [
                    'action_arg' => $actionEnum->value,
                    'exception'  => get_class($e),
                    'message'    => $e->getMessage(),
                    'file'       => $e->getFile(),
                    'line'       => $e->getLine(),
                ],
                500,
                "Broadcast '{$actionEnum->value}' scheduler failed",
                $traceId,
                $timer->lap()
            );

            return Command::FAILURE;
        }
    }
}
