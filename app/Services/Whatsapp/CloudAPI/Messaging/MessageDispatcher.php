<?php

namespace App\Services\Whatsapp\CloudAPI\Messaging;

use App\Helpers\LogHelper;
use App\Models\Messaging\MessageEvent;
use App\Services\Whatsapp\CloudAPI\Payloads\MessagePayloadBuilder;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\TemplateHandlerRegistry;
use App\Services\Whatsapp\CloudAPI\Transport\CloudApiClient;
use Illuminate\Support\Str;

class MessageDispatcher
{
    public function __construct(
        private MessagePayloadBuilder $builder,
        private CloudApiClient $client,
        private TemplateHandlerRegistry $registry
    ) {}

    public function dispatch(object $command): MessageResult
    {
        $traceId = $command->traceId ?? Str::uuid()->toString();
        $event = null;

        try {
            // Resolve handler & context
            $handler = $this->registry->resolve($command);
            $context = $handler->buildContext($command);

            // Build payload
            $dto = $this->builder->build($context);
            $payload = $dto->toArray();

            // Create audit event (pending)
            $event = MessageEvent::create([
                'customer_id'  => $command->customerId ?? null,
                'gateway_id'   => $context->account->id,
                'channel'      => 'meta_api',
                'message_type' => $context->templateName,
                'meta'         => $payload,
                'status'       => 'pending',
                'trace_id'     => $traceId,
            ]);

            // Send to Meta
            $response = $this->client->send($context->account, $dto);

            // Update audit event
            $status = ($response['status'] ?? 0) === 200 ? 'success' : 'failed';
            $messageId = data_get($response, 'body.messages.0.id');

            $event->update([
                'status'            => $status,
                'response_code'     => $response['status'] ?? null,
                'response_message'  => $response,
                'message_id'        => $messageId,
                'sent_at'           => now(),
            ]);

            return new MessageResult($dto, $response);
        } catch (\Throwable $e) {

            if ($event) {
                $event->update([
                    'status'           => 'failed',
                    'sent_at'          => now(),
                    'response_code'    => 'EXCEPTION',
                    'response_message' => $e->getMessage(),
                ]);
            }

            // 🔔 LOG HANYA ERROR
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
