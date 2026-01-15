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

        LogHelper::logInfo(null, [
            'command_class' => get_class($command),
            'command' => $command,
        ], 200, "[WA-DISPATCH] Start", $traceId);

        $handler = $this->registry->resolve($command);

        LogHelper::logInfo(null, [
            'handler_class' => get_class($handler),
        ], 200, "[WA-DISPATCH] Handler resolved", $traceId);

        $context = $handler->buildContext($command);

        LogHelper::logInfo(null, [
            'template_name' => $context->templateName,
            'language'      => $context->language,
            'recipient'     => $context->recipient,
            'body_values'   => $context->bodyValues,
            'header_values' => $context->headerValues,
            'button_values' => $context->buttonValues,
        ], 200, "[WA-DISPATCH] Context built", $traceId);


        $dto = $this->builder->build($context);

        LogHelper::logInfo(null, [
            'dto' => $dto->toLogArray(),
        ], 200, "[WA-DISPATCH] DTO built", $traceId);

        $payload = $dto->toArray();

        LogHelper::logInfo(null, [
            'payload' => $payload,
        ], 200, "[WA-DISPATCH] Payload built", $traceId);

        // ======================================================
        // SAVE EVENT BEFORE SEND
        // ======================================================
        $event = MessageEvent::create([
            'customer_id'  => $command->customerId ?? null,
            'channel'      => 'meta_api',
            'message_type' => $context->templateName, // action dari template
            'meta'         => $payload,
            'status'       => 'pending',
            'trace_id'     => $traceId,
        ]);

        LogHelper::logInfo(null, [
            'event_id' => $event->id,
            'status' => 'pending'
        ], 200, "[WA-DISPATCH] MessageEvent created", $traceId);

        // ======================================================
        // SEND TO META
        // ======================================================
        $response = $this->client->send($context->account, $dto);

        LogHelper::logInfo(null, [
            'response' => $response,
        ], 200, "[WA-DISPATCH] Meta response", $traceId);

        // ======================================================
        // UPDATE EVENT AFTER RESPONSE
        // ======================================================
        $status = $response['status'] == 200 ? 'success' : 'failed';

        $messageId = data_get($response, 'body.messages.0.id');

        $event->update([
            'status'       => $status,
            'response_code' => $response['status'],
            'response_message' => $response,
            'message_id'   => $messageId,
            'sent_at' => now()
        ]);

        LogHelper::logInfo(null, [
            'event_id' => $event->id,
            'status' => $status,
            'message_id' => $messageId
        ], 200, "[WA-DISPATCH] MessageEvent updated", $traceId);

        return new MessageResult($dto, $response);
    }
}
