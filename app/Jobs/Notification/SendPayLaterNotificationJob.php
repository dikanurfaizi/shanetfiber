<?php

namespace App\Jobs\Notification;

use App\Services\Notification\PaymentPayLaterNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPayLaterNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $billingId,
        public string $traceId
    ) {}

    public function handle(PaymentPayLaterNotificationService $service): void
    {
        $service->send(
            billingId: $this->billingId,
            traceId: $this->traceId
        );
    }
}
