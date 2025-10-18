<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\Billing\BillingPeriode;
use App\Helpers\LogHelper;
use App\DataTransferObjects\WhatsappJobDto;
use App\Enums\WhatsAppAction;
use App\Jobs\WhatsApp\ProcessWhatsappMessageJob;

class InitialPayment extends Command
{
    protected $signature   = 'initial-payment:messages-send';
    protected $description = 'Send WhatsApp messages on 1-3 each month between 08:00-22:00 WIB for initial payment reminders';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $traceId = (string) Str::uuid();
        $startTime = microtime(true);
        $now = Carbon::now('Asia/Jakarta');

        $allowedDays = [1, 2, 3];
        $allowedHours = range(8, 21); // 08:00-21:59

        $this->info("=== Initial Payment Job Started @ {$now->toDateTimeString()} (Trace: {$traceId}) ===");

        try {
            // ---- 1. Validasi tanggal ----
            if (!in_array($now->day, $allowedDays)) {
                $this->warn("Skipped: today ({$now->day}) not in allowed days [1,2,3].");
                LogHelper::logInfo('InitialPayment', [
                    'trace_id' => $traceId,
                    'day' => $now->day
                ], 200, 'Skipped — not an allowed date', $traceId);
                return Command::SUCCESS;
            }

            // ---- 2. Validasi jam ----
            if (!in_array($now->hour, $allowedHours)) {
                $this->warn("Skipped: current hour {$now->hour}:00 outside range 08-22 WIB.");
                LogHelper::logInfo('InitialPayment', [
                    'trace_id' => $traceId,
                    'hour' => $now->hour
                ], 200, 'Skipped — outside allowed hour range', $traceId);
                return Command::SUCCESS;
            }

            // ---- 3. Cek periode billing ----
            $currentPeriod = $now->format('F Y');
            $billing = BillingPeriode::where('name', $currentPeriod)->first();

            if (!$billing) {
                $this->error("No billing period found for {$currentPeriod} — job aborted.");
                LogHelper::logError('InitialPayment', [
                    'trace_id' => $traceId,
                    'period' => $currentPeriod
                ], 404, 'Billing period not found', $traceId);
                return Command::FAILURE;
            }

            // ---- 4. Siapkan rentang tanggal (1–3) ----
            [$startDate, $endDate] = $this->getAllowedDateRange($allowedDays);

            // ---- 5. Dispatch WhatsApp Job ----
            $jobData = new WhatsappJobDto(
                customerId: null,
                action: WhatsAppAction::CREATE_NEW_BILL->value,
                traceId: $traceId,
                billingId: null,
                receiptId: null,
                receiptData: null,
                startDate: $startDate,
                endDate: $endDate,
                orderId: null,
                oldPaketName: null,
                oldPaketPrice: null
            );

            ProcessWhatsappMessageJob::dispatch($jobData)->onQueue('whatsapp_broadcast');

            $execTime = round(microtime(true) - $startTime, 2);
            $this->info("✅ WhatsApp broadcast dispatched successfully in {$execTime}s");

            LogHelper::logInfo('InitialPayment', [
                'trace_id' => $traceId,
                'period' => $currentPeriod,
                'execution_time' => $execTime,
                'start_range' => $startDate->toDateTimeString(),
                'end_range' => $endDate->toDateTimeString()
            ], 200, 'Initial payment broadcast dispatched', $traceId);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            LogHelper::logError('InitialPayment', [
                'trace_id' => $traceId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500, 'Initial payment job failed', $traceId);

            return Command::FAILURE;
        }
    }

    private function getAllowedDateRange(array $allowedDays): array
    {
        $month = Carbon::now('Asia/Jakarta')->startOfMonth();
        $start = $month->copy()->day(min($allowedDays))->startOfDay();
        $end   = $month->copy()->day(max($allowedDays))->addDay()->startOfDay();
        return [$start, $end];
    }
}