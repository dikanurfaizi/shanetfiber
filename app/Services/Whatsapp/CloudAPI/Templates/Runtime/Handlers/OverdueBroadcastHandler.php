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

        // ===============================================================
        // MAPPING BODY VALUES BERDASARKAN STRUKTUR TEMPLATE (🔒 LOCK)
        // ===============================================================

        // Kasus 1: 2 Bank, 1 Dana/E-wallet (14 Variabel)
        if ($template === 'pembayaran_terlambat_1') {
            $bodyValues = array_merge(
                [$command->packageName, $command->total, $command->lateDays, $command->note],
                $this->getTwoBanksAndOneWallet($command->bankPayload), // 💡 Panggil helper dari trait
                [$command->contactWhatsapp]
            );
        }

        // Kasus 2: 1 Bank, 1 Dana/E-wallet (11 Variabel)
        elseif ($template === 'pembayaran_terlambat_v3') {
            $bodyValues = array_merge(
                [$command->packageName, $command->total, $command->lateDays, $command->note],
                $this->getOneBankAndOneWallet($command->bankPayload), // 💡 Panggil helper dari trait
                [$command->contactWhatsapp]
            );
        }

        // Kasus 3: Hanya 2 Bank (11 Variabel)
        else {
            $bodyValues = array_merge(
                [$command->packageName, $command->total, $command->lateDays, $command->note],
                $this->getTwoBanksOnly($command->bankPayload), // 💡 Panggil helper dari trait
                [$command->contactWhatsapp]
            );
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
