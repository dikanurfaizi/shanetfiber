<?php

namespace App\Events\SingleNotification;

use App\Services\WhatsAppWebhook\DTO\WebhookPayload;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IncomingWhatsAppMessageEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public WebhookPayload $payload,
        public string $traceId,
        public ?int $eventId = null
    ) {
        //
    }
}
