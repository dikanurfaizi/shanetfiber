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
            throw new InvalidArgumentException("Invalid command for InfoPayLaterHandler");
        }

        return new TemplateContext(
            recipient: $command->recipient,
            templateName: 'pembayaran_jatuh_tempo_2',
            language: 'id',
            values: [
                'customer_name' => $command->customerName,
                'package' => $command->packageName,
                'total_amount' => $command->total,
                'due_date' => $command->dueDate
            ],
            account: $command->account
        );
    }
}
