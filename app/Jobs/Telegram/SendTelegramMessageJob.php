<?php

namespace App\Jobs\Telegram;

use App\Helpers\LogHelper;
use GuzzleHttp\Client;
use Telegram\Bot\Api;
use Telegram\Bot\HttpClients\GuzzleHttpClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\TelegramGateway\TelegramGateway;

class SendTelegramMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $chatId;
    protected string $message;
    protected string $traceId;

    public function __construct($chatId, $message, $traceId)
    {
        $this->chatId = $chatId;
        $this->message = $message;
        $this->traceId = $traceId;
    }

    public function viaQueue()
    {
        return 'telegram';
    }

    public function handle()
    {
        try {
            $botToken = TelegramGateway::where('disabled', 0)->value('token');

            if (!$botToken) {
                throw new \Exception("Bot token not found.");
            }

            $httpClient = new GuzzleHttpClient(new Client(['verify' => false]));
            $telegram = new Api($botToken, false, $httpClient);

            // Payload yang akan dikirim
            $payload = [
                'chat_id' => $this->chatId,
                'text' => $this->message,
                'parse_mode' => 'HTML',
            ];

            // Log sebelum kirim (untuk bisa tes di Postman)
            LogHelper::logInfo(null, [
                'payload' => $payload
            ], 200, "Preparing to send Telegram message", $this->traceId);

            $response = $telegram->sendMessage($payload);

            $logResponse = [
                'message_id' => $response['message_id'] ?? null,
                'from' => [
                    'first_name' => $response['from']['first_name'] ?? null,
                    'username' => $response['from']['username'] ?? null,
                ],
                'chat' => [
                    'first_name' => $response['chat']['first_name'] ?? null,
                    'last_name' => $response['chat']['last_name'] ?? null,
                    'username' => $response['chat']['username'] ?? null,
                ],
                'date' => isset($response['date']) ? date('Y-m-d H:i:s', $response['date']) : null,
            ];

            LogHelper::logInfo(null, ['response' => $logResponse], 200, "Telegram message sent", $this->traceId);
        } catch (\Exception $e) {
            LogHelper::logError(null, [
                'chat_id' => $this->chatId,
                'message' => $this->message
            ], 500, "Failed to send Telegram message: " . $e->getMessage(), $this->traceId);
        }
    }
}
