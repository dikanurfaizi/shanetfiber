<?php

namespace App\Services\WhatsApp\CloudAPI\Templates\Runtime\Handlers;

use App\Services\WhatsApp\CloudAPI\Templates\Runtime\Commands\InfoPayLaterCommand;
use App\Services\WhatsApp\CloudAPI\Templates\Runtime\Commands\InfoPembayaranCommand;
use App\Services\WhatsApp\CloudAPI\Templates\Runtime\Contracts\TemplateHandler as ContractsTemplateHandler;
use App\Services\WhatsApp\CloudAPI\Templates\Runtime\TemplateContext;
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
            account: $command->account,

            bodyValues: [
                $command->customerName,
                $command->period,
                $command->tellerName,
            ],

            // button index 0, param ke-1
            buttonValues: [
                0 => [$command->paylaterCode],
            ]
        );
    }
}
