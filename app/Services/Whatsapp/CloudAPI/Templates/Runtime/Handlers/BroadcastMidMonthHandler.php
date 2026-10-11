<?php

namespace App\Services\WhatsApp\CloudAPI\Templates\Runtime\Handlers;

use App\Services\WhatsApp\CloudAPI\Templates\Runtime\Commands\BroadcastMidMonthCommand;
use App\Services\WhatsApp\CloudAPI\Templates\Runtime\Contracts\TemplateHandler as ContractsTemplateHandler;
use App\Services\WhatsApp\CloudAPI\Templates\Runtime\Handlers\Traits\HasPaymentChannelPayload;
use App\Services\WhatsApp\CloudAPI\Templates\Runtime\TemplateContext;
use InvalidArgumentException;

class BroadcastMidMonthHandler implements ContractsTemplateHandler
{
    use HasPaymentChannelPayload;

    public function buildContext(object $command): TemplateContext
    {
        if (!$command instanceof BroadcastMidMonthCommand) {
            throw new InvalidArgumentException("Invalid command for BroadcastMidMonthHandler");
        }

        $template = $command->templateName;
        $bodyValues = [];

        // Base prefix variables: {{1}} = Account Data, {{2}} = Total, {{3}} = Due Date
        $prefixValues = [$command->accountData, $command->total, $command->dueDate];

        switch ($template) {
            case 'pengingat_3rek_1ewallet':
                $bodyValues = array_merge(
                    $prefixValues,
                    $this->getThreeBanksAndOneWallet($command->bankPayload),
                    [$command->contactWhatsapp]
                );
                break;

            case 'pengingat_3rek':
                $bodyValues = array_merge(
                    $prefixValues,
                    $this->getThreeBanksOnly($command->bankPayload),
                    [$command->contactWhatsapp]
                );
                break;

            case 'pengingat_2rek_1ewallet':
                $bodyValues = array_merge(
                    $prefixValues,
                    $this->getTwoBanksAndOneWallet($command->bankPayload),
                    [$command->contactWhatsapp]
                );
                break;

            case 'pengingat_2rek':
                $bodyValues = array_merge(
                    $prefixValues,
                    $this->getTwoBanksOnly($command->bankPayload),
                    [$command->contactWhatsapp]
                );
                break;

            case 'pengingat_1rek_1ewallet':
                $bodyValues = array_merge(
                    $prefixValues,
                    $this->getOneBankAndOneWallet($command->bankPayload),
                    [$command->contactWhatsapp]
                );
                break;

            case 'pengingat_1rek':
                $bodyValues = array_merge(
                    $prefixValues,
                    $this->getOneBankOnly($command->bankPayload),
                    [$command->contactWhatsapp]
                );
                break;

            case 'pengingat_1ewallet':
                $bodyValues = array_merge(
                    $prefixValues,
                    $this->getOneWalletOnly($command->bankPayload),
                    [$command->contactWhatsapp]
                );
                break;

            default:
                throw new InvalidArgumentException("Template framework [{$template}] is not registered in BroadcastMidMonthHandler.");
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
