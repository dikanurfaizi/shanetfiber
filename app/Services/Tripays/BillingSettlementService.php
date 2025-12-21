<?php

namespace App\Services\Tripays;

use App\Enums\OrderStatus;
use App\Helpers\ExecutionTimer;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingPayment;
use App\Models\Billings\Order;
use App\Services\Receipts\ReferenceIdGeneratorService;
use App\Helpers\LogHelper;
use App\Models\Membership\CustomerMembership;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BillingSettlementService
{
    public function __construct(
        private ReferenceIdGeneratorService $referenceIdGenerator
    ) {}

    public function settle(
        Billing $billing,
        Order $order,
        Carbon $paidAt,
        string $traceId
    ): BillingPayment {
        $timer = new ExecutionTimer();
        return DB::transaction(function () use ($billing, $order, $paidAt, $traceId, $timer) {

            /* ===============================
         * 1. Update billing
         * =============================== */
            $billing->update([
                'status'     => 'LS',
            ]);

            /* ===============================
         * 2. Create billing payment
         * =============================== */
            $billingPayment = BillingPayment::create([
                'billing_id'   => $billing->id,
                'amount_paid'  => $billing->total_amount,
                'payment_time' => $paidAt,
                'method'       => $order->payment_method,
            ]);

            /* ===============================
         * 3. Update order
         * =============================== */
            $order->update([
                'payment_status' => OrderStatus::PAID->value,
                'payment_time'   => $paidAt,
            ]);

            /* ===============================
         * 4. Membership settlement (INI PENTING)
         * =============================== */
            $this->settleMembership(
                customerId: $order->customer_id,
                paidAt: $paidAt,
                traceId: $traceId
            );

            LogHelper::logInfo(
                null,
                ['billing_id' => $billing->id],
                200,
                '[BillingSettlement] Completed',
                $traceId,
                $timer->lap()
            );

            return $billingPayment;
        });
    }

    protected function settleMembership(
        int $customerId,
        Carbon $paidAt,
        string $traceId
    ): void {
        $timer = new ExecutionTimer();
        $membership = CustomerMembership::where('customer_id', $customerId)
            ->where('is_active', true)
            ->lockForUpdate()
            ->first();

        if (!$membership) {
            return;
        }

        // Rule: hanya membership 1 bulan
        if (
            $membership->duration_month === 1 &&
            $membership->end_date &&
            $membership->end_date->lte($paidAt)
        ) {
            $membership->update(['is_active' => false]);

            LogHelper::logInfo(
                null,
                [
                    'customer_id'  => $customerId,
                    'membership_id' => $membership->id,
                ],
                200,
                '[BillingSettlement] Membership deactivated',
                $traceId,
                $timer->lap()
            );
        }
    }
}
