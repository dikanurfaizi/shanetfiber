<?php

namespace App\Services\Telegrams;

use App\Jobs\Telegram\SendTelegramMessageJob;

class TelegramHandler
{
    public function __construct(private DispatchMessageService $dispatchMessageService) {}

    /**
     * @return array{status:int|string, raw:mixed, message_id:?string}
     */
    public function send(int $chatId, string $message, string $traceId): array
    {
        return $this->dispatchMessageService->send($chatId, $message, $traceId);
    }
}
