<?php

namespace App\Services\Tripays;

use App\Enums\OrderStatus;
use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingPayment;
use App\Models\Billings\Order;
use App\Models\Customers\Customer;
use App\Models\Membership\CustomerMembership;
use App\Models\Receipt;
use Illuminate\Support\Facades\DB;

class UnpaidPaymentService
{
    public function __construct() {}

    public function handle(Order $order, Billing $billing, string $traceId): void
    {
        $customer = $order->customer ?? Customer::findOrFail($order->customer_id);

        DB::transaction(function () use ($order, $billing, $customer, $traceId) {

            // (Optional) lock untuk menghindari race saat callback dobel
            $billing->lockForUpdate();
            $order->lockForUpdate();

            /* ==========================================================
         * 1) Rollback membership (jika one-time/1 bulan & memang di-consume saat paid)
         * ========================================================== */
            $this->rollbackMembershipIfNeeded($customer, $billing, $traceId);

            /* ==========================================================
         * 2) Cari billing payments untuk billing ini
         * ========================================================== */
            $payments = BillingPayment::where('billing_id', $billing->id)->get();

            if ($payments->isNotEmpty()) {
                $paymentIds = $payments->pluck('id')->all();

                // 2a) Hard delete receipts yang terkait billing_payment_id tsb
                // Kalau model Receipt pakai SoftDeletes, pakai withTrashed + forceDelete
                Receipt::withTrashed()
                    ->whereIn('billing_payment_id', $paymentIds)
                    ->get()
                    ->each(function ($r) use ($traceId) {
                        $r->forceDelete();
                    });

                LogHelper::logInfo(null, [
                    'billing_id' => $billing->id,
                    'payment_ids' => $paymentIds,
                    'receipts_force_deleted' => true,
                ], 200, '[Tripay] Receipts force deleted (rollback)', $traceId);

                // 2b) Delete billing payments
                BillingPayment::whereIn('id', $paymentIds)->delete();

                LogHelper::logInfo(null, [
                    'billing_id' => $billing->id,
                    'deleted_payments' => $paymentIds,
                ], 200, '[Tripay] Billing payments deleted (rollback)', $traceId);
            }

            /* ==========================================================
         * 3) Kalau billing punya receipt_id legacy, hapus juga (optional)
         * ========================================================== */
            if ($billing->receipt_id) {
                // kalau receipt_id ini mengacu ke receipts.id
                $receipt = Receipt::withTrashed()->find($billing->receipt_id);
                if ($receipt) {
                    $receipt->forceDelete();
                }
            }

            /* ==========================================================
         * 4) Update billing -> unpaid
         * ========================================================== */
            $billing->update([
                'status'       => 'BL',
            ]);

            /* ==========================================================
         * 5) Update order -> unpaid (kalau kamu track)
         * ========================================================== */
            $order->update([
                'payment_status' => OrderStatus::UNPAID->value,
                'payment_time'   => null,
            ]);

            LogHelper::logInfo(null, [
                'order_id'   => $order->id,
                'billing_id' => $billing->id,
                'status'     => 'BL',
            ], 200, '[Tripay] Billing marked as unpaid (rollback completed)', $traceId);
        });
    }

    private function rollbackMembershipIfNeeded(Customer $customer, Billing $billing, string $traceId): void
    {
        $periode = $billing->billingSubscription?->billingPeriode;
        if (!$periode) return;

        // contoh: periode punya month/year atau start/end date
        $periodeStart = $periode->start_date ?? null;
        $periodeEnd   = $periode->end_date ?? null;

        // Ambil membership terakhir customer
        $latest = CustomerMembership::where('customer_id', $customer->id)
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        if (!$latest) return;

        // Rule contoh: membership 1 bulan (end_date not null) dan sekarang inactive -> re-activate
        // dan masih relevan dengan periode billing yang di-rollback
        if (
            $latest->is_active == false &&
            $latest->end_date !== null &&
            $periodeEnd && $latest->end_date->gte($periodeEnd) // atau cocok bulan/tahun
        ) {
            $latest->update(['is_active' => true]);

            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
                'membership_id' => $latest->id,
            ], 200, '[Tripay] Membership re-activated (rollback unpaid)', $traceId);
        }
    }
}
