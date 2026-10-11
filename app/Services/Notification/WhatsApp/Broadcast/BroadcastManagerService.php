<?php

namespace App\Services\Notification\WhatsApp\Broadcast;

use App\Enums\WhatsAppAction;
use App\Helpers\DelayMessage;
use App\Helpers\DummyReceiptHelper;
use App\Helpers\ExecutionTimer;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Jobs\WhatsApp\SendBoardcastNotificationJob;
use App\Models\Billings\Billing;
use App\Models\Customers\Customer;
use App\Models\Messaging\MessageEvent;
use App\Services\Messaging\Collectors\AdminBankCollector;
use App\Services\Messaging\Collectors\BroadcastReminderCollector;
use App\Services\Messaging\Collectors\EligibleCustomerCollector;
use App\Services\Messaging\Handlers\BroadcastMessageHandler;
use App\Services\Messaging\MessageBuilder;
use App\Services\Messaging\MessageDispatcher;
use App\Services\Notification\Traits\MapsBroadcastBankPayload;
use App\Services\Notification\WhatsApp\Single\AbstractSingleNotificationService;
use App\Services\WhatsApp\CloudAPI\Broadcast\MidMonthBroadcast;
use App\Services\WhatsApp\CloudAPI\Broadcast\MonthlyKickOffBroadcast;
use App\Services\WhatsApp\CloudAPI\Broadcast\OverdueBroadcast;
use App\Services\WhatsApp\CloudAPI\Broadcast\PaymentChannelUpdate;
use App\Services\WhatsApp\CloudAPI\Messages\TemplateMessageService;
use App\Services\WhatsApp\WhatsAppGatewayResolver;
use Illuminate\Support\Carbon;
use RuntimeException;
use Throwable;

class BroadcastManagerService extends AbstractSingleNotificationService
{
    use MapsBroadcastBankPayload;

    public function __construct(
        WhatsAppGatewayResolver $gatewayResolver,
        TemplateMessageService $templateMessageService,
        MessageDispatcher $dispatcher,
        AdminBankCollector $bankCollector,
        FormatReceiver $formatNumber,
        MessageBuilder $builder,
        DummyReceiptHelper $dummyReceipt,
        private MonthlyKickOffBroadcast $monthlyKickOffBroadcast,
        private MidMonthBroadcast $midMonthBroadcast,
        private OverdueBroadcast $overdueBroadcast,
        private PaymentChannelUpdate $paymentChannelUpdate,
        private EligibleCustomerCollector $customerCollector,
        private BroadcastReminderCollector $broadcastReminderCollector,
    ) {
        parent::__construct($gatewayResolver, $templateMessageService, $dispatcher, $bankCollector, $formatNumber, $builder, $dummyReceipt);
    }

    public function sendBroadcast(WhatsAppAction $action, bool $isTestMode, string $traceId): void
    {
        $timer = new ExecutionTimer();

        try {
            $context = $this->resolveGatewayContext($action->value, $traceId, $timer);
            if (!$context) return;

            $gateway = $context['gateway'];

            if ($gateway->type === 'cloud') {
                $this->executeCloudBroadcast($action, $traceId, $timer);
                return;
            }

            $this->executeOnPremBroadcast($action, $isTestMode, $traceId, $context, $timer);
        } catch (Throwable $e) {
            $exceptionCode = $e->getCode();
            $statusCode = (is_numeric($exceptionCode) && $exceptionCode >= 400 && $exceptionCode <= 599)
                ? (int) $exceptionCode
                : 500;

            LogHelper::logError(null, [
                'action'          => $action->value,
                'error_message'   => $e->getMessage(),
                'exception_class' => get_class($e),
                'file'            => $e->getFile(),
                'line'            => $e->getLine(),
            ], $statusCode, '[BroadcastManager] Failed executing broadcast process', $traceId, $timer->lap());

            throw $e;
        }
    }

