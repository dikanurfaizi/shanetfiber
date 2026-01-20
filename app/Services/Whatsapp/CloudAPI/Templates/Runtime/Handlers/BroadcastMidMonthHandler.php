<?php

namespace App\Services\Whatsapp\CloudAPI\Templates\Runtime\Handlers;

use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands\BroadcastMidMonthCommand;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Contracts\TemplateHandler as ContractsTemplateHandler;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\TemplateContext;
use InvalidArgumentException;

class BroadcastMidMonthHandler implements ContractsTemplateHandler
{
    public function buildContext(object $command): TemplateContext
    {
        if (!$command instanceof BroadcastMidMonthCommand) {
            throw new InvalidArgumentException("Invalid command for BroadcastMidMonthHandler");
        }

        return new TemplateContext(
            recipient: $command->recipient,
            templateName: $command->templateName,
            language: 'id',
            account: $command->account,

            bodyValues: [
                $command->accountData,
                $command->total,
                $command->dueDate,
                $command->note
            ]
        );
    }
}
