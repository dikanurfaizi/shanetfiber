<?php

namespace App\Services\Whatsapp\CloudAPI\Templates\Runtime\Handlers;

use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands\InfoPembayaranCommand;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Contracts\TemplateHandler as ContractsTemplateHandler;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\TemplateContext;
use InvalidArgumentException;

class InfoPembayaranHandler implements ContractsTemplateHandler
{
    public function buildContext(object $command): TemplateContext
    {
        if (!$command instanceof InfoPembayaranCommand) {
            throw new InvalidArgumentException("Invalid command for InfoPembayaranHandler");
        }

        return new TemplateContext(
            recipient: $command->recipient,
            templateName: 'informasi_bayar',
            language: 'id',
            account: $command->account,

            bodyValues: [
                $command->customerName,
                $command->period,
                $command->amount,
                $command->paymentTime,
                $command->tellerName,
            ],

            buttonValues: [
                0 => [$command->receiptCode],
            ],
        );
    }
}
