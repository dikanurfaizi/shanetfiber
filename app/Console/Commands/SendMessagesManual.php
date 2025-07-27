<?php

namespace App\Console\Commands;

use App\Jobs\WhatsApp\ProcessWhatsappMessageJob;
use App\Jobs\Telegram\ProcessTelegramMessageJob;
use Illuminate\Console\Command;
use App\Helpers\LogHelper;
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
        $startTime = microtime(true);
        $currentDateTime = Carbon::now()->setTimezone('Asia/Jakarta');
        $formattedDate = $currentDateTime->format('Y-m-d H:i:s');
        $dayOfMonth = $currentDateTime->day;

        try {

            $traceId = (string) Str::uuid();
            ProcessWhatsappMessageJob::dispatch(
                '336',
                'activation_customer',
                $traceId,
                null,
                null,
                null,
                null,
                null,
                null
            )->onQueue('default');
            ProcessTelegramMessageJob::dispatch(
                '336',
                'activation_customer',
                $traceId,
                null,
                null,
                null,
                null
            )->onQueue('default');

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
