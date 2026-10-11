<?php

namespace App\Services\Telegrams;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\Telegram\TelegramChatId;
use App\Models\Telegram\TelegramGateway;
use GuzzleHttp\Client;
use Telegram\Bot\Api;
use Telegram\Bot\HttpClients\GuzzleHttpClient;

class TelegramGetUpdateService
{
    public function findNewChatIds(string $traceId): array
    {
        $timer = new ExecutionTimer();

        // Log awal eksekusi service
        LogHelper::logInfo('TelegramGetUpdateService@findNewChatIds', [
            'status' => 'started',
        ], 200, 'Starting to fetch and compare Telegram chat IDs', $traceId, $timer->lap());

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

            // 🔹 Ekstrak informasi chat dari berbagai tipe update Telegram
            $fetchedChats = collect($updates)
                ->map(function ($update) {
                    // Cek objek chat dari message, edited_message, callback_query, atau my_chat_member
                    $message = $update->getMessage()
                        ?? $update->getEditedMessage()
                        ?? $update->getCallbackQuery()?->getMessage();

                    $chat = $message?->getChat();

                    if (!$chat) return null;

                    return [
                        'chat_id'    => (string) $chat->getId(),
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
            $newChats = $fetchedChats->reject(
                fn($chat) =>
                $existingChatIds->contains((string) $chat['chat_id'])
            )->values()->toArray();

            // 🔹 Logging hasil sukses dengan timer
            LogHelper::logInfo('TelegramGetUpdateService@findNewChatIds', [
                'total_fetched'  => $fetchedChats->count(),
                'existing_count' => $existingChatIds->count(),
                'new_count'      => count($newChats),
                'new_chats'      => $newChats,
            ], 200, 'Compared and filtered new Telegram chats successfully', $traceId, $timer->lap());

            return $newChats;
        } catch (\Throwable $e) {
            // 🔹 Logging error dengan timer
            LogHelper::logError('TelegramGetUpdateService@findNewChatIds', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, 'Failed to compare Telegram chat IDs: ' . $e->getMessage(), $traceId, $timer->lap());

            return [];
        }
    }
}
