<?php

namespace App\Services\Whatsapp\Meta;

use App\Models\Whatsapp\WhatsappConversation;
use App\Models\Whatsapp\WhatsappMessageMedia;
use App\Models\Whatsapp\WhatsappWebhookMessage;
use App\Models\Whatsapp\WhatsappMessageStatus;
use App\Jobs\Whatsapp\DownloadWhatsappMediaJob;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Database\QueryException;

class HandleIncomingWhatsappWebhook
{
    public function handleMessages(array $value, string $traceId): void
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
                $message = WhatsappWebhookMessage::create([
                    'conversation_id' => $conversation->id,
                    'wa_message_id'   => $waMessageId,
                    'trace_id'        => $traceId,
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

                $this->handleMediaIfExists($message, $messageData, $traceId);
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

    private function handleMediaIfExists(
        WhatsappWebhookMessage $message,
        array $messageData,
        string $traceId
    ): void {

        $type = $messageData['type'] ?? null;

        if (!in_array($type, ['image', 'document', 'audio', 'video'])) {
            return;
        }

        $mediaData = $messageData[$type] ?? null;

        if (!$mediaData || empty($mediaData['id'])) {
            return;
        }

        $media = WhatsappMessageMedia::create([
            'message_id'      => $message->id,
            'wa_media_id'     => $mediaData['id'],
            'file_name'       => $mediaData['filename'] ?? null,
            'mime_type'       => $mediaData['mime_type'] ?? 'application/octet-stream',
            'sha256'          => $mediaData['sha256'] ?? null,
            'storage_path'    => '',
            'download_status' => 'pending',
        ]);

        // Dispatch async download
        DownloadWhatsappMediaJob::dispatch($media->id, $traceId)
            ->onQueue('default')
            ->delay(now()->addSeconds(5)); // kasih jeda dikit biar WhatsApp siapin media-nya dulu
    }
}
