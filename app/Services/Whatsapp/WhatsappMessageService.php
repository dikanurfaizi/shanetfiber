<?php

namespace App\Services\Whatsapp;

use App\Helpers\GatewayHelper;
use App\Helpers\LogHelper;
use App\Models\Messaging\MessageEvent;
use App\Models\Whatsapp\WhatsappGateway;
use Illuminate\Support\Facades\Http;
use Throwable;

class WhatsappMessageService
{
    public function send(
        string $messageType,
        string $receiver,
        string $message,
        ?string $customerId,
        string $traceId,
        ?int $messageEventId = null
    ): void {
        $stage = 'INIT';
        $start = microtime(true);
        $gatewayId = null;

        LogHelper::logInfo(
            null,
            compact('messageType', 'receiver', 'traceId'),
            200,
            "[WhatsappMessageService] Started",
            $traceId
        );

        try {

            // =====================
            // 1. RESOLVE GATEWAY
            // =====================
            $stage = 'RESOLVE_GATEWAY';

            $gateway = WhatsappGateway::where('slug', 'gowa')
                ->where('is_active', true)
                ->first();

            if (! $gateway) {
                MessageEvent::where('id', $messageEventId)->update([
                    'gateway_id' => null,
                    'status' => 'failed',
                    'response_code' => 'NO_GATEWAY',
                    'response_message' => 'No active gateway found',
                    'sent_at' => now(),
                ]);

                LogHelper::logWarning(
                    null,
                    compact('traceId', 'receiver', 'messageType'),
                    404,
                    '[WhatsappMessageService] No active gateway found',
                    $traceId
                );

                return;
            }

            $gatewayId = $gateway->id;

            // =====================
            // 2. PREPARE REQUEST
            // =====================
            $stage = 'SEND_REQUEST';

            $resolved = app(GatewayHelper::class)->resolve($gateway);
            $url = rtrim($resolved['base_url'], '/') . '/send/message';

            $response = Http::withOptions($resolved['options'])
                ->post($url, [
                    'phone' => $receiver,
                    'message' => $message,
                ]);

            $decoded = json_decode($response->getBody()->getContents(), true);

            $responseCode = $decoded['code'] ?? null;
            $responseMessage = $decoded['message'] ?? null;
            $messageId = $decoded['results']['message_id'] ?? null;

            // =====================
            // 3. UPDATE MESSAGE EVENT
            // =====================
            $stage = 'UPDATE_EVENT';

            MessageEvent::where('id', $messageEventId)->update([
                'gateway_id'     => $gatewayId,
                'status'         => $responseCode === 'SUCCESS' ? 'success' : 'failed',
                'sent_at'        => now(),
                'response_code'  => $responseCode,
                'response_message' => $responseMessage,
                'message_id'     => $responseCode === 'SUCCESS' ? $messageId : null,
            ]);

            $stage = 'COMPLETE';
        } catch (Throwable $e) {

            // =====================
            // 4. HANDLE FAILURE
            // =====================
            MessageEvent::where('id', $messageEventId)->update([
                'gateway_id'       => $gatewayId,
                'status'           => 'failed',
                'sent_at'          => now(),
                'response_code'    => $e->getCode(),
                'response_message' => $e->getMessage(),
            ]);

            LogHelper::logError(
                null,
                [
                    'receiver' => $receiver,
                    'error' => [
                        'code' => $e->getCode(),
                        'message' => $e->getMessage()
                    ]
                ],
                500,
                "[WhatsappMessageService] Exception at stage {$stage}",
                $traceId
            );
        }

        LogHelper::logInfo(
            null,
            [
                'trace_id' => $traceId,
                'gateway_id' => $gatewayId,
                'stage' => $stage,
                'duration' => round(microtime(true) - $start, 3)
            ],
            200,
            "[WhatsappMessageService] Completed",
            $traceId
        );
    }
}
