<?php

namespace App\Services\Billings;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingPayment;
use App\Models\Customers\CustomerSubscription;
use App\Services\Receipts\CreateReceiptService;
use App\Services\Receipts\ReferenceIdGeneratorService;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class PaymentService
{
    public function __construct(
        private ReferenceIdGeneratorService $referenceIdGeneratorService,
        private CreateReceiptService $createReceiptService
    ) {}

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
        ], 100, '[PaymentService] payment start', $traceId, $timer->lap());

        try {
            $result = DB::transaction(function () use ($billing, $paymentData, $traceId) {
                // 1. Lock data billing dengan pessimestic locking untuk hindari race condition
                $billingModel = Billing::query()
                    ->whereKey($billing->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Guard Check: Jangan izinkan pembayaran jika sudah Lunas
                if ($billingModel->status === 'LS') {
                    throw new RuntimeException("Billing ID {$billing->id} has already been paid.");
                }

                // 2. Pemulihan status jika billing sebelumnya 'VD' (Void / Not Active Period)
                if ($billingModel->status === 'VD') {
                    $billingModel->loadMissing('billingSubscription.customerSubscription');

                    $billingSubscription = $billingModel->billingSubscription;
                    if ($billingSubscription) {
                        $billingSubscription->update([
                            'updated_by' => auth()->id(),
                            'status'     => 'active',
                        ]);

                        $billingSubscription->customerSubscription?->update([
                            'status'        => 'active',
                            'terminated_at' => null,
                        ]);
                    }
                }

                // 3. Ambil data langganan aktif pelanggan & kunci barisnya
                $subscription = CustomerSubscription::query()
                    ->with(['customer', 'package'])
                    ->where('customer_id', $billingModel->customer_id)
                    ->where('status', 'active')
                    ->lockForUpdate()
                    ->firstOrFail();

                // 4. Update status billing menjadi Lunas (LS)
                $billingModel->update([
                    'status'     => 'LS',
                    'updated_by' => auth()->id(),
                ]);

                // 5. Catat riwayat pembayaran
                $billingPayment = BillingPayment::create([
                    'billing_id'      => $billingModel->id,
                    'amount_paid'     => $billingModel->total_amount,
                    'payment_method'  => $paymentData['payment_method'],
                    'reference_id'    => $paymentData['reference_id'] ?? $this->referenceIdGeneratorService->generate('ACT'),
                    'paid_by_user_id' => auth()->id(),
                    'payment_time'    => now(),
                ]);

                // 6. Terbitkan kwitansi (Receipt)
                $receiptResult = $this->createReceiptService
                    ->createReceipt($subscription, $billingModel, $billingPayment, $paymentData, $traceId);

                if (empty($receiptResult['success'])) {
                    throw new RuntimeException("Receipt creation failed for billing_id {$billingModel->id}");
                }

                return [
                    'billing' => $billingModel->refresh(),
                    'receipt' => $receiptResult['receipt'],
                ];
            });

            LogHelper::logInfo(null, [
                'billing_id' => $billing->id,
                'receipt_id' => $result['receipt']->id,
            ], 200, '[PaymentService] payment db commit', $traceId, $timer->lap());

            return [
                'success'  => true,
                'billing'  => $result['billing'],
                'receipt'  => $result['receipt'],
                'trace_id' => $traceId,
            ];
        } catch (Throwable $e) {
            LogHelper::logError(null, [
                'billing_id' => $billing->id,
                'exception'  => class_basename($e),
                'message'    => $e->getMessage(),
            ], 500, '[PaymentService] payment failed', $traceId, $timer->lap());

            return [
                'error'   => true,
                'message' => $e->getMessage(),
            ];
        }
    }
}
