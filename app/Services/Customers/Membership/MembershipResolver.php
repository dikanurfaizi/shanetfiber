<?php

namespace App\Services\Customers\Membership;

use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingItem;
use App\Models\Billings\BillingPeriode;
use App\Models\Customers\Customer;
use App\Models\Membership\CustomerMembership;
use App\Models\Websystem;
use App\Services\Billings\BillingCreateService;
use App\Services\Customers\Provisioning\PackageBillingService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\Date;
use InvalidArgumentException;

class MembershipResolver
{
    public function __construct(
        private BillingCreateService $billingCreateService
    ) {}

    public function applyNow(
        Customer $customer,
        array $data,
        BillingPeriode $period,
        Billing $billing,
        string $traceId,
        bool $updateBilling
    ): void {

        LogHelper::logInfo(null, [
            'step' => 'MEMBERSHIP_APPLY_NOW',
            'update_billing' => $updateBilling
        ], 200, 'Membership Resolver', $traceId);

        // deactivate existing
        CustomerMembership::where('customer_id', $customer->id)
            ->where('is_active', 1)
            ->update([
                'is_active' => 0,
                'end_date'  => now(),
            ]);

        $custMembership = CustomerMembership::create([
            'customer_id'   => $customer->id,
            'membership_id' => $data['member_id'],
            'start_date'    => $period->start_date,
            'end_date'      => ($data['billing_option'] ?? 'monthly') === 'monthly'
                ? null
                : $period->end_date,
            'is_active'     => 1,
        ]);

        if ($updateBilling) {
            $this->syncMembershipDiscountToBilling(
                $billing,
                $customer,
                $period,
                $custMembership,
                $traceId
            );
        } else {
            LogHelper::logInfo(null, [
                'step' => 'MEMBERSHIP_BILLING_SYNC_SKIPPED'
            ], 200, 'Membership Resolver', $traceId);
        }
    }

    private function syncMembershipDiscountToBilling(
        Billing $billing,
        Customer $customer,
        BillingPeriode $period,
        CustomerMembership $custMembership,
        string $traceId
    ): void {
        $custMembership->load('membership');

        $membership = $custMembership->membership;
        if (!$membership) {
            throw new DomainException('Membership master not found.');
        }

        // 1) Tentukan base untuk diskon (paling aman: subtotal paket/addon tanpa tax)
        // Kamu bisa pilih basis lain, tapi HARUS konsisten.
        $base = (float) BillingItem::where('billing_id', $billing->id)
            ->whereIn('item_type', ['package', 'addon']) // exclude discount/tax
            ->sum('subtotal');

        // Kalau belum ada items, jangan bikin discount item 0 yang bikin bingung
        if ($base <= 0) {
            // optional: hapus discount item membership kalau ada
            BillingItem::where('billing_id', $billing->id)
                ->where('item_type', 'discount')
                ->where('source_type', 'membership')
                ->delete();
            return;
        }

        // 2) Hitung diskon
        $discount = match ($membership->discount_type) {
            'percent' => ($membership->discount_value / 100) * $base,
            'amount'  => (float) $membership->discount_value,
            default   => 0.0,
        };

        $discount = max(0.0, (float) $discount);

        // Optional guard: diskon tidak boleh melebihi base
        $discount = min($discount, $base);

        if ($discount <= 0) {
            BillingItem::where('billing_id', $billing->id)
                ->where('item_type', 'discount')
                ->where('source_type', 'membership')
                ->delete();
            return;
        }

        // 3) UPSERT discount item (idempotent)
        BillingItem::updateOrCreate(
            [
                'billing_id'   => $billing->id,
                'item_type'    => 'discount',
                'source_type'  => 'membership',
                'source_id'    => $membership->id,
            ],
            [
                'item_name' => $membership->name,
                'qty'       => 1,
                'price'     => -abs($discount),
                'subtotal'  => -abs($discount),
            ]
        );

        // 4) Recalc billing header (subtotal/tax/total) – konsisten dengan mekanisme kamu
        $subtotal = (float) BillingItem::where('billing_id', $billing->id)->sum('subtotal');

        // kalau kamu punya BillingCreateService/calc tax di tempat lain, panggil itu.
        // Minimal patch:

        $taxAmount  = $this->billingCreateService->calculateTax(Websystem::where('slug', 'websystem')->first(), $subtotal);

        $billing->update([
            'subtotal'     => $subtotal,
            'tax_amount'   => $taxAmount,
            'total_amount' => $subtotal + $taxAmount,
        ]);
    }

    public function scheduleNext(
        Customer $customer,
        array $data,
        Carbon $start,
        Carbon $end,
        string $traceId
    ): void {

        LogHelper::logInfo(null, [
            'step' => 'MEMBERSHIP_SCHEDULE_NEXT',
            'start' => $start->toDateString(),
            'end' => $end->toDateString()
        ], 200, 'Membership Resolver', $traceId);

        CustomerMembership::create([
            'customer_id'   => $customer->id,
            'membership_id' => $data['member_id'],
            'start_date'    => $start,
            'end_date'      => $end,
            'is_active'     => 0, // ❗ belum aktif
        ]);
    }

    public function remove(Customer $customer, string $traceId): void
    {
        LogHelper::logInfo(null, [
            'step' => 'MEMBERSHIP_REMOVE_RESOLVER'
        ], 200, 'Membership Resolver', $traceId);

        CustomerMembership::where('customer_id', $customer->id)
            ->where('is_active', 1)
            ->update([
                'is_active' => 0,
                'end_date'  => now(),
            ]);
    }
}
