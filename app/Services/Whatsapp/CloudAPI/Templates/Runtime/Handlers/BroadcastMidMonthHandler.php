<?php

namespace App\Services\Whatsapp\CloudAPI\Templates\Runtime\Handlers;

use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands\BroadcastMidMonthCommand;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Contracts\TemplateHandler as ContractsTemplateHandler;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Handlers\Traits\HasPaymentChannelPayload;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\TemplateContext;
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

        // ===============================================================
        // MAPPING BODY VALUES BERDASARKAN STRUKTUR TEMPLATE (🔒 LOCK)
        // ===============================================================

        // Kasus 1: 2 Bank, 1 Dana/E-wallet (13 Variabel)
        if ($template === 'pengingat_pembayaran_v3') {
            $bodyValues = array_merge(
                [$command->accountData, $command->total, $command->dueDate],
                $this->getTwoBanksAndOneWallet($command->bankPayload), // 💡 Panggil helper dari trait
                [$command->contactWhatsapp]
            );
        }

        // Kasus 2: 1 Bank, 1 Dana/E-wallet (10 Variabel)
        elseif ($template === 'broadcast_tengah_bulan_1_bank_1_ewallet') {
            $bodyValues = array_merge(
                [$command->accountData, $command->total, $command->dueDate],
                $this->getOneBankAndOneWallet($command->bankPayload), // 💡 Panggil helper dari trait
                [$command->contactWhatsapp]
            );
        }

        // Kasus 3: Hanya 2 Bank (10 Variabel)
        else {
            $bodyValues = array_merge(
                [$command->accountData, $command->total, $command->dueDate],
                $this->getTwoBanksOnly($command->bankPayload), // 💡 Panggil helper dari trait
                [$command->contactWhatsapp]
            );
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
