<?php

namespace App\Services\Whatsapp\CloudAPI\Templates\Runtime\Handlers;

use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands\OverdueBroadcastCommand;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Contracts\TemplateHandler as ContractsTemplateHandler;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\TemplateContext;
use InvalidArgumentException;

class OverdueBroadcastHandler implements ContractsTemplateHandler
{
    public function buildContext(object $command): TemplateContext
    {
        if (!$command instanceof OverdueBroadcastCommand) {
            throw new InvalidArgumentException("Invalid command for OverdueBroadcastHandler");
        }

        return new TemplateContext(
            recipient: $command->recipient,
            templateName: $command->templateName,
            language: 'id',
            account: $command->account,

            bodyValues: [
                $command->packageName,
                $command->total,
                $command->lateDays,
                $command->note
            ]
        );
    }
}
