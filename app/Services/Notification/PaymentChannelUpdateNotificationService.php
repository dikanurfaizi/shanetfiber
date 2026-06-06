<?php

namespace App\Services\Notification;

use App\Enums\WhatsAppAction;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Jobs\Whatsapp\ProcessPaymentChannelUpdateJob;
use App\Models\Customers\Customer;
use App\Models\Messaging\MessageEvent;
use App\Services\Whatsapp\CloudAPI\Broadcast\PaymentChannelUpdate;
use App\Services\Whatsapp\WhatsappGatewayResolver;
use App\Services\Messaging\Collectors\ChannelUpdateCustomerCollector;
use App\Services\Messaging\Collectors\AdminBankCollector;
use App\Services\Notification\Traits\MapsBroadcastBankPayload;
use Illuminate\Support\Carbon;

class PaymentChannelUpdateNotificationService
{
    use MapsBroadcastBankPayload;

    public function __construct(
        private PaymentChannelUpdate $paymentChannelUpdate,
        private WhatsappGatewayResolver $gatewayResolver,
        private ChannelUpdateCustomerCollector $channelUpdateCustomerCollector,
        private AdminBankCollector $bankCollector
    ) {}

    public function run(string $traceId): void
    {
        try {
            LogHelper::logInfo(null, ['service' => 'PaymentChannelUpdateNotificationService'], 200, 'Starting payment channel update notifications', $traceId);

            [$gateway, $account, $number] = $this->gatewayResolver->resolveActiveGatewayAndAccount($traceId, 'update_rekening_juni_2026') ?? [null, null, null];
            if (!$gateway || !$account || !$number) return;

            $adminNumber = (new FormatReceiver())->formatReceiver($number->number, null, false);
            $testMode = config('messaging.reminder_allow_test', false);

            if ($gateway->type === 'cloud') {
                $this->paymentChannelUpdate->run($traceId);
                return;
            }

            $deviceId = $number->device_id;
            $mainSlug = WhatsAppAction::PAYMENT_CHANNEL_UPDATE->value;
            $customers = $this->channelUpdateCustomerCollector->collect(now()->startOfDay(), $mainSlug, null);

            $delayInSeconds = 0;
            $delayInterval = rand(30, 60);
            $overrideNumber = config('messaging.phone_number_override', null);

            foreach ($customers as $data) {
                // 👉 Optimasi: Menggunakan method internal buildBankPayload
                $bankPayload = $this->buildBankPayload($data, $mainSlug, $adminNumber);
                if (!$bankPayload) continue;

                $recipient = $overrideNumber ? (string) $overrideNumber : $data->customer_contact;

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

                $this->dispatchNotificationJob($event->id, $deviceId, $traceId, $recipient, $bankPayload, $mainSlug, $testMode, $delayInSeconds);

                if (!$testMode) {
                    $delayInSeconds += $delayInterval;
                }
            }

            LogHelper::logInfo(null, ['total_dispatched' => $customers->count()], 900, '[PaymentChannelUpdate] Finished process', $traceId);
        } catch (\Throwable $e) {
            LogHelper::logError($e, ['service' => 'PaymentChannelUpdateNotificationService', 'error_message' => $e->getMessage()], 500, 'Failed executing payment channel update notifications', $traceId);
        }
    }

