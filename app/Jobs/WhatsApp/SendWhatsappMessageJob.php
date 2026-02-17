<?php

namespace App\Jobs\Whatsapp;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Whatsapp\WhatsappMessageService;

class SendWhatsappMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $messageType,
        private string $receiver,
        private string $message,
        private ?string $customerId,
        private string $traceId,
        private ?int $messageEventId = null,
        private ?string $deviceId = null,
    ) {}

    public function handle(WhatsappMessageService $service): void
    {
        $service->send(
            messageType: $this->messageType,
            receiver: $this->receiver,
            message: $this->message,
            customerId: $this->customerId,
            traceId: $this->traceId,
            messageEventId: $this->messageEventId,
            deviceId: $this->deviceId,
        );
    }
}
