<?php

namespace App\Services\Whatsapp\CloudAPI\Broadcast;

use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Helpers\MaskCustomerName;
use App\Jobs\Whatsapp\CloudAPI\Broadcast\PaymentChannelUpdateJob;
use App\Models\Whatsapp\WhatsappGateway;
use App\Models\Whatsapp\WhatsappNumber;
use App\Services\Whatsapp\CloudAPI\Collectors\ActiveCustomerCollector;
use App\Services\Whatsapp\CloudAPI\Collectors\AdminBankCollector;
use App\Services\Whatsapp\CloudAPI\Messages\TemplateMessageService;
use Carbon\Carbon;

class PaymentChannelUpdate
{
    public function __construct(
        private ActiveCustomerCollector $collector,
        private AdminBankCollector $bankCollector,
        private TemplateMessageService $templateMessageService
    ) {}

    public function run(string $traceId)
    {
        LogHelper::logInfo(null, [], 100, '[PaymentChannelUpdate] Start process', $traceId);

        // Inisialisasi 3 Jenis Nama Template Meta Aktif Anda
        $templateName1 = 'info_perubahan_rekening_pembayaran';
        $templateName2 = 'info_perubahan_rekening_pembayaran_1_bank_1_ewallet';
        $templateName3 = 'info_perubahan_rekening_pembayaran_2_bank';

        // Koleksi data pelanggan multi-slug antiduplikasi
        $customers  = $this->collector->collect(now(), $templateName1, $templateName2, $templateName3);
        $totalCount = $customers->count();

        LogHelper::logInfo(null, ['count' => $totalCount], 110, '[PaymentChannelUpdate] Customers collected', $traceId);

        if ($customers->isEmpty()) {
            return;
        }

        // ===============================================================
        // RESOLVE GATEWAY & ACCOUNT
        // ===============================================================
        $gateway = WhatsappGateway::where('type', 'cloud')
            ->where('is_active', true)
            ->with(['accounts' => function ($q) {
                $q->where('is_active', true);
            }])
            ->first();

        if (!$gateway || $gateway->accounts->isEmpty()) {
            LogHelper::logError(null, [], 120, '[PaymentChannelUpdate] Gateway/Account invalid', $traceId);
            return;
        }

        $account = $gateway->accounts->first();

        // ===============================================================
        // RESOLVE GLOBAL ADMIN CONTACT
        // ===============================================================
        $formatter = new FormatReceiver;

        $whatsappNumberRecord = WhatsappNumber::where('is_enabled', true)
            ->where('is_default', true)
            ->whereHas('account', function ($query) {
                $query->where('is_active', 1)
                    ->whereHas('gateway', function ($q) {
                        $q->where('slug', 'gowa');
                    });
            })
            ->first();

        $adminNumber     = $whatsappNumberRecord ? $whatsappNumberRecord->number : '085783335333';
        $contactAdmin    = $formatter->formatReceiver($adminNumber, 'broadcast', false);
        $contactWhatsapp = $formatter->formatReceiver($adminNumber, null, false);

        // ===============================================================
        // PROCESS ENGINE LOOP
        // ===============================================================
        $delayInSeconds    = 0;
        $delay             = now();
        $channelUpdateDate = '1 Juni 2026';
        $channel           = 'Shanetfiber';

        // 🔍 MEMBACA APAKAH ADA OVERRIDE NOMOR DARI .ENV UNTUK GLOBAL SANDBOX
        $overrideNumber = config('messaging.phone_number_override');

        foreach ($customers as $idx => $data) {

            // 1. Ambil info bank terhubung ke Admin (User) via Pivot bank_users
            $adminBanks = $this->bankCollector->getBanksByUserId($data->user_id ?? 0);

            // 2. Pisahkan kategori Bank vs E-Wallet
            $banks    = $adminBanks->where('type', 'bank')->values();
            $ewallets = $adminBanks->where('type', 'ewallet')->values();

            // 3. Routing Engine Menentukan Nama Template Sesuai Sistem Anda (🔒 DIKUNCI)
            $resolvedTemplate = $templateName1;
            $bankPayload      = [];

            if ($banks->count() >= 2 && $ewallets->count() >= 1) {
                // Kasus 1: 2 Bank, 1 Dana/E-wallet
                $resolvedTemplate = $templateName1;
                $bankPayload = [
                    'bank1_name'  => $banks[0]->bank,
                    'bank1_no'    => $banks[0]->account_number,
                    'bank1_an'    => $banks[0]->name,
                    'bank2_name'  => $banks[1]->bank,
                    'bank2_no'    => $banks[1]->account_number,
                    'bank2_an'    => $banks[1]->name,
                    'wallet_name' => $ewallets[0]->bank,
                    'wallet_no'   => $ewallets[0]->account_number,
                    'wallet_an'   => $ewallets[0]->name,
                ];
            } elseif ($banks->count() == 1 && $ewallets->count() >= 1) {
                // Kasus 2: 1 Bank, 1 Dana/E-wallet
                $resolvedTemplate = $templateName2;
                $bankPayload = [
                    'bank1_name'  => $banks[0]->bank,
                    'bank1_no'    => $banks[0]->account_number,
                    'bank1_an'    => $banks[0]->name,
                    'wallet_name' => $ewallets[0]->bank,
                    'wallet_no'   => $ewallets[0]->account_number,
                    'wallet_an'   => $ewallets[0]->name,
                ];
            } else {
                // Kasus 3: Hanya 2 Bank
                $resolvedTemplate = $templateName3;
                $b1 = $banks->get(0);
                $b2 = $banks->get(1) ?? $b1;

                $bankPayload = [
                    'bank1_name' => $b1->bank ?? '-',
                    'bank1_no'   => $b1->account_number ?? '-',
                    'bank1_an'   => $b1->name ?? '-',
                    'bank2_name' => $b2->bank ?? '-',
                    'bank2_no'   => $b2->account_number ?? '-',
                    'bank2_an'   => $b2->name ?? '-',
                ];
            }

            // 4. Formatting nomor ponsel penerima (Serta Intersep Sandbox jika aktif)
            $targetContact = $overrideNumber ? (string) $overrideNumber : $data->customer_contact;
            $recipient     = $formatter->formatReceiver($targetContact, null, false);

            LogHelper::logDebug(null, [
                'index'             => $idx,
                'customer_id'       => $data->customer_id,
                'original_receiver' => $data->customer_contact,
                'final_recipient'   => $recipient,
                'template'          => $resolvedTemplate,
            ], 200, '[PaymentChannelUpdate] Route template resolved and filtered', $traceId);

            // Memicu pengiriman antrean via Job (Urutan parameter sinkron penuh)
            PaymentChannelUpdateJob::dispatch(
                account: $account,
                templateName: $resolvedTemplate,
                customerId: (string) $data->customer_id,
                customerName: $data->customer_name,
                contact: $recipient, // 👈 Sudah aman & terformat rapi (contoh: 628983249628)
                channelUpdateDate: $channelUpdateDate,
                channel: $channel,
                bankPayload: $bankPayload,
                contactAdmin: $contactAdmin,
                contactWhatsapp: $contactWhatsapp
            )
                ->delay($delay)
                ->onQueue('notification');

            $delayInSeconds += 2;
            $delay = now()->addSeconds($delayInSeconds);
        }

        LogHelper::logInfo(null, ['total_dispatched' => $totalCount], 900, '[PaymentChannelUpdate] Finished process', $traceId);
    }
}
