<?php

namespace App\Console\Commands;

use App\Jobs\WhatsApp\ProcessWhatsappMessageJob;
use App\DataTransferObjects\WhatsappJobDto;
use App\Helpers\LogHelper;
use App\Enums\WhatsAppAction;
use Illuminate\Support\Facades\DB;
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
        $startTime = microtime(true);
        $currentDateTime = Carbon::now()->setTimezone('Asia/Jakarta');
        $formattedDate = $currentDateTime->format('Y-m-d H:i:s');
        $dayOfMonth = $currentDateTime->day;

        try {
            $today = Carbon::today();

            // $results = DB::table('receipts as r')
            //     ->join('billings as b', 'b.receipt_id', '=', 'r.id')
            //     ->leftJoin('message_logs as ml', function ($join) {
            //         $join->on('ml.customer_id', '=', 'b.customer_id')
            //             ->where('ml.message_type', 'like', '%payment%');
            //     })
            //     ->leftJoin('message_log_errors as mle', function ($join) {
            //         $join->on('mle.customer_id', '=', 'b.customer_id')
            //             ->where('mle.message_type', 'like', '%payment%');
            //     })
            //     ->whereDate('r.payment_time', Carbon::today())
            //     ->whereNull('ml.id')
            //     ->whereNull('mle.id')
            //     ->select([
            //         'r.id as receipt_id',
            //         'r.customer_id',
            //         'r.customer_name',
            //         'r.payment_time',
            //         'r.payment_methode',
            //         'b.id as billing_id',
            //         'ml.id as message_log_id',
            //         'mle.id as message_log_error_id',
            //     ])
            //     ->get();
            // foreach ($results as $result) {
            $traceId = (string) Str::uuid();

            // $whatsappJobData1 = new WhatsappJobDto(
            //     customerId: '259',
            //     action: WhatsappAction::PAYLATER->value,
            //     traceId: $traceId,
            //     billingId: '1944',
            //     receiptId: null,
            //     receiptData: null,
            //     startDate: null,
            //     endDate: null,
            //     orderId: null,
            //     oldPaketName: null,
            //     oldPaketPrice: null
            // );

            // ProcessWhatsappMessageJob::dispatch($whatsappJobData1)->onQueue('whatsapp_broadcast');

            // $whatsappJobData2 = new WhatsappJobDto(
            //     customerId: '62',
            //     action: WhatsappAction::CASH->value,
            //     traceId: $traceId,
            //     billingId: '1795',
            //     receiptId: '2181',
            //     receiptData: null,
            //     startDate: null,
            //     endDate: null,
            //     orderId: null,
            //     oldPackageName: null,
            //     oldPackagePrice: null
            // );

            // ProcessWhatsappMessageJob::dispatch($whatsappJobData2)->onQueue('whatsapp_broadcast');
            // }
            // ProcessTelegramMessageJob::dispatch(
            //     '336',
            //     'activation_customer',
            //     $traceId,
            //     null,
            //     null,
            //     null,
            //     null
            // )->onQueue('default');

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
