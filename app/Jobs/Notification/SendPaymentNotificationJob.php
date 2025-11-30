<?php

namespace App\Jobs\Notification;

use App\Services\Notification\PaymentNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPaymentNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $receiptId,
        public string $traceId,
    ) {}

    public function handle(PaymentNotificationService $notificationService): void
    {
        $notificationService->sendWhatsAppPaymentNotificationByReceiptId(
            $this->receiptId,
            $this->traceId
        );
    }
}
