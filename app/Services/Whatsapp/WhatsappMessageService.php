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

        if (!$messageEventId) {
            return;
        }

        /** @var MessageEvent $event */
        $event = MessageEvent::find($messageEventId);

        if (!$event) {
            return;
        }

        $start     = microtime(true);
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
            // INIT
            // =====================
            $payload = [
                'phone'   => $receiver,
                'message' => $message,
            ];

            $event->update([
                'stage'           => 'INIT',
                'request_payload' => $payload,
            ]);

            // =====================
            // RESOLVE GATEWAY
            // =====================
            $event->update(['stage' => 'RESOLVE_GATEWAY']);

            $gateway = WhatsappGateway::where('slug', 'gowa')
                ->where('is_active', true)
                ->first();

            if (!$gateway) {

                $event->update([
                    'stage'            => 'FAILED_NO_GATEWAY',
                    'status'           => 'failed',
                    'response_code'    => 'NO_GATEWAY',
                    'response_message' => 'No active gateway found',
                    'sent_at'          => now(),
                    'last_error_at'    => now(),
                ]);

                return;
            }

            $gatewayId = $gateway->id;

            // =====================
            // SEND REQUEST
            // =====================
            $event->update(['stage' => 'SEND_REQUEST']);

            $resolved = app(GatewayHelper::class)->resolve($gateway);
            $url      = rtrim($resolved['base_url'], '/') . '/send/message';

            $response = Http::withOptions($resolved['options'])
                ->timeout(30)
                ->post($url, $payload);

            $decoded = $response->json();

            $responseCode    = $decoded['code'] ?? null;
            $responseMessage = $decoded['message'] ?? null;
            $messageId       = $decoded['results']['message_id'] ?? null;

            $isSuccess = $responseCode === 'SUCCESS';

            $event->update([
                'gateway_id'       => $gatewayId,
                'stage'            => $isSuccess ? 'SUCCESS' : 'FAILED',
                'status'           => $isSuccess ? 'success' : 'failed',
                'sent_at'          => now(),
                'response_code'    => $responseCode,
                'response_message' => $responseMessage,
                'response_payload' => $decoded,
                'message_id'       => $isSuccess ? $messageId : null,
                'retry_count'      => $event->retry_count + 1,
                'last_error_at'    => $isSuccess ? null : now(),
            ]);
        } catch (Throwable $e) {

            $event->update([
                'gateway_id'       => $gatewayId,
                'stage'            => 'FAILED_EXCEPTION',
                'status'           => 'failed',
                'sent_at'          => now(),
                'response_code'    => $e->getCode() ?: 'EXCEPTION',
                'response_message' => $e->getMessage(),
                'response_payload' => [
                    'exception' => true,
                    'message'   => $e->getMessage(),
                ],
                'retry_count'   => $event->retry_count + 1,
                'last_error_at' => now(),
            ]);

            LogHelper::logError(
                null,
                [
                    'receiver' => $receiver,
                    'error' => [
                        'code'    => $e->getCode(),
                        'message' => $e->getMessage(),
                    ]
                ],
                500,
                "[WhatsappMessageService] Exception occurred",
                $traceId
            );
        }

        LogHelper::logInfo(
            null,
            [
                'trace_id'   => $traceId,
                'gateway_id' => $gatewayId,
                'final_stage' => $event->fresh()->stage,
                'duration'   => round(microtime(true) - $start, 3)
            ],
            200,
            "[WhatsappMessageService] Completed",
            $traceId
        );
    }
}
