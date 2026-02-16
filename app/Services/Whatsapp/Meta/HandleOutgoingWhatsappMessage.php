<?php

namespace App\Services\Whatsapp\Meta;

use App\Models\Whatsapp\WhatsappConversation;
use App\Models\Whatsapp\WhatsappWebhookMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HandleOutgoingWhatsappMessage
{
    public function handle(
        ?int $adminId,
        string $customerNumber,
        string $businessNumber,
        string $waMessageId,
        array $payload
    ): void {

        DB::transaction(function () use (
            $adminId,
            $customerNumber,
            $businessNumber,
            $waMessageId,
            $payload
        ) {

            $conversation = $this->resolveConversation($customerNumber);

            // 🔐 LOCK VALIDATION
            $conversation->refresh();

            if (
                $conversation->locked_by_admin_id &&
                $conversation->locked_by_admin_id !== $adminId
            ) {
                throw new \Exception('Conversation locked by another admin');
            }

            // Acquire lock if not exists
            if (!$conversation->locked_by_admin_id) {
                $conversation->update([
                    'locked_by_admin_id' => $adminId,
                    'locked_at' => now(),
                ]);
            }

            // === Idempotency ===
            $existing = WhatsappWebhookMessage::where('wa_message_id', $waMessageId)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return;
            }

            // Create outgoing message
            WhatsappWebhookMessage::create([
                'conversation_id' => $conversation->id,
                'wa_message_id'   => $waMessageId,
                'trace_id'        => Str::uuid(),
                'direction'       => 'outgoing',
                'from_number'     => $businessNumber,
                'to_number'       => $customerNumber,
                'type'            => data_get($payload, 'type', 'text'),
                'text_body'       => data_get($payload, 'text.body'),
                'timestamp_wa'    => now(),
                'sent_at'         => now(),
                'last_status'     => 'sent',
                'payload'         => $payload,
            ]);

            $conversation->update([
                'last_message_at' => now(),
                'has_unread'      => false,
                'has_admin_reply' => true,
            ]);
        });
    }

    private function resolveConversation(string $customerNumber): WhatsappConversation
    {
        return WhatsappConversation::firstOrCreate(
            [
                'customer_number' => $customerNumber,
                'status'          => 'open'
            ],
            [
                'channel'         => 'whatsapp',
                'last_message_at' => now(),
            ]
        );
    }
}
