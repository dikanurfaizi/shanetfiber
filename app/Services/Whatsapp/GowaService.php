<?php

namespace App\Services\WhatsApp;

use App\Enums\WhatsAppAction;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use App\Models\Billings\Order;
use App\Models\Receipt;
use App\Models\WhatsApp\WhatsAppGateway;
use App\Models\Customers\CustomerSubscription;
use App\Services\Messaging\Collectors\AdminBankCollector;
use App\Services\Messaging\Collectors\BankInfoCollector;
use App\Services\Messaging\Collectors\BillingEligibleCollector;
use App\Services\Messaging\Collectors\BillingPackageCollector;
use App\Services\Messaging\Collectors\BillingPeriodCollector;
use App\Services\Messaging\Collectors\FormatterCollector;
use App\Services\Messaging\Handlers\ActivationHandler;
use App\Services\Messaging\Handlers\BillingStatusHandler;
use App\Services\Messaging\Handlers\BroadcastMonthlyHandler;
use App\Services\Messaging\Handlers\ChangesPackageHandler;
use App\Services\Messaging\Handlers\CreateOrderHandler;
use App\Services\Messaging\Handlers\MenuHandler;
use App\Services\Messaging\Handlers\PaymentHandler;
use App\Services\Messaging\Handlers\TestMessageHandler;
use App\Services\Messaging\Handlers\BroadcastMidMonthHandler;
use App\Services\Messaging\MessageBuilder;
use App\Services\Messaging\MessageDispatcher;
use App\Services\Messaging\Handlers\PaymentChannelUpdateHandler;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class GowaService
{
    /**
     * Chesk status wa gateway
     * return false = enable, return true = disable
     */
    public function __construct(
        private MessageDispatcher $dispatcher,
        private MessageBuilder $builder,
        private AdminBankCollector $bankInfoCollector,
        private BillingEligibleCollector $eligibleCollector,
        private BillingPeriodCollector $periodCollector,
        private FormatterCollector $formatterCollector,
        private FormatReceiver $formatNumber,
    ) {}

    // ============================================
    // =====             Activation           =====

    // public function activation(
    //     ?int $customerMembershipId,
    //     CustomerSubscription $customerSubscription,
    //     string $action,
    //     ?int $eventId,
    //     string $deviceId,
    //     string $traceId,
    //     array $extraProrateData = [] // 💡 Tambahkan parameter array penampung data valid di sini
    // ): void {
    //     $queueName = 'notification';
    //     try {
    //         LogHelper::logInfo(
    //             null,
    //             ['customerId' => $customerSubscription->customer_id],
    //             200,
    //             '[Activation] Start activation',
    //             $traceId
    //         );

    //         if (empty($customerSubscription->customer->contact)) {
    //             LogHelper::logWarning(
    //                 null,
    //                 ['customer_id' => $customerSubscription->customer_id],
    //                 400,
    //                 '[Activation] Empty contact number',
    //                 $traceId
    //             );
    //             return;
    //         }

    //         $this->dispatcher->dispatch(
    //             $action,
    //             fn(string $category) => new ActivationHandler(
    //                 customerMembershipId: $customerMembershipId,
    //                 customerSubscription: $customerSubscription,
    //                 builder: $this->builder,
    //                 getBankInfo: fn($userId) => $this->bankInfoCollector->getFormattedBankInfo($userId, $traceId),
    //                 category: $category,
    //                 extraProrateData: $extraProrateData
    //             ),
    //             queue: $queueName,
    //             traceId: $traceId,
    //             eventId: $eventId,
    //             deviceId: $deviceId,
    //             delaySeconds: 0
    //         );
    //     } catch (\Throwable $e) {
    //         LogHelper::logError(null, [
    //             'customer_id' => $customerSubscription->customer_id ?? null,
    //             'action' => $action,
    //             'error' => [
    //                 'code' => $e->getCode(),
    //                 'message' => $e->getMessage(),
    //             ],
    //         ], 500, '[Activation] Failed to run handler', $traceId);
    //     }
    // }

    // =====             Activation           =====
    // ============================================
    // =====            Create Order          =====

    // public function orderCreated(int $billingId, int $orderId, string $action, string $eventId, string $deviceId, string $traceId): void
    // {
    //     $queueName = 'notification';
    //     try {
    //         $billing = Billing::with(['customer.user', 'billingSubscription.customerSubscription.package'])
    //             ->find($billingId);

    //         $order = Order::find($orderId);

    //         if (! $billing || ! $order) {
    //             LogHelper::logWarning(null, [
    //                 'billing_id' => $billingId,
    //                 'order_id' => $orderId,
    //             ], 404, 'Data billing atau order tidak ditemukan', $traceId);

    //             return;
    //         }

    //         $this->dispatcher->dispatch(
    //             $action,
    //             fn(string $category) => new CreateOrderHandler(
    //                 billing: $billing,
    //                 order: $order,
    //                 builder: $this->builder,
    //                 category: $category
    //             ),
    //             queue: $queueName,
    //             traceId: $traceId,
    //             eventId: $eventId,
    //             deviceId: $deviceId,
    //             delaySeconds: 0
    //         );

    //         LogHelper::logInfo(null, [
    //             'billing_id' => $billingId,
    //             'order_id' => $orderId,
    //             'trace_id' => $traceId,
    //         ], 200, '[createOrder] Handler CreateOrderHandler berhasil dijalankan', $traceId);
    //     } catch (\Throwable $e) {
    //         LogHelper::logError(null, [
    //             'billing_id' => $billingId ?? null,
    //             'order_id' => $orderId ?? null,
    //             'error' => [
    //                 'code' => $e->getCode(),
    //                 'message' => $e->getMessage(),
    //             ],
    //         ], 500, '[createOrder] Gagal memproses handler CreateOrderHandler', $traceId);
    //     }
    // }

    // =====            Create Order          =====
    // ============================================
    // =====          Payment Package         =====

    // public function notifyPaymentCompleted(
    //     Billing $billing,
    //     Receipt $receipt,          // ⬅️ WAJIB
    //     string $channel,            // cash | transfer | payment_gateway
    //     ?string $eventId,
    //     string $deviceId,
    //     string $traceId
    // ): void {
    //     $queueName = 'notification';
    //     try {
    //         $this->dispatcher->dispatch(
    //             'payment',
    //             fn(string $category) => new PaymentHandler(
    //                 billing: $billing,
    //                 receipt: $receipt,
    //                 channel: $channel,
    //                 builder: $this->builder,
    //                 category: $category
    //             ),
    //             queue: $queueName,
    //             traceId: $traceId,
    //             eventId: $eventId,
    //             deviceId: $deviceId,
    //             delaySeconds: 0
    //         );
    //     } catch (\Throwable $e) {
    //         LogHelper::logError(
    //             null,
    //             [
    //                 'billing_id' => $billing->id ?? null,
    //                 'action' => $channel,
    //                 'error' => [
    //                     'code' => $e->getCode(),
    //                     'message' => $e->getMessage(),
    //                 ],
    //             ],
    //             500,
    //             'Gagal memproses payment_package via handler baru',
    //             $traceId
    //         );
    //     }
    // }

    // public function notifyPayLater(
    //     CustomerSubscription $customerSubscription,
    //     Billing $billing,
    //     ?int $eventId,
    //     string $deviceId,
    //     string $action,
    //     string $traceId
    // ): void {
    //     $queueName = 'notification';
    //     try {
    //         $this->dispatcher->dispatch(
    //             $action,
    //             fn(string $category) => new BillingStatusHandler(
    //                 customerSubscription: $customerSubscription,
    //                 billing: $billing,
    //                 type: $action,
    //                 builder: $this->builder,
    //                 getBankInfo: fn($userId) => $this->bankInfoCollector->getFormattedBankInfo($userId, $traceId),
    //                 category: $category
    //             ),
    //             queue: $queueName,
    //             traceId: $traceId,
    //             eventId: $eventId,
    //             deviceId: $deviceId,
    //             delaySeconds: 0
    //         );
    //     } catch (\Throwable $e) {
    //         LogHelper::logError(
    //             null,
    //             [
    //                 'billing_id' => $billing->id ?? null,
    //                 'action' => $action,
    //                 'error' => [
    //                     'code' => $e->getCode(),
    //                     'message' => $e->getMessage(),
    //                 ],
    //             ],
    //             500,
    //             'Gagal memproses payment_package via handler baru',
    //             $traceId
    //         );
    //     }
    // }

    // public function notifyUnpayment(
    //     CustomerSubscription $customerSubscription,
    //     Billing $billing,
    //     ?int $eventId,
    //     string $deviceId,
    //     string $action,
    //     string $traceId
    // ): void {
    //     $queueName = 'notification';
    //     try {
    //         $this->dispatcher->dispatch(
    //             $action,
    //             fn(string $category) => new BillingStatusHandler(
    //                 customerSubscription: $customerSubscription,
    //                 billing: $billing,
    //                 type: $action,
    //                 builder: $this->builder,
    //                 getBankInfo: fn($userId) => $this->bankInfoCollector->getFormattedBankInfo($userId, $traceId),
    //                 category: $category
    //             ),
    //             queue: $queueName,
    //             traceId: $traceId,
    //             eventId: $eventId,
    //             deviceId: $deviceId,
    //             delaySeconds: 0
    //         );
    //     } catch (\Throwable $e) {
    //         LogHelper::logError(
    //             null,
    //             [
    //                 'billing_id' => $billing->id ?? null,
    //                 'action' => $action,
    //                 'error' => [
    //                     'code' => $e->getCode(),
    //                     'message' => $e->getMessage(),
    //                 ],
    //             ],
    //             500,
    //             'Gagal memproses payment_package via handler baru',
    //             $traceId
    //         );
    //     }
    // }

    // =====             Payment Package            =====
    // ==================================================
    // Di dalam GowaService.php
    // public function initialPaymentBroadcast(
    //     string $eventId,
    //     string $deviceId,
    //     string $traceId,
    //     string $recipient,
    //     array $bankPayload,
    //     string $resolvedTemplate,
    //     bool $isTestMode = false,
    //     int $delaySeconds = 0
    // ): void {
    //     $startTime = microtime(true);
    //     $queueName = 'notification';

    //     try {
    //         // Langsung dispatch job pengiriman pesan untuk 1 event ini
    //         $this->dispatcher->dispatch(
    //             $resolvedTemplate,
    //             fn(string $category) => new InitialPaymentHandler(
    //                 eventId: $eventId,
    //                 recipient: $recipient,
    //                 payload: $bankPayload,
    //                 builder: $this->builder,
    //                 category: $category
    //             ),
    //             queue: $queueName,
    //             traceId: $traceId,
    //             eventId: $eventId,
    //             deviceId: $deviceId,
    //             delaySeconds: $isTestMode ? 0 : $delaySeconds
    //         );

    //         LogHelper::logInfo(null, [
    //             'event_id'  => $eventId,
    //             'recipient' => $recipient,
    //         ], 200, 'Initial payment broadcast item dispatched to queue', $traceId, round(microtime(true) - $startTime, 2));
    //     } catch (\Throwable $e) {
    //         LogHelper::logError($e, [
    //             'event_id' => $eventId,
    //             'trace_id' => $traceId,
    //         ], 500, '[initialPaymentBroadcast] Failed dispatching to queue', $traceId);
    //     }
    // }

    // ===== Broadcast at the beginning of the month ====
    // ==================================================
    // ===== Broadcast at the middle of the month =======

    // public function broadcastMidMonth(
    //     string $eventId,
    //     string $deviceId,
    //     string $traceId,
    //     string $recipient,
    //     array $bankPayload,
    //     string $resolvedTemplate,
    //     bool $isTestMode,
    //     ?int $delaySeconds,
    //     object $billing
    // ): void {
    //     $queueName = 'notification';
    //     try {
    //         $finalDelay = $isTestMode ? 0 : ($delaySeconds ?? 0);

    //         // Validasi kontak kosong
    //         if (empty($recipient)) {
    //             LogHelper::logWarning(
    //                 null,
    //                 ['event_id' => $eventId],
    //                 400,
    //                 '[Broadcast Mid Month] Empty recipient contact number',
    //                 $traceId
    //             );
    //             return;
    //         }

    //         $activeSub = $billing->customer->activeSubscription;
    //         $activeMem = $billing->customer->activeMembership;

    //         $this->dispatcher->dispatch(
    //             $resolvedTemplate,
    //             fn(string $category) => new BroadcastMidMonthHandler(
    //                 billing: $billing,
    //                 type: $resolvedTemplate,
    //                 builder: $this->builder,
    //                 getBankInfo: fn() => $bankPayload,

    //                 // 👉 Fake Object buatanmu dipertahankan, aman untuk formatPackageInfo asli!
    //                 getBillingPackages: fn() => collect([(object)[
    //                     'package_name'       => $activeSub->package->name ?? null,
    //                     'billing_periode_id' => $billing->billingSubscription->billing_periode_id ?? null,
    //                     'price'              => $activeSub->package->price ?? 0,
    //                     'package_price'      => $billing->base_price,
    //                     'discount'           => $billing->discount_amount,
    //                     'type'               => $activeMem->membership->name ?? null,
    //                     'status'             => $billing->status,
    //                     'due_date'           => $billing->due_date,
    //                     'paylater_until'     => $billing->paylater_until,
    //                     'periode_name'       => $billing->billingSubscription->billingPeriode->name ?? null,
    //                     'total_amount'       => $billing->total_amount,
    //                 ]]),

    //                 formatPackageInfo: fn($pkg) => $this->formatterCollector->formatPackageInfo($pkg),

    //                 // 👉 Instant return dari subquery, 0 query tambahan saat queue jalan!
    //                 getUnpaidSummary: fn() => [
    //                     'count_unpaid' => (int) ($billing->count_unpaid ?? 0),
    //                     'total_unpaid' => (float) ($billing->total_unpaid ?? 0),
    //                 ],
    //                 category: $category // Diambil langsung dari callback dispatcher
    //             ),
    //             queue: $queueName,
    //             traceId: $traceId,
    //             eventId: $eventId,
    //             deviceId: $deviceId,
    //             delaySeconds: $finalDelay
    //         );
    //     } catch (\Throwable $e) {
    //         LogHelper::logError(
    //             null,
    //             [
    //                 'trace_id' => $traceId,
    //                 'error' => [
    //                     'code' => $e->getCode(),
    //                     'message' => $e->getMessage(),
    //                 ],
    //             ],
    //             500,
    //             '[Broadcast Mid Month] Failed to dispatch handler',
    //             $traceId
    //         );
    //     }
    // }

    // ===== Broadcast at the middle of the month =======
    // ==================================================
    // =====          Changes package         ===========

    // public function changesPackage(
    //     int $customerId,
    //     ?string $oldPackageName,
    //     ?int $oldPackagePrice,
    //     ?int $eventId,
    //     string $deviceId,
    //     string $action,
    //     string $traceId
    // ): void {
    //     $queueName = 'notification';
    //     try {
    //         $this->dispatcher->dispatch(
    //             $action,
    //             fn(string $category) => new ChangesPackageHandler(
    //                 customerId: $customerId,
    //                 oldPackageName: $oldPackageName,
    //                 oldPackagePrice: $oldPackagePrice,
    //                 builder: $this->builder,
    //                 getBankInfo: fn(int $userId) => $this->bankInfoCollector->getFormattedBankInfo($userId, $traceId),
    //                 formatReceiver: fn(string $number) => $this->formatNumber->formatReceiver($number, null, false),
    //                 category: $category
    //             ),
    //             queue: $queueName,
    //             traceId: $traceId,
    //             eventId: $eventId,
    //             deviceId: $deviceId,
    //             delaySeconds: 0
    //         );
    //     } catch (\Throwable $e) {
    //         LogHelper::logError(
    //             null,
    //             [
    //                 'trace_id' => $traceId,
    //                 'customer_id' => $customerId,
    //                 'old_package' => $oldPackageName,
    //                 'old_price' => $oldPackagePrice,
    //                 'error' => [
    //                     'code' => $e->getCode(),
    //                     'message' => $e->getMessage(),
    //                 ],
    //             ],
    //             500,
    //             '[ChangesPackage] Failed to dispatch handler',
    //             $traceId
    //         );
    //     }
    // }

    // =====            Changes package         =====
    // ============================================
    // =====   Test WhatsApp Message Manual   =====

    // public function test(WhatsAppGateway $gateway, string $receive_number)
    // {
    //     $traceId = (string) Str::uuid();
    //     $queueName = 'notification';
    //     try {
    //         $activeAccount = $gateway->accounts()->where('is_active', true)->first();

    //         LogHelper::logInfo(null, [
    //             'active_account' => $activeAccount?->id,
    //             'receive_number' => $receive_number,
    //             'gateway_id' => $gateway->id,
    //         ], 100, '[TestGateway-Gowa] Start testing Gowa gateway', $traceId);

    //         if (empty($receive_number)) {
    //             return back()->with('error', 'Nomor penerima wajib diisi.');
    //         }

    //         // Dispatch ke handler
    //         $dispatcher = app(MessageDispatcher::class);
    //         $templateSlug = WhatsAppAction::TEST_MESSAGE->value;

    //         $dispatcher->dispatch(
    //             action: $templateSlug,
    //             handlerFactory: fn(string $category) => new TestMessageHandler(
    //                 receiver: $receive_number,
    //                 gatewayId: $gateway->id,
    //                 templateSlug: $templateSlug,
    //                 category: $category
    //             ),
    //             queue: $queueName,
    //             traceId: $traceId,
    //             eventId: null,
    //             deviceId: $activeAccount->defaultNumber?->device_id,
    //             delaySeconds: 0
    //         );

    //         LogHelper::logInfo(null, [
    //             'receive_number' => $receive_number,
    //             'gateway_id' => $gateway->id,
    //         ], 200, '[TestGateway-Gowa] Message queued successfully', $traceId);

    //         return back()->with('success', 'Pesan uji berhasil dikirim.');
    //     } catch (\Throwable $e) {

    //         LogHelper::logError(null, [
    //             'gateway_id' => $gateway->id,
    //             'receive_number' => $receive_number,
    //             'error' => [
    //                 'message' => $e->getMessage(),
    //                 'code' => $e->getCode(),
    //             ],
    //         ], 500, '[TestGateway-Gowa] Failed to test gateway', $traceId);

    //         return back()->with('error', 'Gagal mengirim pesan uji.');
    //     }
    // }

    public function menu(string $custName, string $custContact, ?int $eventId, string $deviceId, string $traceId)
    {
        $queueName = 'notification';
        $globalIndex = 1;
        try {
            $this->dispatcher->dispatch(
                'changes_package',
                fn(string $category) => new MenuHandler(
                    type: 'menu',
                    builder: $this->builder,
                    custContact: $custContact,
                    custName: $custName,
                    category: $category
                ),
                queue: $queueName,
                traceId: $traceId,
                eventId: $eventId,
                deviceId: $deviceId,
                delaySeconds: 0
            );
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'trace_id' => $traceId,
                    'cust_contact' => $custContact,
                    'cust_name' => $custName,
                    'error' => [
                        'code' => $e->getCode(),
                        'message' => $e->getMessage(),
                    ],
                ],
                500,
                '[Menu] Failed to dispatch handler',
                $traceId
            );
        }
    }

    // public function paymentChannelUpdate(
    //     string $eventId,
    //     string $deviceId,
    //     string $traceId,
    //     string $recipient,
    //     array $bankPayload,
    //     string $resolvedTemplate,
    //     bool $isTestMode,
    //     ?int $delaySeconds
    // ): void {
    //     $queueName = 'notification';
    //     try {
    //         // 1. Amankan logika delay langsung di dalam eksekutor
    //         $finalDelay = $isTestMode ? 0 : ($delaySeconds ?? 0);

    //         LogHelper::logInfo(
    //             null,
    //             [
    //                 'recipient' => $recipient,
    //                 'template' => $resolvedTemplate,
    //                 'delay_applied' => $finalDelay . ' seconds'
    //             ],
    //             200,
    //             '[Payment Channel Update] Preparing to dispatch handler',
    //             $traceId
    //         );

    //         // 👉 Validasi kontak kosong
    //         if (empty($recipient)) {
    //             LogHelper::logWarning(
    //                 null,
    //                 ['event_id' => $eventId],
    //                 400,
    //                 '[Payment Channel Update] Empty recipient contact number',
    //                 $traceId
    //             );
    //             return;
    //         }

    //         // 2. Kirim ke dispatcher dengan instance Carbon yang valid
    //         $this->dispatcher->dispatch(
    //             $resolvedTemplate,
    //             fn(string $category) => new PaymentChannelUpdateHandler(
    //                 builder: $this->builder,
    //                 recipient: $recipient,
    //                 bankPayload: $bankPayload,
    //                 resolvedTemplate: $resolvedTemplate,
    //                 category: $category
    //             ),
    //             queue: $queueName,
    //             traceId: $traceId,
    //             eventId: $eventId,
    //             deviceId: $deviceId,
    //             delaySeconds: $finalDelay
    //         );
    //     } catch (\Throwable $e) {
    //         LogHelper::logError(
    //             null,
    //             [
    //                 'trace_id' => $traceId,
    //                 'recipient' => $recipient ?? null,
    //                 'error' => [
    //                     'code' => $e->getCode(),
    //                     'message' => $e->getMessage(),
    //                 ],
    //             ],
    //             500,
    //             '[Payment Channel Update] Failed to dispatch handler',
    //             $traceId
    //         );
    //     }
    // }
}
