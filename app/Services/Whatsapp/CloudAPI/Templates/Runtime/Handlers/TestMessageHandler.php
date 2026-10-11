<?php

namespace App\Services\WhatsApp\CloudAPI\Templates\Runtime\Handlers;

use App\Services\WhatsApp\CloudAPI\Templates\Runtime\Commands\TestMessageCommand;
use App\Services\WhatsApp\CloudAPI\Templates\Runtime\Contracts\TemplateHandler as ContractsTemplateHandler;
use App\Services\WhatsApp\CloudAPI\Templates\Runtime\TemplateContext;
use InvalidArgumentException;

class TestMessageHandler implements ContractsTemplateHandler
{
    public function buildContext(object $command): TemplateContext
    {
        if (!$command instanceof TestMessageCommand) {
            throw new InvalidArgumentException("Invalid command for TestMessageHandler");
        }

        return new TemplateContext(
            recipient: $command->recipient,
            templateName: 'hello_world',
            language: 'en_US',
            account: $command->account,

            bodyValues: [],
            headerValues: [],
            buttonValues: []
        );
    }
}
