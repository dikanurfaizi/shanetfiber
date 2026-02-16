<?php

namespace App\Services\Whatsapp\Meta;

use App\Models\Whatsapp\WhatsappWebhookMessage;
use App\Models\Whatsapp\WhatsappMessageStatus;
use Carbon\Carbon;

class HandleStatusUpdateWhatsappWebhook
{
    public function handleStatuses(array $value): void
    {
        foreach ($value['statuses'] ?? [] as $statusData) {

            $waMessageId = $statusData['id'] ?? null;
            $status      = $statusData['status'] ?? null;

            if (!$waMessageId || !$status) {
                continue;
            }

            $statusAt = isset($statusData['timestamp'])
                ? Carbon::createFromTimestamp($statusData['timestamp'])
                : now();

            // Append-only audit
            WhatsappMessageStatus::firstOrCreate(
                [
                    'wa_message_id' => $waMessageId,
                    'status'        => $status,
                ],
                [
                    'status_at'   => $statusAt,
                    'raw_payload' => $statusData,
                ]
            );

            $message = WhatsappWebhookMessage::where('wa_message_id', $waMessageId)
                ->lockForUpdate()
                ->first();

            if (!$message) {
                // Status datang duluan? skip snapshot update
                continue;
            }

            $this->updateMessageSnapshot($message, $status, $statusAt);
        }
    }

    private function updateMessageSnapshot($message, string $status, Carbon $statusAt): void
    {
        switch ($status) {

            case 'sent':
                $message->sent_at ??= $statusAt;
                break;

            case 'delivered':
                $message->delivered_at ??= $statusAt;
                break;

            case 'read':
                $message->read_at ??= $statusAt;
                break;

            case 'failed':
                $message->failed_at ??= $statusAt;
                break;
        }

        $message->last_status = $status;
        $message->save();
    }
}
