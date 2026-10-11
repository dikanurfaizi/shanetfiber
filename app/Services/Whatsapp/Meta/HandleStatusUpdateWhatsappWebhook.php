<?php

namespace App\Services\WhatsApp\Meta;

use App\Models\WhatsApp\WhatsAppWebhookMessage;
use App\Models\WhatsApp\WhatsAppMessageStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HandleStatusUpdateWhatsAppWebhook
{
    public function handleStatuses(array $value, string $traceId): void
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

            // Append-only audit (idempotent)
            WhatsAppMessageStatus::firstOrCreate(
                [
                    'wa_message_id' => $waMessageId,
                    'status'        => $status,
                ],
                [
                    'status_at'   => $statusAt,
                    'raw_payload' => $statusData,
                ]
            );

            $this->updateSnapshotAtomically($waMessageId, $status, $statusAt);
        }
    }

    private function updateSnapshotAtomically(string $waMessageId, string $status, Carbon $statusAt): void
    {
        $fields = [
            'last_status' => $status,
        ];

        switch ($status) {

            case 'sent':
                $fields['sent_at'] = DB::raw("IFNULL(sent_at, '{$statusAt->toDateTimeString()}')");
                break;

            case 'delivered':
                $fields['delivered_at'] = DB::raw("IFNULL(delivered_at, '{$statusAt->toDateTimeString()}')");
                break;

            case 'read':
                $fields['read_at'] = DB::raw("IFNULL(read_at, '{$statusAt->toDateTimeString()}')");
                break;

            case 'failed':
                $fields['failed_at'] = DB::raw("IFNULL(failed_at, '{$statusAt->toDateTimeString()}')");
                break;
        }

        WhatsAppWebhookMessage::where('wa_message_id', $waMessageId)
            ->update($fields);
    }
}
