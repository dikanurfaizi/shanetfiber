<?php

namespace App\Services\Notification;

use Carbon\Carbon; // 💡 Diperbaiki: Impor Carbon agar tidak memicu Class Not Found
use App\Helpers\ExecutionTimer;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Messaging\MessageEvent;
use App\Services\Messaging\Collectors\AdminBankCollector;
use App\Services\Messaging\Collectors\BroadcastReminderCollector;
use App\Services\Notification\Traits\MapsBroadcastBankPayload;
use App\Services\Whatsapp\CloudAPI\Broadcast\OverdueBroadcast;
use App\Services\Whatsapp\GowaService;
use App\Services\Whatsapp\WhatsappGatewayResolver;

class OverdueBroadcastNotificationService
{
    use MapsBroadcastBankPayload;

    public function __construct(
        private GowaService $gowaService,
        private OverdueBroadcast $overdueBroadcast,
        private WhatsappGatewayResolver $gatewayResolver,
        private BroadcastReminderCollector $broadcastReminderCollector,
        private AdminBankCollector $bankCollector, // 💡 Diperbaiki: Pastikan properti bankCollector di-inject jika digunakan di buildBankPayload
    ) {}

    public function run(string $traceId): void
    {
        $timer = new ExecutionTimer();
        try {
            LogHelper::logInfo(null, [
                'service' => 'OverdueBroadcast',
            ], 200, 'Starting overdue broadcast notifications engine', $traceId, $timer->lap());

            [$gateway, $account, $number] = $this->gatewayResolver->resolveActiveGatewayAndAccount($traceId, 'broadcast_akhir_bulan') ?? [null, null, null];

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

            if ($gateway->type === 'cloud') {
                LogHelper::logInfo(null, ['gateway_type' => 'cloud'], 200, 'Redirecting broadcast pipeline engine to Cloud API service', $traceId, $timer->lap());
                $this->overdueBroadcast->run($traceId);
                return;
            }

            $deviceId = $number->device_id;

            // 💡 PANGKAS TOTAL: Pengecekan sisa kuota harian di PHP dihapus karena 
            // Query Collector sudah mengunci `->limit(15)` dan menyaring duplikasi via `whereNotExists`.

            // 1. Ambil data billing potensial hari ini menggunakan collector terpadu
            $billings = $this->broadcastReminderCollector->collect(now());

            LogHelper::logInfo(null, [
                'collected_billings_count' => $billings->count(),
                'test_mode' => $testMode ? 'ON' : 'OFF'
            ], 200, 'Smart client pool pulled from data collectors via database-level thresholds', $traceId, $timer->lap());

            $delayInSeconds = 0;
            $delayInterval = rand(600, 1200);
            $overrideNumber = config('messaging.phone_number_override', null);
            $sentCounter = 0;

            foreach ($billings as $data) {
                // Relasi customer dimuat dari query eager-loading di collector
                $customer = $data->customer;
                if (!$customer) {
                    LogHelper::logWarning(null, ['billing_id' => $data->id], 404, 'Skipping: Customer relation not found for this billing.', $traceId, $timer->lap());
                    continue;
                }

                // 💡 Tipe pesan dibaca langsung dari kolom hasil kalkulasi SQL CASE WHEN
                $resolvedSlug = $data->dynamic_type;

                // Mengambil data relasi user untuk kebutuhan data bank
                $userId = $customer->user_id ?? 0;

                // Siapkan payload bank & detail data tagihan
                $bankPayload = $this->buildBankPayload($customer, $userId, $data, $resolvedSlug, $adminNumber);
                if (!$bankPayload) {
                    LogHelper::logWarning(null, [
                        'customer_id' => $data->customer_id ?? 'unknown',
                        'user_id' => $userId
                    ], 404, 'Skipping customer: Bank resolution data structure empty or not found.', $traceId, $timer->lap());
                    continue;
                }

                $recipient = $overrideNumber ? (string) $overrideNumber : $customer->contact;

                // Buat event log data ke database sebelum disalurkan ke API Gateway
                $event = MessageEvent::create([
                    'customer_id'     => $data->customer_id,
                    'gateway_id'      => $account->id,
                    'channel'         => 'go_whatsapp',
                    'message_type'    => $resolvedSlug, // String tunggal dinamis dari SQL (billing_overdue / due_today / due_soon)
                    'phone_number'    => $recipient,
                    'meta'            => json_encode($bankPayload),
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
                    'message_type' => $resolvedSlug,
                    'delay_applied' => $testMode ? 0 : $delayInSeconds
                ], 200, "Dispatching notification local job for customer: {$customer->name}", $traceId, $timer->lap());

                $this->dispatchNotification(
                    eventId: $event->id,
                    deviceId: $deviceId,
                    traceId: $traceId,
                    recipient: $recipient,
                    bankPayload: $bankPayload,
                    mainSlug: $resolvedSlug,
                    isTestMode: $testMode,
                    delaySeconds: $delayInSeconds,
                    billingData: $data
                );

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
            LogHelper::logError(
                $e,
                [
                    'service' => 'OverdueBroadcastNotificationService',
                    'error_message' => $e->getMessage(),
                ],
                500,
                'Failed executing overdue broadcast notifications',
                $traceId
            );
        }
    }

    /**
     * Helper privat untuk standarisasi pembuatan Payload Bank
     */
    private function buildBankPayload(object $customer, int $userId, object $billingData, string $mainSlug, string $adminNumber): ?array
    {
        $adminBanks = $this->bankCollector->getBanksByUserId($userId);
        if ($adminBanks->isEmpty()) {
            return null;
        }

        $bankResolution = $this->resolveBankTemplateAndPayload(
            $adminBanks->where('type', 'bank')->values(),
            $adminBanks->where('type', 'ewallet')->values(),
            $mainSlug
        );

        // Mengambil nama paket langganan aktif jika ada relasinya
        $packageName = $customer->activeSubscription->package->name ?? 'Shanetfiber';

        return array_merge($bankResolution['payload'], [
            'customer_name'   => $customer->name,
            'services'        => $packageName,
            'effective_date'  => Carbon::now()->translatedFormat('d F Y'), // Mengikuti waktu bulan berjalan dinamis
            'whatsapp_number' => $adminNumber ?? '-',
            'total_amount'    => $billingData->total_unpaid ?? $billingData->total_amount // Total tunggakan akumulasi dari SQL
        ]);
    }

    /**
     * Helper privat untuk standarisasi dispatch Job ke Queue
     */
    private function dispatchNotification(
        string $eventId,
        string $deviceId,
        string $traceId,
        string $recipient,
        array $bankPayload,
        string $mainSlug,
        bool $isTestMode,
        int $delaySeconds,
        object $billingData // 💡 Ditambahkan objek billing data utuh dari database
    ): void {
        // Mengarahkan langsung ke method broadcastMidMonth sesuai target Anda
        $this->gowaService->broadcastMidMonth(
            eventId: $eventId,
            deviceId: $deviceId,
            traceId: $traceId,
            recipient: $recipient,
            bankPayload: $bankPayload,
            resolvedTemplate: $mainSlug,
            isTestMode: $isTestMode,
            delaySeconds: $delaySeconds,
            billing: $billingData // 💡 Diteruskan ke parameter target
        );
    }
}
