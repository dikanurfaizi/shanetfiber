<?php

namespace App\Services\Whatsapp\CloudAPI\Templates\Runtime\Handlers;

use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands\PaymentChannelUpdateCommand;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Contracts\TemplateHandler as ContractsTemplateHandler;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\TemplateContext;
use InvalidArgumentException;

class PaymentChannelUpdateHandler implements ContractsTemplateHandler
{
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
        if ($template === 'info_perubahan_rekening_pembayaran') {
            $bodyValues = [
                $command->channel,                               // {{1}} Nama layanan / Nomor akun
                $command->customerName,                          // {{2}} Nominal tagihan (Atau nama pelengkap sesuai skema)
                '3',                                             // {{3}} Hari keterlambatan (Bisa di-hardcode / dibuat dinamis)
                'biaya keterlambatan',                           // {{4}} Konsekuensi teks
                $command->bankPayload['bank1_name'] ?? '-',      // {{5}} Bank 1 Name
                $command->bankPayload['bank1_no'] ?? '-',        // {{6}} Bank 1 No. Rekening
                $command->bankPayload['bank1_an'] ?? '-',        // {{7}} Bank 1 Atas Nama
                $command->bankPayload['bank2_name'] ?? '-',      // {{8}} Bank 2 Name
                $command->bankPayload['bank2_no'] ?? '-',        // {{9}} Bank 2 No. Rekening
                $command->bankPayload['bank2_an'] ?? '-',        // {{10}} Bank 2 Atas Nama
                $command->bankPayload['wallet_name'] ?? 'DANA',  // {{11}} E-Wallet Name
                $command->bankPayload['wallet_no'] ?? '-',        // {{12}} E-Wallet Nomor
                $command->bankPayload['wallet_an'] ?? '-',        // {{13}} E-Wallet Atas Nama
                $command->contactWhatsapp,                       // {{14}} Nomor WhatsApp Alternatif Admin
            ];
        }

        // Kasus 2: 1 Bank, 1 Dana/E-wallet (11 Variabel)
        elseif ($template === 'info_perubahan_rekening_pembayaran_1_bank_1_ewallet') {
            $bodyValues = [
                $command->channel,                               // {{1}} Nama layanan / Nomor akun
                $command->customerName,                          // {{2}} Nominal tagihan
                '3',                                             // {{3}} Hari keterlambatan
                'biaya keterlambatan',                           // {{4}} Konsekuensi teks
                $command->bankPayload['bank1_name'] ?? '-',      // {{5}} Bank 1 Name
                $command->bankPayload['bank1_no'] ?? '-',        // {{6}} Bank 1 No. Rekening
                $command->bankPayload['bank1_an'] ?? '-',        // {{7}} Bank 1 Atas Nama
                $command->bankPayload['wallet_name'] ?? 'DANA',  // {{8}} E-Wallet Name
                $command->bankPayload['wallet_no'] ?? '-',        // {{9}} E-Wallet Nomor
                $command->bankPayload['wallet_an'] ?? '-',        // {{10}} E-Wallet Atas Nama
                $command->contactWhatsapp,                       // {{11}} Nomor WhatsApp Alternatif Admin
            ];
        }

        // Kasus 3: Hanya 2 Bank (11 Variabel)
        else {
            // Fallback default untuk 'overdue_reminder_2bank' atau 'info_perubahan_rekening_2_bank'
            $bodyValues = [
                $command->channel,                               // {{1}} Nama layanan / Nomor akun
                $command->customerName,                          // {{2}} Nominal tagihan
                '3',                                             // {{3}} Hari keterlambatan
                'biaya keterlambatan',                           // {{4}} Konsekuensi teks
                $command->bankPayload['bank1_name'] ?? '-',      // {{5}} Bank 1 Name
                $command->bankPayload['bank1_no'] ?? '-',        // {{6}} Bank 1 No. Rekening
                $command->bankPayload['bank1_an'] ?? '-',        // {{7}} Bank 1 Atas Nama
                $command->bankPayload['bank2_name'] ?? '-',      // {{8}} Bank 2 Name
                $command->bankPayload['bank2_no'] ?? '-',        // {{9}} Bank 2 No. Rekening
                $command->bankPayload['bank2_an'] ?? '-',        // {{10}} Bank 2 Atas Nama
                $command->contactWhatsapp,                       // {{11}} Nomor WhatsApp Alternatif Admin
            ];
        }

        // Return ke TemplateContext bawaan framework CloudAPI Anda
        return new TemplateContext(
            recipient: $command->recipient,
            templateName: $template,
            language: 'id',
            account: $command->account,
            bodyValues: $bodyValues
        );
    }
}
