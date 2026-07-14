<?php

namespace App\Services\Notification;

use App\Enums\WhatsAppAction;
use App\Helpers\ExecutionTimer;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Customers\Customer;
use App\Models\Messaging\MessageEvent;
use App\Services\Whatsapp\CloudAPI\Broadcast\MonthlyKickOffBroadcast;
use App\Services\Whatsapp\WhatsappGatewayResolver;
use App\Services\Whatsapp\GowaService;
use App\Services\Messaging\Collectors\ChannelUpdateCustomerCollector;
use App\Services\Messaging\Collectors\AdminBankCollector;
use App\Services\Notification\Traits\MapsBroadcastBankPayload;
use Illuminate\Support\Carbon;

class MonthlyKickoffBroadcastService
{
    use MapsBroadcastBankPayload;

    public function __construct(
        private MonthlyKickOffBroadcast $monthlyKickOffBroadcast,
        private WhatsappGatewayResolver $gatewayResolver,
        private GowaService $gowaService,
        private ChannelUpdateCustomerCollector $channelUpdateCustomerCollector,
        private AdminBankCollector $bankCollector
    ) {}

    public function run(string $messageType, string $gatewayType, string $traceId): void
    {
        $timer = new ExecutionTimer();
        try {
            LogHelper::logInfo(null, [
                'service' => 'MonthlyKickoffBroadcastService',
                'message_type' => $messageType,
                'gateway_type' => $gatewayType
            ], 200, 'Starting monthly kick off broadcast notifications engine', $traceId, $timer->lap());

            [$gateway, $account, $number] = $this->gatewayResolver->resolveActiveGatewayAndAccount($traceId, 'broadcast_awal_bulan') ?? [null, null, null];

            if (!$gateway || !$account || !$number) {
                LogHelper::logWarning(null, [
                    'resolved_gateway' => !empty($gateway),
                    'resolved_account' => !empty($account),
                    'resolved_number'  => !empty($number)
                ], 404, 'Broadcast skipped: Active gateway, account, or number could not be resolved.', $traceId, $timer->lap());
                return;
            }

            $adminNumber = (new FormatReceiver())->formatReceiver($number->number, null, false);
            $testMode = config('messaging.reminder_allow_test', false);

            if ($gatewayType === 'cloud') {
                LogHelper::logInfo(null, ['gateway_type' => 'cloud'], 200, 'Redirecting broadcast pipeline engine to Cloud API service', $traceId, $timer->lap());
                $this->monthlyKickOffBroadcast->run($messageType, $traceId);
                return;
            }

            $deviceId = $number->device_id;

            // 1. Hitung total pesan yang sudah terkirim/pending hari ini untuk slug ini
            $totalSentToday = MessageEvent::where('message_type', $messageType)
                ->where('created_at', '>=', now()->startOfDay())
                ->count();

            // 2. Tentukan batasan limit (Sesuai dengan komentar bisnis Anda: maksimal 15 per hari)
            $maxLimit = 15;
            $sisaKuota = $maxLimit - $totalSentToday;

            LogHelper::logInfo(null, [
                'quota_limit_max' => $maxLimit,
                'already_sent_today' => $totalSentToday,
                'remaining_quota' => $sisaKuota,
                'test_mode' => $testMode ? 'ON' : 'OFF'
            ], 200, 'Checking daily local billing broadcast threshold capacity', $traceId, $timer->lap());

            // 3. Jika sisa kuota 0 atau minus, stop proses broadcast hari ini
            if ($sisaKuota <= 0) {
                LogHelper::logWarning(null, [
                    'total_sent' => $totalSentToday,
                    'max_limit' => $maxLimit,
                    'remaining_quota' => $sisaKuota
                ], 200, 'Quota reached or exceeded. Local notifications halted safely.', $traceId, $timer->lap());
                return;
            }

            // 4. Ambil data customer
            $customers = $this->channelUpdateCustomerCollector->collect(now()->startOfDay(), $messageType, null);

            LogHelper::logInfo(null, [
                'collected_customers_count' => $customers->count()
            ], 200, 'Raw client pool pulled from data collectors', $traceId, $timer->lap());

            $delayInSeconds = 0;
            $delayInterval = rand(600, 1200);
            $overrideNumber = config('messaging.phone_number_override', null);
            $sentCounter = 0;

            foreach ($customers as $data) {
                // Validasi sisa kuota di dalam loop demi keamanan ganda
                if ($sentCounter >= $sisaKuota) {
                    LogHelper::logWarning(null, [
                        'sent_in_this_batch' => $sentCounter,
                        'batch_max_allowed' => $sisaKuota
                    ], 200, 'Loop stopped because quota limit hit mid-process throttling', $traceId, $timer->lap());
                    break;
                }

                $bankPayload = $this->buildBankPayload($data, $messageType, $adminNumber);
                if (!$bankPayload) {
                    LogHelper::logWarning(null, [
                        'customer_id' => $data->customer_id ?? 'unknown',
                        'user_id' => $data->user_id ?? 0
                    ], 404, 'Skipping customer: Bank resolution data structure empty or not found.', $traceId, $timer->lap());
                    continue;
                }

                $recipient = $overrideNumber ? (string) $overrideNumber : $data->customer_contact;

                $event = MessageEvent::create([
                    'customer_id'     => $data->customer_id ?? null,
                    'gateway_id'      => $account->id,
                    'channel'         => 'go_whatsapp',
                    'message_type'    => $messageType,
                    'phone_number'    => $recipient,
                    'meta'            => json_encode($bankPayload), // Ditambahkan log dump meta payload ke database agar sinkron
                    'status'          => 'pending',
                    'stage'           => 'INIT',
                    'request_payload' => null,
                    'retry_count'     => 0,
                    'trace_id'        => $traceId,
                ]);

                LogHelper::logInfo(null, [
                    'event_id' => $event->id,
                    'customer_id' => $data->customer_id,
                    'recipient' => $recipient,
                    'delay_applied' => $testMode ? 0 : $delayInSeconds
                ], 200, "Dispatching notification local job for customer: {$data->customer_name}", $traceId, $timer->lap());

                $this->dispatchNotification($event->id, $deviceId, $traceId, $recipient, $bankPayload, $messageType, $testMode, $delayInSeconds);

                if (!$testMode) {
                    $delayInSeconds += $delayInterval;
                }

                $sentCounter++;
            }

            LogHelper::logInfo(null, [
                'total_dispatched' => $sentCounter,
                'execution_duration_seconds' => $timer->lap()
            ], 900, 'Finished monthly kickoff broadcast local process pipeline execution', $traceId);
        } catch (\Throwable $e) {
            LogHelper::logError($e, [
                'service' => 'MonthlyKickoffBroadcastService',
                'message_type' => $messageType,
                'error_message' => $e->getMessage()
            ], 500, 'Fatal error inside monthly kick off broadcast runner', $traceId, $timer->lap());
        }
    }

