<?php

namespace App\Services\Whatsapp\CloudAPI\Templates\Runtime\Handlers;

use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands\CustomerActivationCommand;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Contracts\TemplateHandler as ContractsTemplateHandler;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\TemplateContext;
use InvalidArgumentException;

class CustomerActivationHandler implements ContractsTemplateHandler
{
    public function buildContext(object $command): TemplateContext
    {
        if (!$command instanceof CustomerActivationCommand) {
            throw new InvalidArgumentException("Invalid command for CustomerActivationHandler");
        }

        return new TemplateContext(
            recipient: $command->recipient,
            templateName: 'aktivasi_layanan_pelanggan',
            language: 'id',
            account: $command->account,

            bodyValues: [
                $command->customerName,
                $command->customerId,
                $command->packageName,
                $command->packagePrice,
                $command->memberName,
                $command->adminName,
                $command->adminContact,
                $command->bank,
            ]
        );
    }
}
