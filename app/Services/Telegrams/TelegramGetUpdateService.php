<?php

namespace App\Services\Telegrams;

use App\Models\Telegram\TelegramGateway;
use App\Models\Telegram\TelegramChatId;
use Telegram\Bot\HttpClients\GuzzleHttpClient;
use App\Helpers\LogHelper;
use GuzzleHttp\Client;
use Telegram\Bot\Api;

class TelegramGetUpdateService
{
    public function findNewChatIds(string $traceId): array
    {
        try {
            // 🔹 Ambil token bot aktif
            $botToken = TelegramGateway::where('disabled', 0)->value('token');
            if (!$botToken) {
                throw new \RuntimeException('Bot token not found');
            }

            // 🔹 Inisialisasi Telegram SDK
            $httpClient = new GuzzleHttpClient(new Client(['verify' => false]));
            $telegram = new Api($botToken, false, $httpClient);

            // 🔹 Ambil update terbaru dari Telegram
            $updates = $telegram->getUpdates();

            // 🔹 Ekstrak informasi chat
            $fetchedChats = collect($updates)
                ->map(function ($update) {
                    $chat = $update->getMessage()?->getChat();
                    if (!$chat) return null;

                    return [
                        'chat_id'    => (string) $chat->getId(), // penting: cast ke string untuk hindari - kehilangan tanda minus
                        'username'   => $chat->getUsername(),
                        'first_name' => $chat->getFirstName(),
                        'last_name'  => $chat->getLastName(),
                    ];
                })
                ->filter()
                ->unique('chat_id')
                ->values();

            // 🔹 Ambil chat_id yang sudah tersimpan di DB
            $existingChatIds = TelegramChatId::pluck('chat_id')->map(fn($v) => (string) $v);

            // 🔹 Filter hanya chat baru
            $newChats = $fetchedChats->reject(fn($chat) =>
                $existingChatIds->contains((string) $chat['chat_id'])
            )->values()->toArray();

            // 🔹 Logging hasil
            LogHelper::logInfo('TelegramGetUpdateService', [
                'total_fetched'  => $fetchedChats->count(),
                'existing_count' => $existingChatIds->count(),
                'new_count'      => count($newChats),
                'new_chats'      => $newChats,
            ], 200, 'Compared and filtered new Telegram chats', $traceId);

            return $newChats;
        } catch (\Throwable $e) {
            LogHelper::logError('TelegramGetUpdateService', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, 'Failed to compare Telegram chat IDs', $traceId);

            return [];
        }
    }
}
