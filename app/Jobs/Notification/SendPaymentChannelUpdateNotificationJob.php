<?php

namespace App\Jobs\Notification;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Notification\PaymentChannelUpdateNotificationService;

class SendPaymentChannelUpdateNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $traceId
    ) {}

    public function handle(PaymentChannelUpdateNotificationService $service): void
    {
        $service->run($this->traceId);
    }
}
