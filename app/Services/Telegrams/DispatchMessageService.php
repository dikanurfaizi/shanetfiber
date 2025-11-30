<?php

namespace App\Services\Telegrams;

use App\Models\Telegram\TelegramGateway;
use Telegram\Bot\HttpClients\GuzzleHttpClient;
use App\Helpers\LogHelper;
use GuzzleHttp\Client;
use Telegram\Bot\Api;

class DispatchMessageService
{
    public function send($chatId, $message, $traceId)
    {
        $botToken = TelegramGateway::where('disabled', 0)->value('token');

        if (!$botToken) {
            throw new \RuntimeException('Bot token not found');
        }

        $httpClient = new GuzzleHttpClient(new Client(['verify' => false]));
        $telegram = new Api($botToken, false, $httpClient);

        $payload = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
        ];

        return $telegram->sendMessage($payload);
    }
}