    private function executeCloudBroadcast(WhatsAppAction $action, string $traceId, ExecutionTimer $timer): void
    {
        LogHelper::logInfo(null, ['action' => $action->value], 200, '[BroadcastManager] Direct execution via Meta Cloud API', $traceId);

        match ($action) {
            WhatsAppAction::INITIAL_BROADCAST       => $this->monthlyKickOffBroadcast->run($action->value, $traceId),
            WhatsAppAction::DUE_DATE_BROADCAST      => $this->midMonthBroadcast->run($traceId),
            WhatsAppAction::OVERDUE_BROADCAST       => $this->overdueBroadcast->run($traceId),
            WhatsAppAction::PAYMENT_CHANNEL_UPDATE => $this->paymentChannelUpdate->run($traceId),
            default                                => null,
        };

        LogHelper::logInfo(null, ['action' => $action->value], 200, '[BroadcastManager] Cloud API Batch Broadcast finished', $traceId, $timer->lap());
    }

    private function executeOnPremBroadcast(WhatsAppAction $action, bool $isTestMode, string $traceId, array $context, ExecutionTimer $timer): void
    {
        LogHelper::logInfo(null, ['action' => $action->value], 200, '[BroadcastManager] Routing On-Premise broadcast to Jobs with delay', $traceId);

        $totalSentToday = MessageEvent::where('message_type', $action->value)
            ->where('created_at', '>=', now()->startOfDay())
            ->count();

        $maxLimit  = 10;
        $sisaKuota = $maxLimit - $totalSentToday;

        if ($sisaKuota <= 0) {
            LogHelper::logWarning(null, [
                'total_sent' => $totalSentToday,
                'max_limit'  => $maxLimit,
            ], 200, 'Quota reached. On-Premise broadcast halted safely.', $traceId, $timer->lap());
            return;
        }

        $bankCache    = [];
        $sentCounter  = 0;
        $delayMessage = new DelayMessage();

        if ($action === WhatsAppAction::INITIAL_BROADCAST) {
            $customers = $this->customerCollector->collect(now()->startOfDay(), $action->value, null, $isTestMode, $traceId);
            if ($isTestMode) $customers = $customers->take(2);

            foreach ($customers as $customerData) {
                if ($sentCounter >= $sisaKuota) break;

                $bankInfo      = $this->getBankInfoCached($customerData->user_id ?? 1, $bankCache, $traceId);
                $broadcastData = $this->prepareInitialBroadcastData($customerData, $bankInfo);

                $this->dispatchOnPremJob(
                    actionSlug: $action->value,
                    actionEnum: $action,
                    customerId: $customerData->customer_id,
                    broadcastData: $broadcastData,
                    deviceId: $context['deviceId'],
                    traceId: $traceId,
                    delayMessage: $delayMessage
                );

                $sentCounter++;
            }
        } elseif ($action === WhatsAppAction::PAYMENT_CHANNEL_UPDATE) {
            $adminNumber = $this->formatNumber->formatReceiver($context['number']->number, null, false);
            $customers   = $this->customerCollector->collect(now()->startOfDay(), $action->value, null, false, $traceId);
            if ($isTestMode) $customers = $customers->take(2);

            foreach ($customers as $customerData) {
                if ($sentCounter >= $sisaKuota) break;

                $bankPayload = $this->buildChannelUpdateBankPayload($customerData, $action->value, $adminNumber);
                if (!$bankPayload) continue;

                $recipient = $this->formatNumber->resolveRecipient($customerData->customer_contact);

                $broadcastData = array_merge($bankPayload, [
                    'customer_id'    => $customerData->customer_id ?? null,
                    'target_contact' => $recipient['formatted'],
                    'is_override'    => $recipient['is_override'],
                ]);

                $this->dispatchOnPremJob(
                    actionSlug: $action->value,
                    actionEnum: $action,
                    customerId: $customerData->customer_id,
                    broadcastData: $broadcastData,
                    deviceId: $context['deviceId'],
                    traceId: $traceId,
                    delayMessage: $delayMessage
                );

                $sentCounter++;
            }
        } else {
            $billings = $this->broadcastReminderCollector->collect(now());

            if ($billings->isEmpty()) {
                LogHelper::logInfo(null, [], 200, '[BroadcastManager] No eligible billings found for reminder broadcast', $traceId, $timer->lap());
                return;
            }

            if ($isTestMode) $billings = $billings->take(2);

            foreach ($billings as $billing) {
                if ($sentCounter >= $sisaKuota) break;

                $customer = $billing->customer;
                if (!$customer || empty($customer->contact)) continue;

                $bankInfo      = $this->getBankInfoCached($customer->user_id ?? 1, $bankCache, $traceId);
                $actionSlug    = $billing->dynamic_type ?? $action->value;
                $broadcastData = $this->prepareBillingBroadcastData($billing, $bankInfo);

                $this->dispatchOnPremJob(
                    actionSlug: $actionSlug,
                    actionEnum: $action,
                    customerId: $customer->id,
                    broadcastData: $broadcastData,
                    deviceId: $context['deviceId'],
                    traceId: $traceId,
                    delayMessage: $delayMessage
                );

                $sentCounter++;
            }
        }

        LogHelper::logInfo(null, [
            'action'     => $action->value,
            'total_sent' => $sentCounter,
        ], 200, '[BroadcastManager] All On-Premise jobs dispatched to queue successfully.', $traceId, $timer->lap());
    }

