<?php

namespace App\Services\Whatsapp\CloudAPI\Templates\Runtime\Handlers;

use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands\OverdueBroadcastCommand;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Contracts\TemplateHandler as ContractsTemplateHandler;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Handlers\Traits\HasPaymentChannelPayload;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\TemplateContext;
use InvalidArgumentException;

class OverdueBroadcastHandler implements ContractsTemplateHandler
{
    use HasPaymentChannelPayload;

    public function buildContext(object $command): TemplateContext
    {
        if (!$command instanceof OverdueBroadcastCommand) {
            throw new InvalidArgumentException("Invalid command for OverdueBroadcastHandler");
        }

        $template = $command->templateName;
        $bodyValues = [];

        // Base prefix variables: {{1}} = Package Name, {{2}} = Total, {{3}} = Late Days, {{4}} = Note
        $prefixValues = [$command->packageName, $command->total, $command->lateDays, $command->note];

        switch ($template) {
            case 'terlambat_3rek_1ewallet':
                $bodyValues = array_merge(
                    $prefixValues,
                    $this->getThreeBanksAndOneWallet($command->bankPayload),
                    [$command->contactWhatsapp]
                );
                break;

            case 'terlambat_3rek':
                $bodyValues = array_merge(
                    $prefixValues,
                    $this->getThreeBanksOnly($command->bankPayload),
                    [$command->contactWhatsapp]
                );
                break;

            case 'terlambat_2rek_1ewallet':
                $bodyValues = array_merge(
                    $prefixValues,
                    $this->getTwoBanksAndOneWallet($command->bankPayload),
                    [$command->contactWhatsapp]
                );
                break;

            case 'terlambat_2rek':
                $bodyValues = array_merge(
                    $prefixValues,
                    $this->getTwoBanksOnly($command->bankPayload),
                    [$command->contactWhatsapp]
                );
                break;

            case 'terlambat_1rek_1ewallet':
                $bodyValues = array_merge(
                    $prefixValues,
                    $this->getOneBankAndOneWallet($command->bankPayload),
                    [$command->contactWhatsapp]
                );
                break;

            case 'terlambat_1rek':
                $bodyValues = array_merge(
                    $prefixValues,
                    $this->getOneBankOnly($command->bankPayload),
                    [$command->contactWhatsapp]
                );
                break;

            case 'terlambat_1ewallet':
                $bodyValues = array_merge(
                    $prefixValues,
                    $this->getOneWalletOnly($command->bankPayload),
                    [$command->contactWhatsapp]
                );
                break;

            default:
                throw new InvalidArgumentException("Template framework [{$template}] is not registered in OverdueBroadcastHandler.");
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