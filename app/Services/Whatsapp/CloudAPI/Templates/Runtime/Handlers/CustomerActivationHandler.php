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
            values: [
                'customer_name' => $command->customerName,
                'customer_id' => $command->customerId,
                'package_name' => $command->packageName,
                'package_price' => $command->packagePrice,
                'member_name' => $command->memberName,
                'admin_name' => $command->adminName,
                'admin_contact' => $command->adminContact,
                'bank' => $command->bank
            ],
            account: $command->account
        );
    }
}
