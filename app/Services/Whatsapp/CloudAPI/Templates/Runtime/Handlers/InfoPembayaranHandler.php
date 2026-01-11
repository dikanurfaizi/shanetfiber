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
            values: [
                'customer_name' => $command->customerName,
                'period' => $command->period,
                'amount' => $command->amount,
                'payment_time' => $command->paymentTime,
                'teller_name' => $command->tellerName,
                'receipt_code' => $command->receiptCode,
            ],
            account: $command->account
        );
    }
}
