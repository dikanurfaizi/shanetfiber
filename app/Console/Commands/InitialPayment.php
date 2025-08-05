<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\Billing\BillingPeriode;
use App\Helpers\LogHelper;
use App\Jobs\WhatsApp\ProcessWhatsappMessageJob;

class InitialPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'initial-payment:messages-send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send messages every 15 minutes on the 1st, 2nd, and 3rd of each month for initial payment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $traceId = (string) Str::uuid();
        $startTime = microtime(true);
        $now = Carbon::now()->setTimezone('Asia/Jakarta');
        $formattedDate = $now->format('Y-m-d H:i:s');
        $dayOfMonth = $now->day;

        $this->info("Starting initial payment messages job at {$formattedDate}");

        try {
            $allowedDates = [1, 2, 3];
            [$startDate, $endDate] = $this->getAllowedDateRange($allowedDates);
            if (!in_array($dayOfMonth, $allowedDates)) {
                $message = "Today ({$dayOfMonth}) is not an allowed date. Allowed dates: " . implode(', ', $allowedDates);

                LogHelper::logWarning(
                    'SendMessagesCommand',
                    [
                        'current_day' => $dayOfMonth,
                        'allowed_days' => $allowedDates
                    ],
                    422,
                    $message
                );

                $this->warn($message);
                return 0;
            }

            $currentHour = $now->hour;
            if ($currentHour < 8 || $currentHour >= 22) {
                $message = "Current time ({$currentHour}:00 WIB) is outside the allowed range (08:00 - 22:00 WIB). Command will not proceed.";

                LogHelper::logWarning(
                    'SendMessagesCommand',
                    [
                        'current_hour' => $currentHour,
                        'allowed_range' => '08:00 - 22:00'
                    ],
                    422,
                    $message,
                    $traceId
                );

                $this->warn($message);
                return 0;
            }

            // Format saat ini: "May 2025"
            $currentPeriod = $now->format('F Y');

            $lastPeriode = BillingPeriode::orderByDesc('created_at')->first();

            if (!$lastPeriode) {
                $this->error("No billing periode found.");
                return 1;
            }

            if ($lastPeriode->name !== $currentPeriod) {
                $this->warn("Current period ($currentPeriod) is not equal to last billing periode ({$lastPeriode->name}). Skipping...");
                return 0;
            }

            ProcessWhatsappMessageJob::dispatch(
                null,
                'create_new_bill',
                $traceId,
                null,
                null,
                null,
                $startDate,
                $endDate,
                null
            )->onQueue('whatsapp_broadcast');

            $dispatchTime = now()->format('Y-m-d H:i:s');
            $executionTime = round(microtime(true) - $startTime, 2);

            LogHelper::logInfo('InitialPaymentCommand', [
                'dispatch_time' => $dispatchTime,
                'execution_seconds' => $executionTime
            ], 200, "Successfully dispatched {$this->description}", $traceId);

            $this->info("Successfully dispatched initial payment message at {$dispatchTime}");
            $this->info("Total execution time: {$executionTime} seconds");
            return 0;
        } catch (\Exception $e) {
            $errorTime = now()->format('Y-m-d H:i:s');
            $errorMessage = "Failed to dispatch initial payment message: " . $e->getMessage();

            LogHelper::logError('InitialPaymentCommand', [
                'error_time' => $errorTime,
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500, $errorMessage, $traceId);

            $this->error($errorTime . " - " . $errorMessage);
            return 1;
        }
    }

    private function getAllowedDateRange(array $allowedDates): array
    {
        $startDay = min($allowedDates);
        $endDay   = max($allowedDates);

        $startDate = Carbon::now()->startOfMonth()->setDay($startDay)->startOfDay();
        $endDate   = Carbon::now()->startOfMonth()->setDay($endDay)->addDay()->startOfDay();

        return [$startDate, $endDate];
    }
}
