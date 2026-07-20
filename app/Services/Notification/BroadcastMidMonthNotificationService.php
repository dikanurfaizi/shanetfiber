<?php

namespace App\Services\Notification;

use App\Helpers\ExecutionTimer;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use App\Models\Messaging\MessageEvent;
use App\Services\Whatsapp\CloudAPI\Broadcast\MidMonthBroadcast;
use App\Services\Whatsapp\WhatsappGatewayResolver;
use App\Services\Whatsapp\GowaService;
use App\Services\Messaging\Collectors\AdminBankCollector;
use App\Services\Messaging\Collectors\BroadcastReminderCollector;
use App\Services\Notification\Traits\MapsBroadcastBankPayload;
use Illuminate\Support\Carbon;

class BroadcastMidMonthNotificationService
{
    use MapsBroadcastBankPayload;

    public function __construct(
        private MidMonthBroadcast $midMontBroadcast,
        private WhatsappGatewayResolver $gatewayResolver,
        private GowaService $gowaService,
        private AdminBankCollector $bankCollector,
        private BroadcastReminderCollector $broadcastMidMonthCollector,
    ) {}

    public function run(string $traceId): void
    {
        $timer = new ExecutionTimer();
        try {
            LogHelper::logInfo(
                null,
                [
                    'service' => 'BroadcastMidMonthNotificationService',
                ],
                200,
                'Starting broadcast mid-month reminders',
                $traceId,
                $timer->lap()
            );

            [$gateway, $account, $number] = $this->gatewayResolver->resolveActiveGatewayAndAccount($traceId, 'broadcast_pertengahan_bulan') ?? [null, null, null];

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
                LogHelper::logInfo(null, ['gateway_type' => $gateway->type], 200, 'Redirecting broadcast pipeline engine to Cloud API service', $traceId, $timer->lap());
                $this->midMontBroadcast->run($traceId);
                return;
            }

            $deviceId = $number->device_id;

            $billings = $this->broadcastMidMonthCollector->collect(now());

            LogHelper::logInfo(null, [
                'service' => 'BroadcastMidMonthNotificationService',
                'gateway_type' => $gateway->type,
                'total_billings_collected' => $billings->count(),
            ], 200, 'Collected billings for mid-month broadcast', $traceId, $timer->lap());

            $delayInSeconds = 0;
            $delayInterval = rand(60, 120);
            $overrideNumber = config('messaging.phone_number_override', null);
            $sentCounter = 0;

            foreach ($billings as $billing) {
                $bankPayload = $this->buildBankPayload($billing, $billing->dynamic_type, $adminNumber);
                if (!$bankPayload) {
                    LogHelper::logWarning(null, [
                        'customer_id' => $billing->customer_id ?? 'unknown',
                        'user_id' => $billing->user_id ?? 0
                    ], 404, 'Skipping customer: Bank resolution data structure empty or not found.', $traceId, $timer->lap());
                    continue;
                }

                $recipient = $overrideNumber ? (string) $overrideNumber : $billing->customer->contact;
                $mainSlug = $bankPayload['template'] ?? $billing->dynamic_type;

                $event = MessageEvent::create([
                    'customer_id'     => $billing->customer_id ?? null,
                    'gateway_id'      => $account->id,
                    'channel'         => 'go_whatsapp',
                    'message_type'    => $mainSlug,
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
                    'customer_id' => $billing->customer_id,
                    'recipient' => $recipient,
                    'delay_applied' => $testMode ? 0 : $delayInSeconds
                ], 200, "Dispatching notification local job for customer: {$billing->customer_name}", $traceId, $timer->lap());

                $this->dispatchNotification(
                    $event->id,
                    $deviceId,
                    $traceId,
                    $recipient,
                    $bankPayload,
                    $mainSlug,
                    $testMode,
                    $delayInSeconds,
                    $billing
                );

                if (!$testMode) {
                    $delayInSeconds += $delayInterval;
                }

                $sentCounter++;
            }

            LogHelper::logInfo(
                null,
                [
                    'service' => 'BroadcastMidMonthNotificationService',
                ],
                200,
                'Finish broadcast mid-month reminders',
                $traceId,
                $timer->lap()
            );
        } catch (\Throwable $e) {

            LogHelper::logError(
                $e,
                [
                    'service' => 'BroadcastMidMonthNotificationService',
                    'error_message' => $e->getMessage(),
                ],
                500,
                'Failed executing broadcast mid-month reminders',
                $traceId,
                $timer->lap()
            );
        }
    }

    /**
     * Helper privat untuk standarisasi pembuatan Payload Bank (DRY Principle)
     */
    private function buildBankPayload(Billing $billing, string $mainSlug, string $adminNumber): ?array
    {
        $userId = $billing->customer->user_id ?? 0;

        $adminBanks = $this->bankCollector->getBanksByUserId($userId);
        if ($adminBanks->isEmpty()) {
            return null;
        }

        $bankResolution = $this->resolveBankTemplateAndPayload(
            $adminBanks->where('type', 'bank')->values(),
            $adminBanks->where('type', 'ewallet')->values(),
            $mainSlug
        );

        return array_merge($bankResolution['payload'], [
            'customer_name'   => $billing->customer->name,
            // 👉 LANGSUNG DIAMBIL DARI ACCESSOR / RELASI AKTIF
            'services'        => $billing->customer->activePackage->name ?? 'Shanetfiber',
            'whatsapp_number' => $adminNumber ?? '-',
            'template'        => $bankResolution['template']
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
        int $delaySeconds = 0,
        object $billing
    ): void {
        $this->gowaService->broadcastMidMonth(
            eventId: $eventId,
            deviceId: $deviceId,
            traceId: $traceId,
            recipient: $recipient,
            bankPayload: $bankPayload,
            resolvedTemplate: $mainSlug,
            isTestMode: $isTestMode,
            delaySeconds: $isTestMode ? 0 : $delaySeconds,
            billing: $billing
        );
    }
}
