<?php

namespace App\Events\SingleNotification;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WhatsAppTestRequest
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $receiverNumber,
        public readonly string $traceId
    ) {}
}
