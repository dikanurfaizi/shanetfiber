<?php

namespace App\Services\Whatsapp\CloudAPI\Templates\Runtime\Handlers;

use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands\TestMessageCommand;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Contracts\TemplateHandler as ContractsTemplateHandler;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\TemplateContext;
use InvalidArgumentException;

class TestMessageHandler implements ContractsTemplateHandler
{
    public function buildContext(object $command): TemplateContext
    {
        if (!$command instanceof TestMessageCommand) {
            throw new InvalidArgumentException("Invalid command for InfoPayLaterHandler");
        }

        return new TemplateContext(
            recipient: $command->recipient,
            templateName: 'hello_world',
            language: 'en_US',
            values: [],
            account: $command->account
        );
    }
}
