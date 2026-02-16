<?php

namespace App\Services\Whatsapp\Meta;

use App\Models\Whatsapp\WhatsappConversation;
use App\Models\Whatsapp\WhatsappWebhookMessage;
use App\Models\Whatsapp\WhatsappMessageStatus;
use Illuminate\Support\Str;
use Carbon\Carbon;

class HandleIncomingWhatsappWebhook
{
    public function handleMessages(array $value): void
    {
        foreach ($value['messages'] ?? [] as $messageData) {

            $waMessageId = $messageData['id'] ?? null;

            if (!$waMessageId) {
                continue;
            }

            // === IDEMPOTENCY CHECK (DB-level) ===
            $existing = WhatsappWebhookMessage::where('wa_message_id', $waMessageId)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                continue;
            }

            $fromNumber = $messageData['from'] ?? null;

            if (!$fromNumber) {
                continue;
            }

            $conversation = $this->resolveConversation($fromNumber);

            // === CREATE MESSAGE ===
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

            // === STATUS AUDIT (append only) ===
            WhatsappMessageStatus::create([
                'wa_message_id' => $waMessageId,
                'status'        => 'received',
                'status_at'     => now(),
                'raw_payload'   => $messageData,
            ]);

            // === UPDATE CONVERSATION SNAPSHOT ===
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
}