    private function dispatchOnPremJob(
        string $actionSlug,
        WhatsAppAction $actionEnum,
        mixed $customerId,
        array $broadcastData,
        mixed $deviceId,
        string $traceId,
        DelayMessage $delayMessage
    ): void {
        $scheduledAt = $delayMessage->calculation($actionEnum->value, $traceId, 0);

        $event = MessageEvent::create([
            'customer_id'  => $customerId,
            'channel'      => 'go_whatsapp',
            'message_type' => $actionEnum->value,
            'phone_number' => null,
            'status'       => 'pending',
            'stage'        => 'SENDING_QUEUE',
            'trace_id'     => $traceId,
            'meta'         => null,
            'device_id'    => $deviceId,
            'scheduled_at' => $scheduledAt,
        ]);

        SendBoardcastNotificationJob::dispatch(
            action: $actionSlug,
            broadcastData: $broadcastData,
            deviceId: $deviceId,
            eventId: $event->id,
            traceId: $traceId
        )->delay($scheduledAt);
    }

    private function getBankInfoCached(mixed $userId, array &$bankCache, string $traceId): mixed
    {
        $adminUserId = (int) ($userId ?? 1);
        if ($adminUserId <= 0) $adminUserId = 1;

        if (!isset($bankCache[$adminUserId])) {
            $bankCache[$adminUserId] = $this->bankCollector->getFormattedBankInfo($adminUserId, $traceId);
        }

        return $bankCache[$adminUserId];
    }

    public function sendSingleEvent(MessageEvent $event, string $traceId): void
    {
        $timer = new ExecutionTimer();

        try {
            LogHelper::logInfo(null, ['event_id' => $event->id], 200, '[Resend Single] Starting payment channel update for single event', $traceId);

            $context = $this->resolveGatewayContext($event->message_type, $traceId, $timer);
            if (!$context) {
                throw new RuntimeException("Active gateway context not found for event ID {$event->id}.");
            }

            $adminNumber  = $this->formatNumber->formatReceiver($context['number']->number, null, false);
            $mainSlug     = WhatsAppAction::PAYMENT_CHANNEL_UPDATE->value;
            $customerData = $this->customerCollector->collect(now(), $mainSlug, $event->customer_id, false, $traceId)->first();

            if (!$customerData) {
                $customer = Customer::with(['subscriptions' => fn($q) => $q->where('status', 'active')->with('package')])->find($event->customer_id);
                if ($customer) {
                    $activeSub = $customer->subscriptions->first();
                    $customerData = (object) [
                        'user_id'          => $customer->user_id,
                        'customer_name'    => $customer->name,
                        'customer_contact' => $customer->contact,
                        'package_name'     => $activeSub?->package?->name ?? 'Shanetfiber',
                    ];
                }
            }

            if (!$customerData) {
                throw new RuntimeException("Data customer tidak ditemukan di sistem untuk customer_id: {$event->customer_id}");
            }

            $bankPayload = $this->buildChannelUpdateBankPayload($customerData, $mainSlug, $adminNumber);
            if (!$bankPayload) {
                throw new RuntimeException("Gagal membuat bank payload. Data bank milik user_id: {$customerData->user_id} kosong.");
            }

            $event->update([
                'meta' => json_encode($bankPayload),
            ]);

            $recipient = $this->formatNumber->resolveRecipient($customerData->customer_contact ?? $event->customer?->contact);

            if (!$recipient['formatted']) {
                throw new RuntimeException("Recipient phone number not found for event ID: {$event->id}");
            }

            $broadcastData = array_merge($bankPayload, [
                'customer_id'    => $event->customer_id,
                'target_contact' => $recipient['formatted'],
                'is_override'    => $recipient['is_override'],
            ]);

            $this->dispatcher->dispatch(
                action: $mainSlug,
                handlerFactory: fn(string $category) => new BroadcastMessageHandler(
                    action: $mainSlug,
                    builder: $this->builder,
                    broadcastData: $broadcastData,
                    category: $category
                ),
                traceId: $traceId,
                eventId: $event->id,
                deviceId: $context['deviceId']
            );

            LogHelper::logInfo(null, ['event_id' => $event->id, 'recipient' => $recipient['formatted']], 200, '[Resend Single] Payment channel update dispatched successfully via MessageDispatcher', $traceId, $timer->lap());
        } catch (Throwable $e) {
            $exceptionCode = $e->getCode();
            $statusCode = (is_numeric($exceptionCode) && $exceptionCode >= 400 && $exceptionCode <= 599)
                ? (int) $exceptionCode
                : 500;

            LogHelper::logError(null, [
                'event_id'        => $event->id,
                'error_message'   => $e->getMessage(),
                'exception_class' => get_class($e),
                'file'            => $e->getFile(),
                'line'            => $e->getLine(),
            ], $statusCode, '[Resend Single] Failed executing single payment channel update', $traceId, $timer->lap());
            throw $e;
        }
    }

