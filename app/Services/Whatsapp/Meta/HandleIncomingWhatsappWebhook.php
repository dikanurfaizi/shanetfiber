<?php

namespace App\Services\Whatsapp\Meta;

use App\Models\Whatsapp\WhatsappConversation;
use App\Models\Whatsapp\WhatsappWebhookMessage;
use App\Models\Whatsapp\WhatsappMessageStatus;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Database\QueryException;

class HandleIncomingWhatsappWebhook
{
    public function handleMessages(array $value): void
    {
        foreach ($value['messages'] ?? [] as $messageData) {

            $waMessageId = $messageData['id'] ?? null;
            $fromNumber  = $messageData['from'] ?? null;

            if (!$waMessageId || !$fromNumber) {
                continue;
            }

            $conversation = $this->resolveConversation($fromNumber);

            try {
                // Insert langsung — biarkan unique index handle duplikasi
                WhatsappWebhookMessage::create([
                    'conversation_id' => $conversation->id,
                    'wa_message_id'   => $waMessageId,
                    'trace_id'        => Str::uuid(),
                    'direction'       => 'incoming',
                    'from_number'     => $fromNumber,
                    'type'            => $messageData['type'] ?? 'text',
                    'text_body'       => data_get($messageData, 'text.body'),
                    'timestamp_wa'    => isset($messageData['timestamp'])
                        ? Carbon::createFromTimestamp($messageData['timestamp'])
                        : null,
                    'last_status'     => 'received',
                    'payload'         => $messageData,
                ]);
            } catch (QueryException $e) {

                // Duplicate key? artinya webhook retry → aman, skip
                if ($this->isDuplicateKey($e)) {
                    continue;
                }

                throw $e;
            }

            // Append-only status audit (anggap table ini juga punya unique)
            WhatsappMessageStatus::firstOrCreate(
                [
                    'wa_message_id' => $waMessageId,
                    'status'        => 'received',
                ],
                [
                    'status_at'   => now(),
                    'raw_payload' => $messageData,
                ]
            );

            // Update conversation snapshot ringan (tanpa lock)
            $conversation->update([
                'has_unread'      => true,
                'last_message_at' => now(),
            ]);
        }
    }

    private function resolveConversation(string $customerNumber): WhatsappConversation
    {
        return WhatsappConversation::firstOrCreate(
            ['customer_number' => $customerNumber],
            [
                'channel'         => 'whatsapp',
                'status'          => 'open',
                'last_message_at' => now(),
            ]
        );
    }

    private function isDuplicateKey(QueryException $e): bool
    {
        return str_contains($e->getMessage(), 'Duplicate entry');
    }
}
