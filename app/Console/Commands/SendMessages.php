<?php

namespace App\Console\Commands;

use App\Services\BroadcastMessage\SendPaymentReminderService;
use Illuminate\Console\Command;
use App\Helpers\LogHelper;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

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
    protected $description = 'Send messages at every 15 minutes in 10th and 15th of each month for reminder';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $traceId = (string) Str::uuid();
        $startTime = microtime(true);
        $currentDateTime = Carbon::now()->setTimezone('Asia/Jakarta');
        $formattedDate = $currentDateTime->format('Y-m-d H:i:s');
        $dayOfMonth = $currentDateTime->day;

        // Log command initiation
        // LogHelper::logInfo(
        //     'SendMessagesCommand',
        //     [
        //         'start_time' => $formattedDate,
        //         'day_of_month' => $dayOfMonth
        //     ],
        //     200,
        //     "Starting reminder messages sending process",
        //     $traceId
        // );

        $this->info("Starting reminder messages job at {$formattedDate}");

        try {
            // Validate if today is an allowed date
            $allowedDates = [14, 15];
            if (!in_array($dayOfMonth, $allowedDates)) {
                $message = "Today ({$dayOfMonth}) is not an allowed date for sending reminders. Allowed dates: " . implode(', ', $allowedDates);

                LogHelper::logWarning(
                    'SendMessagesCommand',
                    [
                        'current_day' => $dayOfMonth,
                        'allowed_days' => $allowedDates
                    ],
                    422,
                    $message,
                    $traceId
                );

                $this->warn($message);
                return 0;
            }

            $currentHour = $currentDateTime->hour;
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

            // dispatch(new SendPaymentReminderJob())->onQueue('default');
            app(SendPaymentReminderService::class)->sendReminders();

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
                "Successfully dispatched {$this->description}",
                $traceId
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
                $errorMessage,
                $traceId
            );

            $this->error($errorTime . " - " . $errorMessage);
            return 1;
        }
    }
}
