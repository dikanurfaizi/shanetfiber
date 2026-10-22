<?php

namespace App\Services\Billings;

use App\Enums\OrderStatus;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingPayment;
use App\Models\Billings\Order;
use Illuminate\Support\Facades\DB;
use Throwable;

class PaymentCancellationService
{
    /**
     * Memproses pembatalan pembayaran (unpayment) secara transactional di Database lokal.
     */
    public function cancelPayment(BillingPayment $payment, string $traceId): array
    {
        $timer = new ExecutionTimer();
        $billingId = $payment->billing_id;
        $customerId = $payment->billing?->customer_id;

        LogHelper::logInfo('CancelPayment', [
            'billing_id'  => $billingId,
            'payment_id'  => $payment->id,
            'customer_id' => $customerId,
        ], 200, 'Starting transaction for payment cancellation.', $traceId, $timer->lap());

        try {
            $result = DB::transaction(function () use ($payment, $traceId) {
                $payment->lockForUpdate();

                $billing = $payment->billing()->lockForUpdate()->firstOrFail();

                // 1. Ambil data receipt asli lalu hapus receipt dan billing payment
                $originalReceipt = $this->handleReceipt($payment, $traceId);

                // 2. Kembalikan status billing ke 'BL' (Belum Lunas)
                $this->updateBillingStatus($billing, $traceId);

                // 3. Batalkan order terkait
                $this->updateRelatedOrders($billing, $traceId);

                return [
                    'billing'         => $billing->refresh(),
                    'originalReceipt' => $originalReceipt,
                ];
            });

            LogHelper::logInfo('CancelPayment', [
                'billing_id' => $billingId,
                'payment_id' => $payment->id,
                'receipt_id' => $result['originalReceipt']['id'] ?? null,
            ], 200, 'Payment cancellation processed successfully in DB.', $traceId, $timer->lap());

            return [
                'success'         => true,
                'billing'         => $result['billing'],
                'originalReceipt' => $result['originalReceipt'],
                'message'         => 'Pembatalan pembayaran untuk ' . ($result['billing']->customer->name ?? 'Pelanggan') . ' berhasil diproses.',
            ];
        } catch (Throwable $e) {
            LogHelper::logError('CancelPayment', [
                'billing_id'  => $billingId,
                'payment_id'  => $payment->id,
                'customer_id' => $customerId,
                'exception'   => class_basename($e),
                'message'     => $e->getMessage(),
            ], 500, 'Failed to cancel payment. Transaction rolled back.', $traceId, $timer->lap());

            return [
                'success' => false,
                'message' => 'Gagal melakukan unpayment: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Revert status billing menjadi 'BL' (Belum Lunas).
     */
    private function updateBillingStatus(Billing $billing, string $traceId): void
    {
        $timer = new ExecutionTimer();
        $userId = auth()->id();

        $billing->update([
            'updated_by' => $userId,
            'status'     => 'BL',
        ]);

        LogHelper::logInfo('UpdateBillingStatus', [
            'billing_id' => $billing->id,
            'updated_by' => $userId,
        ], 200, 'Successfully reverted billing status to BL (Belum Lunas).', $traceId, $timer->lap());
    }

    /**
     * Hard delete receipt dan billing payment.
     */
    private function handleReceipt(BillingPayment $payment, string $traceId): ?array
    {
        $timer = new ExecutionTimer();
        $payment->loadMissing('receipt');
        $receipt = $payment->receipt;

        if (!$receipt) {
            LogHelper::logWarning('HandleReceipt', [
                'payment_id' => $payment->id,
                'billing_id' => $payment->billing_id,
            ], 404, 'No receipt associated with this payment. Skipping delete.', $traceId, $timer->lap());

            return null;
        }

        $data = $receipt->toArray();

        $receipt->forceDelete();
        $payment->forceDelete();

        LogHelper::logInfo('HandleReceipt', [
            'receipt_id' => $data['id'] ?? null,
            'payment_id' => $payment->id,
            'billing_id' => $payment->billing_id,
        ], 200, 'Successfully executed hard delete on both Receipt and BillingPayment.', $traceId, $timer->lap());

        return $data;
    }

    /**
     * Update order terkait billing menjadi CANCELED.
     */
    private function updateRelatedOrders(Billing $billing, string $traceId): void
    {
        $timer = new ExecutionTimer();
        $orders = Order::where('billing_id', $billing->id)->get();

        if ($orders->isEmpty()) {
            return;
        }

        foreach ($orders as $order) {
            $order->update([
                'payment_status' => OrderStatus::CANCELED,
            ]);
        }

        LogHelper::logInfo('UpdateRelatedOrders', [
            'billing_id'  => $billing->id,
            'order_ids'   => $orders->pluck('id')->toArray(),
            'order_count' => $orders->count(),
        ], 200, 'Successfully updated related orders status to CANCELED.', $traceId, $timer->lap());
    }
}
