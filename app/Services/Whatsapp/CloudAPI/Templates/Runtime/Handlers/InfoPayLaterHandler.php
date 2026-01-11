<?php

namespace App\Services\Whatsapp\CloudAPI\Templates\Runtime\Handlers;

use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands\InfoPayLaterCommand;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands\InfoPembayaranCommand;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Contracts\TemplateHandler as ContractsTemplateHandler;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\TemplateContext;
use InvalidArgumentException;

class InfoPayLaterHandler implements ContractsTemplateHandler
{
    public function buildContext(object $command): TemplateContext
    {
        if (!$command instanceof InfoPayLaterCommand) {
            throw new InvalidArgumentException("Invalid command for InfoPayLaterHandler");
        }

        return new TemplateContext(
            recipient: $command->recipient,
            templateName: 'informasi_bayar_nanti',
            language: 'id',
            values: [
                'customer_name' => $command->customerName,
                'period' => $command->period,
                'teller_name' => $command->tellerName,
                'paylater_code' => $command->paylaterCode
            ],
            account: $command->account
        );
    }
}
