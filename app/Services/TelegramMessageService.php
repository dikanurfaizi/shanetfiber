<?php

namespace App\Services;

use App\Models\TelegramGateway\TelegramChatId;
use App\Models\TelegramGateway\TelegramGateway;
use App\Models\TelegramGateway\TelegramMessage;
use App\Models\Customer;
use App\Helpers\LogHelper;
use App\Helpers\Markdown;
use App\Jobs\Telegram\SendTelegramMessageJob;
use Illuminate\Support\Collection;
use GuzzleHttp\Client;
use Telegram\Bot\Api;
use Telegram\Bot\HttpClients\GuzzleHttpClient;

class TelegramMessageService
{
    protected string $botToken;
    protected Api $telegram;

    public function __construct()
    {
        $botToken = TelegramGateway::where('disabled', 0)->value('token');

        if (!$botToken) {
            throw new \Exception('Bot token not found');
        }

        $httpClient = new GuzzleHttpClient(
            new Client(['verify' => false])
        );

        $this->telegram = new Api($botToken, false, $httpClient);
    }

    public function getUpdates(): array
    {
        return $this->telegram->getUpdates();
    }

    public function findNewChatIds(string $traceId): Collection
    {
        $updates = $this->getUpdates();

        $existingChatIds = TelegramChatId::whereNotNull('chat_id')
            ->whereNull('deleted_by')
            ->pluck('chat_id')
            ->map(fn($id) => (int)$id)
            ->toArray();

        return collect($updates)
            ->map(function ($item) {
                $from = $item['message']['from'] ?? null;
                if (!$from) return null;

                return [
                    'chat_id'    => (int) $from['id'],
                    'username'   => $from['username'] ?? '-',
                    'first_name' => $from['first_name'] ?? '-',
                    'last_name'  => $from['last_name'] ?? '-',
                ];
            })
            ->filter()
            ->unique('chat_id')
            ->filter(fn($user) => !in_array($user['chat_id'], $existingChatIds))
            ->values();
    }

    public function activation($customerId, $action, $traceId)
    {
        $customer = Customer::find($customerId);
        if (!$customer) {
            LogHelper::logError(
                null,
                ['customer_id' => $customerId],
                404,
                "Customer tidak ditemukan saat action: {$action}",
                $traceId
            );
            return;
        }
        try {
            $admin_name = $customer->user->name;
            $chat_id = TelegramChatId::where('user_id', $customer->user_id)->value('chat_id');

            if (!$chat_id) {
                LogHelper::logWarning(
                    null,
                    [
                        'customer_id' => $customer->customer_id,
                        'user_id' => $customer->user_id,
                    ],
                    404,
                    "Telegram Chat ID tidak ditemukan untuk user_id: {$customer->user_id}",
                    $traceId
                );
                return;
            }

            $replace = [
                '%name%' => $customer->name,
                '%address%' => $customer->address,
                '%email%' => $customer->email ?? '-',
                '%customer_id%' => $customer->customer_id,
                '%paket%' => $customer->paket->name . ' ' . $customer->paket->description,
                '%ppp_username%' => $customer->username,
                '%ppp_password%' => $customer->password_ppp,
                '%admin_name%' => $admin_name,
            ];

            $message = $this->getDefaultMessage($action, $traceId);
            if (!$message) {
                LogHelper::logWarning(
                    null,
                    [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'address' => $customer->address,
                        'email' => $customer->email ?? '-',
                        'customer_id' => $customer->customer_id,
                        'action' => $action,
                    ],
                    404, // Not Found
                    "Template pesan untuk aksi '{$action}' tidak ditemukan untuk customer ID: {$customer->id}",
                    $traceId
                );
                return;
            }

            $replaceMessage = $this->replaceMessagePlaceholders($message, $replace, $traceId);
            // LogHelper::logInfo(null, 200, ['text' => $replaceMessage], "DEBUG final telegram message", $traceId);
            $message = Markdown::convertMarkdownToHtml($replaceMessage);
            // LogHelper::logInfo(null, 200, ['text' => $message], "DEBUG final telegram message", $traceId);
            SendTelegramMessageJob::dispatch($chat_id, $message, $traceId)->onQueue('default');
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'id' => $customer,
                    'name' => $customer->name,
                    'address' => $customer->address,
                    'email' => $customer->email ?? '-',
                    'customer_id' => $customer->customer_id,
                    'action' => $action,
                ],
                500, // Internal Error
                "Terjadi kesalahan dalam {$action}: " . $e->getMessage(),
                $traceId
            );
        }
    }

    public function getDefaultMessage($slug, $traceId)
    {
        try {
            return TelegramMessage::where('slug', $slug)
                ->where('disabled', false)
                ->where('default', true)
                ->value('reply_message');
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                null,
                500,
                "Error retrieving default message for slug: $slug | " . $e->getMessage()
            );
            return "Pesan default tidak tersedia.";
        }
    }

    public function replaceMessagePlaceholders($message, array $replace, string $traceId)
    {
        try {
            return str_replace(array_keys($replace), array_values($replace), $message);
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'message' => $message,
                    'replace' => $replace
                ],
                500, // Bad Request
                "Error replacing message placeholders: {$e->getMessage()}"
            );
            return $message; // Jika gagal, return pesan asli agar tidak blank
        }
    }
}