    private function prepareInitialBroadcastData(Customer $customer, string $adminBankInfo): array
    {
        $recipient = $this->formatNumber->resolveRecipient($customer->contact);
        $csContact = $this->formatNumber->formatReceiver($customer->user?->contact ?? '', 'display', false);

        return [
            'customer_id'          => $customer->id,
            'customer_code'        => $customer->customer_id ?? $customer->id,
            'customer_name'        => $customer->name ?? '-',
            'customer_address'     => $customer->address ?? '-',
            'target_contact'       => $recipient['formatted'],
            'admin_bank_info'      => $adminBankInfo,
            'cs_contact'           => !empty($csContact) ? $csContact : '-',
            'total_bill_formatted' => $this->builder->currency($customer->package?->price ?? 0),
            'is_override'          => $recipient['is_override'],
        ];
    }

    private function prepareBillingBroadcastData(Billing $billing, string $adminBankInfo): array
    {
        $customer  = $billing->customer;
        $recipient = $this->formatNumber->resolveRecipient($customer->contact);
        $csContact = $this->formatNumber->formatReceiver($customer->user?->contact ?? '', 'display', false);

        $dueDate          = $billing->due_date ? Carbon::parse($billing->due_date) : null;
        $dayDiff          = $dueDate ? (int) now()->diffInDays($dueDate, false) : 0;
        $dueDateFormatted = $dueDate ? $dueDate->locale('id')->translatedFormat('d F Y') : '-';

        return [
            'customer_id'          => $customer->id,
            'customer_code'        => $customer->customer_id ?? $customer->id,
            'customer_name'        => $customer->name ?? '-',
            'customer_address'     => $customer->address ?? '-',
            'target_contact'       => $recipient['formatted'],
            'count_bill'           => $billing->count_bill ?? 1,
            'day'                  => abs($dayDiff),
            'due_date'             => $dueDateFormatted,
            'packages_detail'      => $billing->package_name ?? $customer->package?->name ?? '-',
            'total_bill_formatted' => $this->builder->currency($billing->total_amount ?? 0),
            'admin_bank_info'      => $adminBankInfo,
            'cs_contact'           => !empty($csContact) ? $csContact : '-',
            'is_override'          => $recipient['is_override'],
        ];
    }

    private function buildChannelUpdateBankPayload(object $data, string $mainSlug, string $adminNumber): ?array
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
            'customer_name'   => $data->customer_name ?? '-',
            'services'        => $data->package_name ?? 'Shanetfiber',
            'effective_date'  => Carbon::parse('2026-06-01')->translatedFormat('d F Y'),
            'whatsapp_number' => $adminNumber ?? '-',
        ]);
    }
}
