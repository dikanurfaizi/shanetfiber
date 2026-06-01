<?php

namespace App\Services\Whatsapp\CloudAPI\Templates\Runtime\Handlers;

use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands\PaymentChannelUpdateCommand;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Contracts\TemplateHandler as ContractsTemplateHandler;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Handlers\Traits\HasPaymentChannelPayload;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\TemplateContext;
use InvalidArgumentException;

class PaymentChannelUpdateHandler implements ContractsTemplateHandler
{
    use HasPaymentChannelPayload;

    public function buildContext(object $command): TemplateContext
    {
        if (!$command instanceof PaymentChannelUpdateCommand) {
            throw new InvalidArgumentException("Invalid command for PaymentChannelUpdateHandler");
        }

        $template = $command->templateName;
        $bodyValues = [];

        // ===============================================================
        // MAPPING BODY VALUES BERDASARKAN STRUKTUR TEMPLATE (🔒 LOCK)
        // ===============================================================

        // Kasus 1: 2 Bank, 1 Dana/E-wallet (14 Variabel)
        if ($template === 'info_perubahan_rekening_pembayaran_2_bank_1_ewallet') {
            $bodyValues = array_merge(
                [$command->customerName, $command->channelUpdateDate, $command->channel],
                $this->getTwoBanksAndOneWallet($command->bankPayload), // 💡 Panggil helper dari trait
                [$command->contactWhatsapp]
            );
        }

        // Kasus 2: 1 Bank, 1 Dana/E-wallet (11 Variabel)
        elseif ($template === 'info_perubahan_rekening_pembayaran_1_bank_1_ewallet') {
            $bodyValues = array_merge(
                [$command->customerName, $command->channelUpdateDate, $command->channel],
                $this->getOneBankAndOneWallet($command->bankPayload), // 💡 Panggil helper dari trait
                [$command->contactWhatsapp]
            );
        }

        // Kasus 3: Hanya 2 Bank (11 Variabel)
        else {
            $bodyValues = array_merge(
                [$command->customerName, $command->channelUpdateDate, $command->channel],
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
