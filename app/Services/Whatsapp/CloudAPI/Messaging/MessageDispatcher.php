<?php

namespace App\Services\WhatsApp\CloudAPI\Messaging;

use App\Helpers\LogHelper;
use App\Models\Messaging\MessageEvent;
use App\Services\WhatsApp\CloudAPI\Payloads\MessagePayloadBuilder;
use App\Services\WhatsApp\CloudAPI\Templates\Runtime\TemplateHandlerRegistry;
use App\Services\WhatsApp\CloudAPI\Transport\CloudApiClientProcess;
use App\Services\WhatsApp\Meta\HandleOutgoingWhatsAppMessage;
use Illuminate\Support\Str;

class MessageDispatcher
{
    public function __construct(
        private MessagePayloadBuilder $builder,
        private CloudApiClientProcess $client,
        private TemplateHandlerRegistry $registry,
        private HandleOutgoingWhatsAppMessage $outgoingWhatsappMessage
    ) {}

    public function dispatch(object $command, ?int $eventId = null): MessageResult
    {
        $traceId = $command->traceId ?? Str::uuid()->toString();
        $event = null;

        try {
            $handler = $this->registry->resolve($command);
            $context = $handler->buildContext($command);

            $dto = $this->builder->build($context);
            $payload = $dto->toArray();

            // =========================
            // Resolve or Create Event
            // =========================
            if ($eventId) {
                $event = MessageEvent::findOrFail($eventId);
                $event->increment('retry_count');
            } else {
                $event = MessageEvent::create([
                    'customer_id'     => $command->customerId ?? null,
                    'gateway_id'      => $context->account->id,
                    'channel'         => 'meta_api',
                    'message_type'    => $context->templateName,
                    'phone_number'    => $context->recipient,
                    'meta'            => $payload,
                    'status'          => 'pending',
                    'stage'           => 'INIT',
                    'request_payload' => $payload,
                    'retry_count'     => 0,
                    'trace_id'        => $traceId,
                ]);
            }

            $event->update([
                'status' => 'pending',
                'stage'  => 'SEND_REQUEST',
            ]);

            // =========================
            // Send
            // =========================
            $response = $this->client->send($context->account, $dto);

            // Cek sukses berdasarkan flag boolean 'success' dan HTTP status 200
            $isSuccess = ($response['success'] ?? false) && ($response['status'] ?? 0) === 200;
            $status = $isSuccess ? 'success' : 'failed';
            $messageId = data_get($response, 'body.messages.0.id');

            if ($status === 'success' && $messageId) {
                $this->outgoingWhatsappMessage->handle(
                    null,
                    $command->recipient,
                    $context->account->defaultNumber->number,
                    $messageId,
                    $payload
                );
            }

            $event->update([
                'status'           => $status,
                'stage'            => $status === 'success' ? 'SUCCESS' : 'FAILED',
                'response_code'    => $response['status'] ?? null,
                'response_message' => $response['message'] ?? null,
                'response_payload' => $response,
                'message_id'       => $messageId,
                'sent_at'          => now(),
                'last_error_at'    => $status === 'failed' ? now() : null,
            ]);

            return new MessageResult($dto, $response);
        } catch (\Throwable $e) {

            if ($event) {
                $event->increment('retry_count');
                $event->update([
                    'status'           => 'failed',
                    'stage'            => 'FAILED_EXCEPTION',
                    'sent_at'          => now(),
                    'response_code'    => 'EXCEPTION',
                    'response_message' => $e->getMessage(),
                    'response_payload' => [
                        'exception' => true,
                        'message'   => $e->getMessage(),
                    ],
                    'last_error_at' => now(),
                ]);
            }

            LogHelper::logError(
                null,
                [
                    'trace_id' => $traceId,
                    'command'  => class_basename($command),
                    'error'    => $e->getMessage(),
                ],
                500,
                '[WA-DISPATCH] Dispatch failed',
                $traceId
            );

            throw $e;
        }
    }
}
