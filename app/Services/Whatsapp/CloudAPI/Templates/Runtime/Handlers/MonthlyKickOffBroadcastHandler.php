<?php

namespace App\Services\WhatsApp\CloudAPI\Templates\Runtime\Handlers;

use App\Services\WhatsApp\CloudAPI\Templates\Runtime\Commands\MonthlyKickOffBroadcastCommand;
use App\Services\WhatsApp\CloudAPI\Templates\Runtime\Contracts\TemplateHandler as ContractsTemplateHandler;
use App\Services\WhatsApp\CloudAPI\Templates\Runtime\Handlers\Traits\HasPaymentChannelPayload;
use App\Services\WhatsApp\CloudAPI\Templates\Runtime\TemplateContext;
use InvalidArgumentException;

class MonthlyKickOffBroadcastHandler implements ContractsTemplateHandler
{
    use HasPaymentChannelPayload;

    public function buildContext(object $command): TemplateContext
    {
        if (!$command instanceof MonthlyKickOffBroadcastCommand) {
            throw new InvalidArgumentException("Invalid command for MonthlyKickOffBroadcastHandler");
        }

        $template = $command->templateName;
        $bodyValues = [];

        // Base prefix variables: {{1}} = Customer Name, {{2}} = Channel/Package, {{3}} = Initial Date
        $prefixValues = [$command->customerName, $command->channel, $command->initialDate];

        switch ($template) {
            case 'info_billing_3rek_1ewallet':
                $bodyValues = array_merge(
                    $prefixValues,
                    $this->getThreeBanksAndOneWallet($command->bankPayload),
                    [$command->contactWhatsapp]
                );
                break;

            case 'info_billing_3rek':
                $bodyValues = array_merge(
                    $prefixValues,
                    $this->getThreeBanksOnly($command->bankPayload),
                    [$command->contactWhatsapp]
                );
                break;

            case 'info_billing_2rek_1ewallet':
                $bodyValues = array_merge(
                    $prefixValues,
                    $this->getTwoBanksAndOneWallet($command->bankPayload),
                    [$command->contactWhatsapp]
                );
                break;

            case 'info_billing_2rek':
                $bodyValues = array_merge(
                    $prefixValues,
                    $this->getTwoBanksOnly($command->bankPayload),
                    [$command->contactWhatsapp]
                );
                break;

            case 'info_billing_1rek_1ewallet':
                $bodyValues = array_merge(
                    $prefixValues,
                    $this->getOneBankAndOneWallet($command->bankPayload),
                    [$command->contactWhatsapp]
                );
                break;

            case 'info_billing_1rek':
                $bodyValues = array_merge(
                    $prefixValues,
                    $this->getOneBankOnly($command->bankPayload),
                    [$command->contactWhatsapp]
                );
                break;

            case 'info_billing_1ewallet':
                $bodyValues = array_merge(
                    $prefixValues,
                    $this->getOneWalletOnly($command->bankPayload),
                    [$command->contactWhatsapp]
                );
                break;

            default:
                throw new InvalidArgumentException("Template framework [{$template}] is not registered in MonthlyKickOffBroadcastHandler.");
        }

        return new TemplateContext(
            recipient: $command->recipient,
            templateName: $template,
            language: 'id',
            account: $command->account,
            bodyValues: $bodyValues
        );
    }
}
