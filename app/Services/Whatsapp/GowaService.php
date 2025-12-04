<?php

namespace App\Services\Whatsapp;

use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Billing\Billing;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Receipt;
use App\Models\Whatsapp\WhatsappGateway;
use App\Services\Messaging\Collectors\BankInfoCollector;
use App\Services\Messaging\Collectors\BillingEligibleCollector;
use App\Services\Messaging\Collectors\BillingPackageCollector;
use App\Services\Messaging\Collectors\BillingPeriodCollector;
use App\Services\Messaging\Collectors\FormatterCollector;
use App\Services\Messaging\Collectors\BroadcastMidMonthCollector;
use App\Services\Messaging\Collectors\UnpaidSummaryCollector;
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
        private BankInfoCollector $bankInfoCollector,
        private BillingPackageCollector $packageCollector,
        private BillingEligibleCollector $eligibleCollector,
        private BillingPeriodCollector $periodCollector,
        private FormatterCollector $formatterCollector,
        private BroadcastMidMonthCollector $collector,
        private FormatReceiver $formatNumber,
        private UnpaidSummaryCollector $unpaidSummaryCollector,
    ) {}

    // ============================================
    // =====             Activation           =====

    public function activation(int $customerId, string $action, string $traceId): void
    {
        try {
            LogHelper::logInfo(null, ['customerId' => $customerId], 200, '[Activation] Start activation', $traceId);

            $customer = Customer::with(['package', 'member', 'user'])->find($customerId);

            if (! $customer) {
                LogHelper::logWarning(null, ['customer_id' => $customerId], 404, '[Activation] Customer not found', $traceId);

                return;
            }

            if (empty($customer->contact)) {
                LogHelper::logWarning(null, ['customer_id' => $customerId], 400, '[Activation] Empty contact number', $traceId);

                return;
            }

            $this->dispatcher->dispatch(
                $action,
                fn(string $category) => new ActivationHandler(
                    customer: $customer,
                    builder: $this->builder,
                    getBankInfo: fn($userId) => $this->bankInfoCollector->getBankInfo($userId),
                    category: $category
                ),
                'notification',
                $traceId,
                delay: now()->addSeconds(6)
            );
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'customer_id' => $customerId ?? null,
                'action' => $action,
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                ],
            ], 500, '[Activation] Failed to run handler', $traceId);
        }
    }

    // =====             Activation           =====
    // ============================================
    // =====            Create Order          =====

    public function orderCreated(int $billingId, int $orderId, string $action, string $traceId): void
    {
        try {
            $billing = Billing::with(['customer.user', 'billingSubscription.customerSubscription.package'])
                ->find($billingId);

            $order = Order::find($orderId);

            if (! $billing || ! $order) {
                LogHelper::logWarning(null, [
                    'billing_id' => $billingId,
                    'order_id' => $orderId,
                ], 404, 'Data billing atau order tidak ditemukan', $traceId);

                return;
            }

            $this->dispatcher->dispatch(
                $action,
                fn(string $category) => new CreateOrderHandler(
                    billing: $billing,
                    order: $order,
                    builder: $this->builder,
                    category: $category
                ),
                'notification',
                $traceId,
                delay: now()->addSeconds(6)
            );

            LogHelper::logInfo(null, [
                'billing_id' => $billingId,
                'order_id' => $orderId,
                'trace_id' => $traceId,
            ], 200, '[createOrder] Handler CreateOrderHandler berhasil dijalankan', $traceId);
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'billing_id' => $billingId ?? null,
                'order_id' => $orderId ?? null,
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                ],
            ], 500, '[createOrder] Gagal memproses handler CreateOrderHandler', $traceId);
        }
    }

    // =====            Create Order          =====
    // ============================================
    // =====          Payment Package         =====

    /**Payment package
     * Unpayment package
     *
     */
    public function payment_package(Customer $customer, Billing $billing, Receipt $receipt, string $action, string $traceId)
    {
        try {
            // Mapping handler
            $statusHandlers = ['paylater', 'unpayment'];
            $paymentHandlers = ['transfer', 'cash', 'payment_gateway'];

            // 1️⃣ Gunakan BillingStatusHandler
            if (in_array($action, $statusHandlers, true)) {
                $this->dispatcher->dispatch(
                    $action,
                    fn(string $category) => new BillingStatusHandler(
                        customer: $customer,
                        billing: $billing,
                        type: $action,
                        builder: $this->builder,
                        getBankInfo: fn($userId) => $this->bankInfoCollector->getBankInfo($userId),
                        category: $category
                    ),
                    'notification',
                    $traceId,
                    delay: now()->addSeconds(6)
                );

                return;
            }

            // 2️⃣ Handler lain (payment transfer/cash/payment_gateway) pakai PaymentHandler
            if (in_array($action, $paymentHandlers, true)) {
                $this->dispatcher->dispatch(
                    'payment',
                    fn(string $category) => new PaymentHandler(
                        billing: $billing,
                        receipt: $receipt,
                        channel: $action,
                        builder: $this->builder,
                        category: $category
                    ),
                    'notification',
                    $traceId,
                    delay: now()->addSeconds(6)
                );

                return;
            }

            // 3️⃣ Fallback log
            LogHelper::logWarning(
                null,
                ['billing_id' => $billing->id, 'action' => $action],
                400,
                "Aksi {$action} tidak dikenal pada payment_package",
                $traceId
            );
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'billing_id' => $billing->id ?? null,
                    'action' => $action,
                    'error' => [
                        'code' => $e->getCode(),
                        'message' => $e->getMessage(),
                    ],
                ],
                500,
                'Gagal memproses payment_package via handler baru',
                $traceId
            );
        }
    }

    // =====             Payment Package            =====
    // ==================================================
    // ===== Broadcast at the beginning of the month ====

    public function broadcastMonthly(Carbon $startDate, Carbon $endDate, string $action, string $traceId): void
    {
        $startTime = microtime(true);

        try {
            $periode = $this->periodCollector->getLastBillingPeriod($traceId);
            if (! $periode) {
                return;
            }

            $billings = $this->eligibleCollector->getEligibleBillings($startDate, $endDate, $periode->id, $traceId);
            if ($billings->isEmpty()) {
                LogHelper::logWarning(null, ['periode_id' => $periode->id], 404, 'No billing found', $traceId);

                return;
            }

            $delaySeconds = 0;
            foreach ($billings as $billing) {
                $this->dispatcher->dispatch(
                    $action,
                    fn(string $category) => new BroadcastMonthlyHandler(
                        billingRow: $billing,
                        builder: $this->builder,
                        getBankInfo: fn(int $userId) => $this->bankInfoCollector->getBankInfo($userId),
                        getBillingPackages: fn(int $custId) => $this->packageCollector->getBillingPackages($custId),
                        formatPackageInfo: fn($pkg) => $this->formatterCollector->formatPackageInfo($pkg),
                        category: $category
                    ),
                    'notification',
                    $traceId,
                    delay: now()->addSeconds($delaySeconds)
                );

                $delaySeconds += 30;
            }

            LogHelper::logInfo(null, [
                'periode' => $periode->name,
                'count' => $billings->count(),
            ], 200, 'Broadcast monthly done', $traceId, round(microtime(true) - $startTime, 2));
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'trace_id' => $traceId,
                'error' => ['code' => $e->getCode(), 'message' => $e->getMessage()],
            ], 500, '[broadcastMonthly] Failed', $traceId);
        }
    }

    // ===== Broadcast at the beginning of the month ====
    // ==================================================
    // ===== Broadcast at the middle of the month =======

    public function broadcastMidMonth(string $traceId): void
    {
        try {
            $today = Carbon::today();
            $types = ['past_due', 'due_today', 'upcoming'];

            $delayCounter = 0;
            foreach ($types as $type) {
                $billings = $this->collector->collect($today, $type);
                $count = $billings->count();
                LogHelper::logInfo(null, [], 200, "Found {$count} billing(s) for '{$type}'", $traceId);

                $slug = match ($type) {
                    'past_due'  => 'billing_due_soon',
                    'due_today' => 'billing_due_today',
                    'upcoming'  => 'billing_overdue',
                    default     => 'billing_overdue',
                };

                foreach ($billings as $billing) {
                    $this->dispatcher->dispatch(
                        $slug,
                        fn(string $category) => new BroadcastMidMonthHandler(
                            billing: $billing,
                            type: $slug,
                            builder: $this->builder,
                            getBankInfo: fn($userId) => $this->bankInfoCollector->getBankInfo($userId),
                            getBillingPackages: fn($custId) => $this->packageCollector->getBillingPackages($custId),
                            formatPackageInfo: fn($pkg) => $this->formatterCollector->formatPackageInfo($pkg),
                            getUnpaidSummary: fn($custId) => $this->unpaidSummaryCollector->getUnpaidSummary($custId),
                            category: $category
                        ),
                        queue: 'notification',
                        traceId: $traceId,
                        delay: now()->addSeconds($delayCounter)
                    );
                    $delayCounter += 30;
                }
            }
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'trace_id' => $traceId,
                    'error' => [
                        'code' => $e->getCode(),
                        'message' => $e->getMessage(),
                    ],
                ],
                500,
                '[Broadcast Mid Month] Failed to dispatch handler',
                $traceId
            );
        }
    }

    // ===== Broadcast at the middle of the month =======
    // ==================================================
    // =====          Changes package         ===========

    public function changesPackage(int $customerId, ?string $oldPackageName, ?int $oldPackagePrice, string $traceId): void
    {
        try {
            $this->dispatcher->dispatch(
                'changes_package',
                fn(string $category) => new ChangesPackageHandler(
                    customerId: $customerId,
                    oldPackageName: $oldPackageName,
                    oldPackagePrice: $oldPackagePrice,
                    builder: $this->builder,
                    getBankInfo: fn(int $userId) => $this->bankInfoCollector->getBankInfo($userId),
                    formatReceiver: fn(string $number) => $this->formatNumber->formatReceiver($number, null, false),
                    category: $category
                ),
                'notification',
                $traceId,
                delay: now()->addSeconds(6)
            );
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'trace_id' => $traceId,
                    'customer_id' => $customerId,
                    'old_package' => $oldPackageName,
                    'old_price' => $oldPackagePrice,
                    'error' => [
                        'code' => $e->getCode(),
                        'message' => $e->getMessage(),
                    ],
                ],
                500,
                '[ChangesPackage] Failed to dispatch handler',
                $traceId
            );
        }
    }

    // =====            Changes package         =====
    // ============================================
    // =====   Test WhatsApp Message Manual   =====

    public function test(WhatsappGateway $gateway, string $receive_number)
    {
        $traceId = (string) Str::uuid();

        try {

            $activeAccount = $gateway->accounts()->where('is_active', true)->first();

            LogHelper::logInfo(null, [
                'active_account' => $activeAccount?->id,
                'receive_number' => $receive_number,
                'gateway_id' => $gateway->id,
            ], 100, '[TestGateway-Gowa] Start testing Gowa gateway', $traceId);

            if (empty($receive_number)) {
                return back()->with('error', 'Nomor penerima wajib diisi.');
            }

            // Dispatch ke handler
            $dispatcher = app(MessageDispatcher::class);
            $templateSlug = 'test_message';

            $dispatcher->dispatch(
                action: $templateSlug,
                handlerFactory: fn(string $category) => new TestMessageHandler(
                    receiver: $receive_number,
                    gatewayId: $gateway->id,
                    templateSlug: $templateSlug,
                    category: $category
                ),
                queue: 'notification',
                traceId: $traceId
            );

            LogHelper::logInfo(null, [
                'receive_number' => $receive_number,
                'gateway_id' => $gateway->id,
            ], 200, '[TestGateway-Gowa] Message queued successfully', $traceId);

            return back()->with('success', 'Pesan uji berhasil dikirim.');
        } catch (\Throwable $e) {

            LogHelper::logError(null, [
                'gateway_id' => $gateway->id,
                'receive_number' => $receive_number,
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                ],
            ], 500, '[TestGateway-Gowa] Failed to test gateway', $traceId);

            return back()->with('error', 'Gagal mengirim pesan uji.');
        }
    }

    public function menu($custName, $custContact, $traceId)
    {
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
                'default',
                $traceId
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
}
