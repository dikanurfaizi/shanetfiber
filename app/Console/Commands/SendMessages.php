<?php

namespace App\Console\Commands;

use App\Jobs\SendPaymentReminderJob;
use Illuminate\Console\Command;
use App\Helpers\LogHelper;
use Illuminate\Support\Carbon;

class SendMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:messages-send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send messages at 9:00 AM on the 15th, 16th, and 17th of each month';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);
        $currentDateTime = Carbon::now()->setTimezone('Asia/Jakarta');
        $formattedDate = $currentDateTime->format('Y-m-d H:i:s');
        $dayOfMonth = $currentDateTime->day;

        // Log command initiation
        LogHelper::logInfo(
            'SendMessagesCommand',
            [
                'start_time' => $formattedDate,
                'day_of_month' => $dayOfMonth
            ],
            200,
            "Starting reminder messages sending process"
        );

        $this->info("Starting reminder messages job at {$formattedDate}");

        try {
            // Validate if today is an allowed date
            $allowedDates = [10, 15, 20];
            if (!in_array($dayOfMonth, $allowedDates)) {
                $message = "Today ({$dayOfMonth}) is not an allowed date for sending reminders. Allowed dates: " . implode(', ', $allowedDates);

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

            // Dispatch the job with logging
            LogHelper::logInfo(
                'SendMessagesCommand',
                [],
                200,
                "Dispatching SendPaymentReminderJob to queue"
            );

            dispatch(new SendPaymentReminderJob())->onQueue('default');

            // Log successful dispatch
            $dispatchTime = Carbon::now()->format('Y-m-d H:i:s');
            $executionTime = round(microtime(true) - $startTime, 2);

            LogHelper::logInfo(
                'SendMessagesCommand',
                [
                    'dispatch_time' => $dispatchTime,
                    'execution_seconds' => $executionTime
                ],
                200,
                "Successfully dispatched SendPaymentReminderJob"
            );

            $this->info("Successfully dispatched reminder job at {$dispatchTime}");
            $this->info("Total execution time: {$executionTime} seconds");

            return 0;
        } catch (\Exception $e) {
            $errorTime = Carbon::now()->format('Y-m-d H:i:s');
            $errorMessage = "Failed to dispatch reminder job: " . $e->getMessage();

            LogHelper::logError(
                'SendMessagesCommand',
                [
                    'error_time' => $errorTime,
                    'exception' => get_class($e),
                    'error_message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ],
                500,
                $errorMessage
            );

            $this->error($errorTime . " - " . $errorMessage);
            return 1;
        }
    }
}
