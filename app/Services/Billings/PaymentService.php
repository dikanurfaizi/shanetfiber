<?php

namespace App\Services\Billings;

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
        string $traceId,
        float $lastCheckpoint
    ): array {

        LogHelper::logInfo('Payment', [
            'billing_id'     => $billing->id,
            'customer_id'    => $billing->customer_id,
            'payment_method' => $paymentData['payment_method'] ?? null,
        ], 100, 'payment.start', $traceId, $lastCheckpoint);

        try {
            DB::beginTransaction();

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
                ->createReceipt($subscription, $billing, $billingPayment, $paymentData, $traceId, $lastCheckpoint);

            if (!$receiptResult['success']) {
                throw new RuntimeException("Receipt creation failed for billing_id {$billing->id}");
            }

            $now = microtime(true);
            $exec = round($now - $receiptResult['lastCheckpoint'], 3);
            $lastCheckpoint = $now;

            DB::commit();

            LogHelper::logInfo('Payment', [
                'billing_id' => $billing->id,
                'receipt_id' => $receiptResult['receipt']->id,
            ], 200, 'payment.db.commit', $traceId, $exec);

            // External actions (non-critical)
            if (config('services.shanetfiber.allow_mikrotik_actions')) {
                $now = microtime(true);
                $exec = round($now - $receiptResult['lastCheckpoint'], 3);
                $lastCheckpoint = $now;

                $this->paymentProcessingService
                    ->processPaymentActions($billing, $traceId);

                LogHelper::logInfo('Payment', [
                    'billing_id' => $billing->id,
                ], 200, 'payment.external.processed', $traceId, $exec);
            }

            return [
                'receipt'  => $receiptResult['receipt'],
                'trace_id' => $traceId,
                'lastCheckpoint' => $lastCheckpoint
            ];
        } catch (Throwable $e) {
            $now = microtime(true);
            $exec = round($now - $lastCheckpoint, 3);

            DB::rollBack();

            LogHelper::logError('Payment', [
                'billing_id' => $billing->id,
                'exception'  => class_basename($e),
                'message'    => $e->getMessage(),
            ], 500, 'payment.failed', $traceId, $exec);

            throw $e;
        }
    }
}
