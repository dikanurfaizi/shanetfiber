<?php

namespace App\Events\SingleNotification;

use App\Models\Telegram\TelegramGateway;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TelegramBotLinkRequest
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $receiverNumber,
        public readonly TelegramGateway $telegramGateway,
        public readonly ?int $eventId,
        public readonly string $traceId
    ) {}
}
