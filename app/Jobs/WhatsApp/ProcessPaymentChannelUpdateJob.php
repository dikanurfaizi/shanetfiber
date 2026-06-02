<?php

namespace App\Jobs\Whatsapp;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Whatsapp\GowaService;

class ProcessPaymentChannelUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $eventId,
        private string $deviceId,
        private string $traceId,
        private string $recipient,
        private array $bankPayload,
        private string $mainSlug
    ) {}

    /**
     * Eksekusi GowaService murni tanpa membawa parameter Carbon delay lagi
     */
    public function handle(GowaService $gowaService): void
    {
        $gowaService->paymentChannelUpdate(
            eventId: $this->eventId,
            deviceId: $this->deviceId,
            traceId: $this->traceId,
            recipient: $this->recipient,
            bankPayload: $this->bankPayload,
            resolvedTemplate: $this->mainSlug
        );
    }
}
