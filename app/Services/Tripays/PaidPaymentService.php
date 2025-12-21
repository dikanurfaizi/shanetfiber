<?php

namespace App\Services\Tripays;

use App\Events\PaymentSucceeded;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use App\Models\Billings\Order;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerSubscription;
use App\Models\Receipt;
use App\Services\Networks\NetworkActivationService;
use App\Services\Receipts\CreateReceiptService;
use App\Services\Routerboard\IpStaticService;
use App\Services\Routerboard\Ppp\SecretService;
use Carbon\Carbon;

class PaidPaymentService
{
    protected SecretService $secretService;
    protected IpStaticService $ipStaticService;
    private CreateReceiptService $createReceiptService;
    private CustomerPaymentContextResolver $contextResolver;
    private BillingSettlementService $billingSettlementService;
    private NetworkActivationService $networkActivationService;

    public function __construct(
        SecretService $secretService,
        IpStaticService $ipStaticService,
        CreateReceiptService $createReceiptService,
        CustomerPaymentContextResolver $contextResolver,
        BillingSettlementService $billingSettlementService,
        NetworkActivationService $networkActivationService,
    ) {
        $this->secretService = $secretService;
        $this->ipStaticService = $ipStaticService;
        $this->createReceiptService = $createReceiptService;
        $this->contextResolver = $contextResolver;
        $this->billingSettlementService = $billingSettlementService;
        $this->networkActivationService = $networkActivationService;
    }

    public function handle(
        Order $order,
        Billing $billing,
        ?Carbon $paymentTime,
        string $traceId
    ): Receipt {
        $timer = new ExecutionTimer();

        try {
            /* ==========================================================
         * 1. Resolve core entities
         * ========================================================== */
            $customer = $order->customer ?? Customer::findOrFail($order->customer_id);
            $user     = auth()->user() ?? $customer->user;
            $paidAt   = $paymentTime ?? Carbon::now();

            $subscription = CustomerSubscription::where('customer_id', $customer->id)
                ->where('status', 'active')
                ->firstOrFail();

            /* ==========================================================
         * 2. Resolve & validate customer context
         * ========================================================== */
            $customerData = $this->contextResolver->resolve(
                $customer,
                $subscription,
                $traceId
            );

            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                    'user_id'     => $user?->id,
                    'validated'   => array_keys($customerData),
                ],
                200,
                '[Tripay] Customer context resolved',
                $traceId,
                $timer->lap()
            );

            /* ==========================================================
         * 3. Billing settlement
         * ========================================================== */
            $billingPayment = $this->billingSettlementService->settle(
                $billing,
                $order,
                $paidAt,
                $traceId
            );

            LogHelper::logInfo(
                null,
                [
                    'billing_id' => $billing->id,
                    'payment_id' => $billingPayment->id,
                ],
                200,
                '[Tripay] Billing settled',
                $traceId,
                $timer->lap()
            );

            /* ==========================================================
         * 4. Receipt creation
         * ========================================================== */
            $receiptResult = $this->createReceiptService->createReceipt(
                customerSubscription: $subscription,
                billing: $billing,
                billingPayment: $billingPayment,
                paymentData: [
                    'payment_method' => $order->payment_method,
                ],
                traceId: $traceId,
                createdBy: $user?->name
            );

            $receipt = $receiptResult['receipt'];

            LogHelper::logInfo(
                null,
                [
                    'receipt_id'  => $receipt->id,
                    'customer_id' => $customer->id,
                ],
                200,
                '[Tripay] Receipt created',
                $traceId,
                $timer->lap()
            );

            /* ==========================================================
         * 5. Network activation (PPP / IP Static)
         * ========================================================== */
            $this->networkActivationService->handle(
                customerData: $customerData,
                profile: $customer->paket->profile,
                periodeName: $billing->billingSubscription->billingPeriode->name,
                traceId: $traceId
            );

            LogHelper::logInfo(
                null,
                ['customer_id' => $customer->id],
                200,
                '[Tripay] Network activation processed',
                $traceId,
                $timer->lap()
            );

            /* ==========================================================
         * 6. Emit domain event
         * ========================================================== */
            event(new PaymentSucceeded(
                receiptId: $receipt->id,
                traceId: $traceId
            ));

            LogHelper::logInfo(
                null,
                ['receipt_id' => $receipt->id],
                200,
                '[Tripay] Payment success completed',
                $traceId,
                $timer->lap()
            );

            return $receipt;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'order_id'   => $order->id ?? null,
                    'billing_id' => $billing->id ?? null,
                    'error'      => $e->getMessage(),
                ],
                500,
                '[Tripay] processPaymentSuccess FAILED',
                $traceId,
                $timer->lap()
            );

            throw $e;
        }
    }
}
