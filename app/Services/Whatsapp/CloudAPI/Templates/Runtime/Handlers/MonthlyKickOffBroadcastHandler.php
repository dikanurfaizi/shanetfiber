<?php

namespace App\Services\Whatsapp\CloudAPI\Templates\Runtime\Handlers;

use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands\MonthlyKickOffBroadcastCommand;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Contracts\TemplateHandler as ContractsTemplateHandler;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Handlers\Traits\HasPaymentChannelPayload;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\TemplateContext;
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

        // Base prefix variables yang selalu ada di awal setiap template billing:
        // {{1}} = Customer Name, {{2}} = Channel/Package Name, {{3}} = Billing/Update Date
        $prefixValues = [$command->customerName, $command->channel, $command->initialDate];

        // ===============================================================
        // 💡 MAPPING 5 TEMPLATE SECARA DINAMIS
        // ===============================================================
        switch ($template) {
            // Kasus 1: 2 Bank, 1 E-wallet (13 Variabel)
            case 'info_billing_2rek_1ewallet':
                $bodyValues = array_merge(
                    $prefixValues,
                    $this->getTwoBanksAndOneWallet($command->bankPayload),
                    [$command->contactWhatsapp] // Variabel Terakhir {{13}}
                );
                break;

            // Kasus 2: 1 Bank, 1 E-wallet (10 Variabel)
            case 'info_billing_1rek_1ewallet':
                $bodyValues = array_merge(
                    $prefixValues,
                    $this->getOneBankAndOneWallet($command->bankPayload),
                    [$command->contactWhatsapp] // Variabel Terakhir {{10}}
                );
                break;

            // Kasus 3: Hanya 2 Bank (10 Variabel)
            case 'info_billing_2rek':
                $bodyValues = array_merge(
                    $prefixValues,
                    $this->getTwoBanksOnly($command->bankPayload),
                    [$command->contactWhatsapp] // Variabel Terakhir {{10}}
                );
                break;

            // Kasus 4: Hanya 1 Bank (7 Variabel)
            case 'info_billing_1rek':
                $bodyValues = array_merge(
                    $prefixValues,
                    $this->getOneBankOnly($command->bankPayload),
                    [$command->contactWhatsapp] // Variabel Terakhir {{7}}
                );
                break;

            // Kasus 5: Hanya 1 E-Wallet (7 Variabel)
            case 'info_billing_1ewallet':
                $bodyValues = array_merge(
                    $prefixValues,
                    $this->getOneWalletOnly($command->bankPayload),
                    [$command->contactWhatsapp] // Variabel Terakhir {{7}}
                );
                break;

            // Fallback safety net jika ada template tak dikenal lewat ke handler ini
            default:
                throw new InvalidArgumentException("Template framework [{$template}] is not registered in MonthlyKickOffBroadcastHandler.");
        }

        // Return ke TemplateContext bawaan framework CloudAPI
        return new TemplateContext(
            recipient: $command->recipient,
            templateName: $template,
            language: 'id',
            account: $command->account,
            bodyValues: $bodyValues
        );
    }
}
