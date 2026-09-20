<?php

namespace App\Console\Commands;

use App\Helpers\LogHelper;
use App\Helpers\ExecutionTimer;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SendMessagesManual extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'message:send-manually';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send messages manually';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $traceId = (string) Str::uuid();
        $timer = new ExecutionTimer();
        $currentDateTime = Carbon::now()->setTimezone('Asia/Jakarta');
        $formattedDate = $currentDateTime->format('Y-m-d H:i:s');
        $dayOfMonth = $currentDateTime->day;

        try {
            $today = Carbon::today();
            // Log successful dispatch
            $dispatchTime = Carbon::now()->format('Y-m-d H:i:s');

            LogHelper::logInfo(
                null,
                [
                    'dispatch_time' => $dispatchTime
                ],
                200,
                "Successfully dispatched {$this->description}",
                $traceId,
                $timer->lap()
            );

            $this->info("Successfully dispatched reminder job at {$dispatchTime}");
            $this->info("Total execution time: {$timer->lap()} seconds");

            return 0;
        } catch (\Exception $e) {
            $errorTime = Carbon::now()->format('Y-m-d H:i:s');
            $errorMessage = "Failed to dispatch reminder job: " . $e->getMessage();

            LogHelper::logError(
                null,
                [
                    'error_time' => $errorTime,
                    'exception' => get_class($e),
                    'error_message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ],
                500,
                $errorMessage,
                $traceId,
                $timer->lap()
            );

            $this->error($errorTime . " - " . $errorMessage);
            return 1;
        }
    }
}
