<?php

namespace App\Services\Telegrams;

use App\Jobs\Telegram\SendTelegramMessageJob;

class TelegramHandler
{
    public function __construct(private DispatchMessageService $dispatchMessageService) {}

    public function send(int $chatId, string $message, string $traceId): void
    {
        $this->dispatchMessageService->send($chatId, $message, $traceId);
    }
}
