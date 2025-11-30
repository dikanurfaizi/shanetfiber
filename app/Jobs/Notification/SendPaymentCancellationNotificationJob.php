<?php

namespace App\Jobs\Notification;

use App\Services\Notification\PaymentCancellationNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPaymentCancellationNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?int $receiptId,
        public int $billingId,
        public ?array $receiptData,
        public string $traceId
    ) {}

    public function handle(PaymentCancellationNotificationService $service): void
    {
        $service->send(
            receiptId: $this->receiptId,
            billingId: $this->billingId,
            receiptData: $this->receiptData,
            traceId: $this->traceId
        );
    }
}
