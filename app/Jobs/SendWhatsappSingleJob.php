<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\WhatsappGateway\WhatsappGateway;
use App\Models\MessageLog;
use Carbon\Carbon;
use App\Services\WhatsappMessageService;
use App\Helpers\LogHelper;

class SendWhatsappSingleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private $billing,
        private string $messageType,
        private WhatsappGateway $gateway
    ) {}

    public function handle(): void
    {
        $messageService = app(WhatsappMessageService::class);

        // Cek jika pesan sudah pernah dikirim
        $alreadySent = MessageLog::where('customer_id', $this->billing->customer_id)
            ->where('message_type', $this->messageType)
            ->whereDate('created_at', Carbon::today())
            ->exists();

        if ($alreadySent) {
            LogHelper::logWarning(
                null,
                [
                    'customer_name' => $this->billing->customer_name,
                    'message_type' => $this->messageType,
                ],
                409,
                "Skipping redundant message for {$this->billing->customer_name} with type {$this->messageType}."
            );
            return;
        }

        $message = $messageService->bill_info($this->billing, $this->messageType);

        if ($message) {
            try {
                $sendMessage = $messageService->sendWhatsappSingleJob(
                    $this->billing->customer_name,
                    $this->billing->customer_contact,
                    $this->gateway,
                    $message
                );

                if ($sendMessage) {
                    MessageLog::create([
                        'customer_id'   => $this->billing->customer_id,
                        'message_type'  => $this->messageType,
                        'gateway_id'    => $this->gateway->id,
                        'content'       => $message,
                        'sent_at'       => now(),
                    ]);
                    LogHelper::logInfo(null, ['customer_name' => $this->billing->customer_name], 200, "Successfully sent message");
                } else {
                    LogHelper::logError(null, [
                        'billing_id' => $this->billing->id,
                        'error' => 'Failed to send message.'
                    ], 500, "Failed to send WhatsApp message.");
                }
            } catch (\Exception $e) {
                LogHelper::logError(null, [
                    'billing_id' => $this->billing->id,
                    'error' => $e->getMessage(),
                ], 500, "Failed to send message.");
            }
        }
    }
}