    /**
     * Menangani pengiriman ulang (resend) untuk satu event spesifik secara aman dengan logging ketat.
     */
    public function sendSingleEvent(MessageEvent $event, string $traceId): void
    {
        $timer = new ExecutionTimer();
        try {
            LogHelper::logInfo(null, [
                'event_id' => $event->id,
                'target_customer_id' => $event->customer_id
            ], 200, '[Resend Single] Executing manual fallback request for single event row', $traceId, $timer->lap());

            [$gateway, $account, $number] = $this->gatewayResolver->resolveActiveGatewayAndAccount($traceId, 'update_rekening_juni_2026') ?? [null, null, null];
            if (!$gateway || !$account || !$number) {
                throw new \RuntimeException("Active gateway infrastructure parameters could not be established for single resend.");
            }

            $adminNumber = (new FormatReceiver())->formatReceiver($number->number, null, false);
            $deviceId = $number->device_id;
            $mainSlug = WhatsAppAction::PAYMENT_CHANNEL_UPDATE->value;

            // Ambil data lewat collector
            $customerData = $this->channelUpdateCustomerCollector->collect(now(), $mainSlug, $event->customer_id)->first();

            // Fallback: jika terfilter anti-double query SQL, kita instansiasi model Customer dasar
            if (!$customerData) {
                LogHelper::logInfo(null, ['event_customer_id' => $event->customer_id], 200, '[Resend Single] Collector query empty due to anti-double filters. Falling back to manual Eloquent relation check.', $traceId);

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
                throw new \RuntimeException("Customer relational entity records completely missing from database for ID: {$event->customer_id}");
            }

            $bankPayload = $this->buildBankPayload($customerData, $mainSlug, $adminNumber);
            if (!$bankPayload) {
                throw new \RuntimeException("Failed to generate financial payload arrays. Bank lists for vendor ID [{$customerData->user_id}] are completely unconfigured.");
            }

            // Update log event lama dengan struktur payload terbaru hasil re-generate resend
            $event->update([
                'meta' => json_encode($bankPayload),
            ]);

            $overrideNumber = config('messaging.phone_number_override', null);
            $recipient = $overrideNumber ? (string) $overrideNumber : ($customerData->customer_contact ?? $event->customer?->contact);

            if (!$recipient) {
                throw new \RuntimeException("Recipient terminal telephone destination addresses are invalid or missing.");
            }

            LogHelper::logInfo(null, [
                'event_id' => $event->id,
                'recipient' => $recipient,
                'resolved_payload_keys' => array_keys($bankPayload)
            ], 200, '[Resend Single] Injecting parameters directly to gateway router', $traceId);

            // Kirim tanpa akumulasi delay (Immediate Release)
            $this->dispatchNotification($event->id, $deviceId, $traceId, $recipient, $bankPayload, $mainSlug, true);

            LogHelper::logInfo(null, [
                'event_id' => $event->id,
                'recipient' => $recipient
            ], 200, '[Resend Single] Single monthly billing notification dispatched successfully to queues', $traceId);
        } catch (\Throwable $e) {
            LogHelper::logError($e, [
                'event_id' => $event->id,
                'error_msg' => $e->getMessage()
            ], 500, '[Resend Single] Critical failure handling manual single broadcast action', $traceId);
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
            'effective_date'  => Carbon::now()->translatedFormat('d F Y'), // 💡 DINAMIS: Menggunakan waktu bulan berjalan, bukan hardcode 1 Juni lagi
            'whatsapp_number' => $adminNumber ?? '-'
        ]);
    }

    /**
     * Helper privat untuk standarisasi dispatch Job ke Queue
     */
    private function dispatchNotification(string $eventId, string $deviceId, string $traceId, string $recipient, array $bankPayload, string $mainSlug, bool $isTestMode, int $delaySeconds = 0): void
    {
        $this->gowaService->paymentChannelUpdate(
            eventId: $eventId,
            deviceId: $deviceId,
            traceId: $traceId,
            recipient: $recipient,
            bankPayload: $bankPayload,
            resolvedTemplate: $mainSlug,
            isTestMode: $isTestMode,
            delaySeconds: $isTestMode ? 0 : $delaySeconds
        );
    }
}
