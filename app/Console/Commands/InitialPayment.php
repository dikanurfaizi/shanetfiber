<?php

namespace App\Console\Commands;

use App\Services\BroadcastMessage\InitialPaymentReminderService;
use Illuminate\Console\Command;
use App\Helpers\LogHelper;
use Illuminate\Support\Carbon;
use App\Models\Billing\BillingPeriode;

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
        $startTime = microtime(true);
        $now = Carbon::now()->setTimezone('Asia/Jakarta');
        $formattedDate = $now->format('Y-m-d H:i:s');
        $dayOfMonth = $now->day;

        LogHelper::logInfo('InitialPaymentCommand', [
            'start_time' => $formattedDate,
            'day_of_month' => $dayOfMonth
        ], 200, "Starting initial payment messages sending process");

        $this->info("Starting initial payment messages job at {$formattedDate}");

        try {
            $allowedDates = [1, 2, 3];
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

            // Jalankan proses pengiriman pesan
            app(InitialPaymentReminderService::class)->sendReminders();

            $dispatchTime = now()->format('Y-m-d H:i:s');
            $executionTime = round(microtime(true) - $startTime, 2);

            LogHelper::logInfo('InitialPaymentCommand', [
                'dispatch_time' => $dispatchTime,
                'execution_seconds' => $executionTime
            ], 200, "Successfully dispatched {$this->description}");

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
            ], 500, $errorMessage);

            $this->error($errorTime . " - " . $errorMessage);
            return 1;
        }
    }
}
