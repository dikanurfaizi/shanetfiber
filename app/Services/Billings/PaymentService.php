<?php

namespace App\Services\Billings;

use App\Helpers\ExecutionTimer;
use Illuminate\Support\Facades\DB;
use Throwable;
use RuntimeException;
use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingPayment;
use App\Models\Customers\CustomerSubscription;
use App\Services\Billings\PaymentProcessingService;
use App\Services\Receipts\CreateReceiptService;
use App\Services\Receipts\ReferenceIdGeneratorService;

class PaymentService
{
    private PaymentProcessingService $paymentProcessingService;
    private ReferenceIdGeneratorService $referenceIdGeneratorService;
    private CreateReceiptService $createReceiptService;

    public function __construct(
        PaymentProcessingService $paymentProcessingService,
        ReferenceIdGeneratorService $referenceIdGeneratorService,
        CreateReceiptService $createReceiptService
    ) {
        $this->paymentProcessingService = $paymentProcessingService;
        $this->referenceIdGeneratorService = $referenceIdGeneratorService;
        $this->createReceiptService = $createReceiptService;
    }

    public function processPayment(
        Billing $billing,
        array $paymentData,
        string $traceId
    ): array {
        $timer = new ExecutionTimer();
        LogHelper::logInfo(null, [
            'billing_id'     => $billing->id,
            'customer_id'    => $billing->customer_id,
            'payment_method' => $paymentData['payment_method'] ?? null,
        ], 100, 'payment.start', $traceId, $timer->lap());

        try {
            DB::beginTransaction();

            $billing = Billing::query()
                ->whereKey($billing->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($billing->status === 'VD') {
                $billing->billingSubscription->update([
                    'updated_by' => auth()->user()->id,
                    'status'      => 'active'
                ]);

                $billing->billingSubscription->customerSubscription->update([
                    'status' => 'active',
                    'terminated_at' => null,
                ]);
            }

            $subscription = CustomerSubscription::query()
                ->with(['customer', 'package'])
                ->where('customer_id', $billing->customer_id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->firstOrFail();

            // Update billing
            $billing->update([
                'status'     => 'LS',
                'updated_by' => auth()->id(),
            ]);

            // Insert billing payment
            $billingPayment = BillingPayment::create([
                'billing_id'      => $billing->id,
                'amount_paid'     => $billing->total_amount,
                'payment_method'  => $paymentData['payment_method'],
                'reference_id'    => $paymentData['reference_id']
                    ?? $this->referenceIdGeneratorService->generate('ACT'),
                'paid_by_user_id' => auth()->id(),
                'payment_time'    => now(),
            ]);

            // Create receipt
            $receiptResult = $this->createReceiptService
                ->createReceipt($subscription, $billing, $billingPayment, $paymentData, $traceId);

            if (!$receiptResult['success']) {
                throw new RuntimeException("Receipt creation failed for billing_id {$billing->id}");
            }

            DB::commit();

            LogHelper::logInfo(null, [
                'billing_id' => $billing->id,
                'receipt_id' => $receiptResult['receipt']->id,
            ], 200, 'payment.db.commit', $traceId, $timer->lap());

            // External actions (non-critical)
            if (config('services.shanetfiber.allow_mikrotik_actions')) {
                $this->paymentProcessingService
                    ->processPaymentActions($billing, $traceId);

                LogHelper::logInfo(null, [
                    'billing_id' => $billing->id,
                ], 200, 'payment.external.processed', $traceId, $timer->lap());
            }

            return [
                'receipt'  => $receiptResult['receipt'],
                'trace_id' => $traceId
            ];
        } catch (Throwable $e) {
            DB::rollBack();

            LogHelper::logError(null, [
                'billing_id' => $billing->id,
                'exception'  => class_basename($e),
                'message'    => $e->getMessage(),
            ], 500, 'payment.failed', $traceId, $timer->lap());

            throw $e;
        }
    }
}
