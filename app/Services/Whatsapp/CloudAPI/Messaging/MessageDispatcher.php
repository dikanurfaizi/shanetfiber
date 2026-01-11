<?php

namespace App\Services\Whatsapp\CloudAPI\Messaging;

use App\Helpers\LogHelper;
use App\Services\Whatsapp\CloudAPI\Payloads\MessagePayloadBuilder;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\TemplateHandlerRegistry;
use App\Services\Whatsapp\CloudAPI\Transport\CloudApiClient;

class MessageDispatcher
{
    public function __construct(
        private MessagePayloadBuilder $builder,
        private CloudApiClient $client,
        private TemplateHandlerRegistry $registry
    ) {}

    public function dispatch(object $command): MessageResult
    {
        LogHelper::logInfo(null, [
            'command_class' => get_class($command),
            'command' => $command,
        ], 200, "[WA-DISPATCH] Start");

        $handler = $this->registry->resolve($command);

        LogHelper::logInfo(null, [
            'handler_class' => get_class($handler),
        ], 200, "[WA-DISPATCH] Handler resolved");

        $context = $handler->buildContext($command);

        LogHelper::logInfo(null, [
            'template_name' => $context->templateName,
            'language' => $context->language,
            'recipient' => $context->recipient,
            'values' => $context->values,
        ], 200, "[WA-DISPATCH] Context built");

        $dto = $this->builder->build($context);

        LogHelper::logInfo(null, [
            'dto' => $dto->toLogArray(),
        ], 200, "[WA-DISPATCH] DTO built");

        $payload = $dto->toArray();

        LogHelper::logInfo(null, [
            'payload' => $payload,
        ], 200, "[WA-DISPATCH] Payload built");

        $response = $this->client->send($context->account, $dto);

        LogHelper::logInfo(null, [
            'response' => $response,
        ], 200, "[WA-DISPATCH] Meta response");

        return new MessageResult($dto, $response);
    }
}