    /**
     * Menangani pengiriman ulang (resend) untuk satu event spesifik secara aman.
     */
    public function sendSingleEvent(MessageEvent $event, string $traceId): void
    {
        try {
            LogHelper::logInfo(null, ['event_id' => $event->id], 200, '[Resend Single] Starting payment channel update for single event', $traceId);

            [$gateway, $account, $number] = $this->gatewayResolver->resolveActiveGatewayAndAccount($traceId, 'update_rekening_juni_2026') ?? [null, null, null];
            if (!$gateway || !$account || !$number) {
                throw new \RuntimeException("Active gateway or account not found for channel update resend.");
            }

            $adminNumber = (new FormatReceiver())->formatReceiver($number->number, null, false);
            $deviceId = $number->device_id;
            $mainSlug = WhatsAppAction::PAYMENT_CHANNEL_UPDATE->value;

            // 👉 Solusi Poin 1 & 3: Ambil data lewat collector
            $customerData = $this->channelUpdateCustomerCollector->collect(now(), $mainSlug, $event->customer_id)->first();

            // Fallback: jika terfilter anti-double query SQL, kita instansiasi model Customer dasar agar resend tidak patah/gagal
            if (!$customerData) {
                $customer = Customer::with(['subscriptions' => fn($q) => $q->where('status', 'active')->with('package')])->find($event->customer_id);
                if ($customer) {
                    $activeSub = $customer->subscriptions->first();
                    $customerData = (object) [
                        'user_id'          => $customer->user_id,
                        'customer_name'    => $customer->name,
                        'customer_contact' => $customer->contact,
                        'package_name'     => $activeSub?->package?->name ?? 'Shanetfiber'
                    ];
                }
            }

            if (!$customerData) {
                throw new \RuntimeException("Data customer tidak ditemukan di sistem untuk customer_id: {$event->customer_id}");
            }

            $bankPayload = $this->buildBankPayload($customerData, $mainSlug, $adminNumber);
            if (!$bankPayload) {
                throw new \RuntimeException("Gagal membuat bank payload. Data bank milik user_id: {$customerData->user_id} kosong.");
            }

            // Update log event lama dengan struktur payload terbaru hasil re-generate resend
            $event->update([
                'meta'            => json_encode($bankPayload),
            ]);

            $overrideNumber = config('messaging.phone_number_override', null);
            $recipient = $overrideNumber ? (string) $overrideNumber : ($customerData->customer_contact ?? $event->customer?->contact);

            if (!$recipient) {
                throw new \RuntimeException("Recipient phone number not found for event ID: {$event->id}");
            }

            // Kirim tanpa akumulasi delay (Immediate Release) karena ini adalah manual resend tunggal
            $this->dispatchNotificationJob($event->id, $deviceId, $traceId, $recipient, $bankPayload, $mainSlug, true);

            LogHelper::logInfo(null, ['event_id' => $event->id, 'recipient' => $recipient], 200, '[Resend Single] Payment channel update job dispatched successfully', $traceId);
        } catch (\Throwable $e) {
            LogHelper::logError($e, ['event_id' => $event->id, 'error' => $e->getMessage()], 500, '[Resend Single] Failed executing single payment channel update', $traceId);
            throw $e;
        }
    }

    /**
     * Helper privat untuk standarisasi pembuatan Payload Bank (DRY Principle)
     */
    private function buildBankPayload(object $data, string $mainSlug, string $adminNumber): ?array
    {
        $adminBanks = $this->bankCollector->getBanksByUserId($data->user_id ?? 0);
        if ($adminBanks->isEmpty()) {
            return null;
        }

        $bankResolution = $this->resolveBankTemplateAndPayload(
            $adminBanks->where('type', 'bank')->values(),
            $adminBanks->where('type', 'ewallet')->values(),
            $mainSlug
        );

        return array_merge($bankResolution['payload'], [
            'customer_name'   => $data->customer_name,
            'services'        => $data->package_name ?? 'Shanetfiber',
            'effective_date'  => Carbon::parse('2026-06-01')->translatedFormat('d F Y'),
            'whatsapp_number' => $adminNumber ?? '-'
        ]);
    }

    /**
     * Helper privat untuk standarisasi dispatch Job ke Queue
     */
    private function dispatchNotificationJob(string $eventId, string $deviceId, string $traceId, string $recipient, array $bankPayload, string $mainSlug, bool $isTestMode, int $delaySeconds = 0): void
    {
        $jobInstance = ProcessPaymentChannelUpdateJob::dispatch(
            eventId: $eventId,
            deviceId: $deviceId,
            traceId: $traceId,
            recipient: $recipient,
            bankPayload: $bankPayload,
            mainSlug: $mainSlug
        );

        $jobInstance->onQueue('notification');

        if (!$isTestMode && $delaySeconds > 0) {
            $jobInstance->delay(now()->addSeconds($delaySeconds));
        }
    }
}
