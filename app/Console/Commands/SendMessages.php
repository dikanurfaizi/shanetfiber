<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Helpers\LogHelper;
use App\DataTransferObjects\WhatsappJobDto;
use App\Enums\WhatsAppAction;
use App\Jobs\WhatsApp\ProcessWhatsappMessageJob;

class SendMessages extends Command
{
    protected $signature   = 'reminder:messages-send';
    protected $description = 'Send WhatsApp reminder messages on the 15th between 08:00-22:00 WIB every 15 minutes';

    public function handle(): int
    {
        $traceId   = (string) Str::uuid();
        $startTime = microtime(true);
        $now       = Carbon::now('Asia/Jakarta');

        $allowedDays  = [15];
        $allowedHours = range(8, 21); // 08:00–21:59

        $this->info("=== Reminder Message Job Started @ {$now->toDateTimeString()} (Trace: {$traceId}) ===");

        try {
            // ---- 1. Validasi tanggal ----
            if (!in_array($now->day, $allowedDays)) {
                $this->warn("Skipped: today ({$now->day}) not in allowed days [10,15].");
                LogHelper::logInfo('SendMessages', [
                    'trace_id' => $traceId,
                    'day' => $now->day,
                ], 200, 'Skipped — not allowed date', $traceId);
                return Command::SUCCESS;
            }

            // ---- 2. Validasi jam ----
            if (!in_array($now->hour, $allowedHours)) {
                $this->warn("Skipped: current hour {$now->hour}:00 outside allowed range (08-22 WIB).");
                LogHelper::logInfo('SendMessages', [
                    'trace_id' => $traceId,
                    'hour' => $now->hour,
                ], 200, 'Skipped — outside allowed hour range', $traceId);
                return Command::SUCCESS;
            }

            // ---- 3. Siapkan payload WhatsApp Job ----
            $jobData = new WhatsappJobDto(
                customerId: null,
                action: WhatsAppAction::SEND_INFO_BILLING->value,
                traceId: $traceId,
                billingId: null,
                receiptId: null,
                receiptData: null,
                startDate: null,
                endDate: null,
                orderId: null,
                oldPaketName: null,
                oldPaketPrice: null
            );

            // ---- 4. Dispatch ke queue WhatsApp ----
            ProcessWhatsappMessageJob::dispatch($jobData)->onQueue('whatsapp_broadcast');

            $executionTime = round(microtime(true) - $startTime, 2);
            $dispatchTime  = now('Asia/Jakarta')->format('Y-m-d H:i:s');

            $this->info("✅ Reminder message dispatched successfully at {$dispatchTime} ({$executionTime}s)");

            LogHelper::logInfo('SendMessages', [
                'trace_id' => $traceId,
                'dispatch_time' => $dispatchTime,
                'execution_seconds' => $executionTime,
            ], 200, 'Reminder message job dispatched', $traceId);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            LogHelper::logError('SendMessages', [
                'trace_id' => $traceId,
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, 'Reminder message job failed', $traceId);

            return Command::FAILURE;
        }
    }
}
