<?php

namespace App\Services\Notification;

use App\Enums\WhatsAppAction;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Jobs\Whatsapp\ProcessPaymentChannelUpdateJob;
use App\Models\Messaging\MessageEvent;
use App\Services\Whatsapp\CloudAPI\Broadcast\PaymentChannelUpdate;
use App\Services\Whatsapp\WhatsappGatewayResolver;
use App\Services\Whatsapp\GowaService;
use App\Services\Messaging\Collectors\ChannelUpdateCustomerCollector;
use App\Services\Messaging\Collectors\AdminBankCollector;
use App\Services\Notification\Traits\MapsBroadcastBankPayload;
use Illuminate\Support\Carbon;

class PaymentChannelUpdateNotificationService
{
    use MapsBroadcastBankPayload;

    public function __construct(
        private PaymentChannelUpdate $paymentChannelUpdate,
        private GowaService $gowaService,
        private WhatsappGatewayResolver $gatewayResolver,
        private ChannelUpdateCustomerCollector $channelUpdateCustomerCollector,
        private AdminBankCollector $bankCollector
    ) {}

    public function run(string $traceId): void
    {
        try {
            LogHelper::logInfo(
                null,
                ['service' => 'PaymentChannelUpdateNotificationService'],
                200,
                'Starting payment channel update notifications',
                $traceId
            );

            [$gateway, $account, $number] = $this->gatewayResolver->resolveActiveGatewayAndAccount($traceId, 'update_rekening_juni_2026')
                ?? [null, null, null];

            if (!$gateway || !$account || !$number) {
                return;
            }

            $formatter = new FormatReceiver();
            $adminNumber = $formatter->formatReceiver(
                $number->number,
                null,
                false
            );

            $testMode = config('messaging.reminder_allow_test', false);

            LogHelper::logInfo(
                null,
                [
                    'gateway' => $gateway->name ?? 'N/A',
                    'account' => $account->name ?? 'N/A',
                    'number'  => $number->number ?? 'N/A',
                ],
                200,
                'Resolved gateway, account, and number for payment channel update',
                $traceId
            );

            if ($gateway->type === 'cloud') {
                $this->paymentChannelUpdate->run($traceId);
            } else {
                $deviceId = $number->device_id;

                // --- Cukup Menggunakan 1 Slug Utama ---
                $today = now()->startOfDay();
                $mainSlug =  WhatsAppAction::PAYMENT_CHANNEL_UPDATE->value;

                // Mengirimkan slug yang sama ke collector agar filter anti-double konsisten
                $customers = $this->channelUpdateCustomerCollector->collect($today, $mainSlug);

                $delayInSeconds = 0;
                $delayInterval = 300; // Jeda aman 5 menit per customer untuk WA Web

                $overrideNumber = config('messaging.phone_number_override', null);

                foreach ($customers as $data) {

                    $adminBanks = $this->bankCollector->getBanksByUserId($data->user_id ?? 0);

                    if ($adminBanks->isEmpty()) {
                        continue;
                    }

                    $banks    = $adminBanks->where('type', 'bank')->values();
                    $ewallets = $adminBanks->where('type', 'ewallet')->values();

                    $bankResolution = $this->resolveBankTemplateAndPayload($banks, $ewallets, $mainSlug);

                    $bankPayload = array_merge($bankResolution['payload'], [
                        'customer_name'   => $data->customer_name,
                        'services'        => $data->package_name ?? 'Shanetfiber',
                        'effective_date'  => Carbon::parse('2026-06-01')->translatedFormat('d F Y'),
                        'whatsapp_number' => $adminNumber ?? '-'
                    ]);

                    $targetContact = $overrideNumber ? (string) $overrideNumber : $data->customer_contact;
                    $recipient = $targetContact;

                    // Buat Track Record di MessageEvent terlebih dahulu
                    $event = MessageEvent::create([
                        'customer_id'     => $data->customer_id ?? null,
                        'gateway_id'      => $account->id,
                        'channel'         => 'go_whatsapp',
                        'message_type'    => $mainSlug,
                        'meta'            => json_encode($bankPayload),
                        'status'          => 'pending',
                        'stage'           => 'INIT',
                        'request_payload' => json_encode($bankPayload),
                        'retry_count'     => 0,
                        'trace_id'        => $traceId,
                    ]);

                    // 👉 Hitung delay waktu rilis job (Maju 5 menit / 300 detik tiap iterasi)
                    $targetReleaseTime = now()->addSeconds($delayInSeconds);

                    // 👉 3. Kirim via Laravel Job dengan Delay Gradual
                    // 3.1. Inisialisasi awal instance Job Builder (Jangan langsung di-chaining dahulu)
                    $jobInstance = ProcessPaymentChannelUpdateJob::dispatch(
                        eventId: (string) $event->id,
                        deviceId: (string) $deviceId,
                        traceId: $traceId,
                        recipient: $recipient,
                        bankPayload: $bankPayload,
                        mainSlug: $mainSlug
                    );

                    // 3.2. Tentukan nama queue tujuan secara eksplisit
                    $jobInstance->onQueue('notification');

                    // 3.3. Suntikkan delay gradual murni hanya jika BUKAN test mode (Production)
                    if (!$testMode) {
                        $targetReleaseTime = now()->addSeconds($delayInSeconds);
                        $jobInstance->delay($targetReleaseTime);

                        // Akumulasi jeda 5 menit untuk customer berikutnya
                        $delayInSeconds += $delayInterval;
                    }
                }

                LogHelper::logInfo(null, ['total_dispatched' => $customers->count()], 900, '[PaymentChannelUpdate] Finished process', $traceId);
            }

            LogHelper::logInfo(
                null,
                ['service' => 'PaymentChannelUpdateNotificationService'],
                200,
                'Completed payment channel update notifications',
                $traceId
            );
        } catch (\Throwable $e) {
            LogHelper::logError(
                $e,
                [
                    'service' => 'PaymentChannelUpdateNotificationService',
                    'error_message' => $e->getMessage(),
                ],
                500,
                'Failed executing payment channel update notifications',
                $traceId
            );
        }
    }
}
