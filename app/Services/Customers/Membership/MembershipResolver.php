<?php

namespace App\Services\Customers\Membership;

use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingItem;
use App\Models\Billings\BillingPeriode;
use App\Models\Customers\Customer;
use App\Models\Membership\CustomerMembership;
use App\Services\Billings\BillingCreateService;
use App\Services\Customers\Provisioning\PackageBillingService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\Date;
use InvalidArgumentException;

class MembershipResolver
{
    public function __construct(
        private BillingCreateService $billingCreateService,
        private PackageBillingService $packageBillingService
    ) {}

    /* ==========================================================
     * Helpers: Membership compare (pre-rotate)
     * ========================================================== */
    public function resolve(
        Customer $customer,
        array $data,
        BillingPeriode $billingPeriode,
        string $traceId
    ): ?CustomerMembership {

        $incomingMemberId = (int) ($data['member_id'] ?? 0);

        // Ambil membership aktif saat ini (jika ada)
        $activeMembership = CustomerMembership::where('customer_id', $customer->id)
            ->where('is_active', 1)
            ->latest('id')
            ->first();

        LogHelper::logInfo(null, [
            'customer_id'          => $customer->id,
            'active_membership_id' => $activeMembership?->id,
            'incoming_member_id'   => $incomingMemberId,
        ], 200, 'Resolving membership', $traceId);

        /**
         * ==========================================================
         * CASE 0 — Tidak ada member_id di payload
         * ==========================================================
         * Artinya: jangan ubah apa pun
         */
        if (!array_key_exists('member_id', $data)) {
            return $activeMembership;
        }

        /**
         * ==========================================================
         * CASE 1 — Incoming member_id = 0 → REMOVE MEMBERSHIP
         * ==========================================================
         */
        if ($incomingMemberId === 0) {

            if ($activeMembership) {
                $activeMembership->update([
                    'is_active' => 0,
                    'end_date'  => now(),
                ]);

                LogHelper::logInfo(null, [
                    'customer_id' => $customer->id,
                    'membership_id' => $activeMembership->membership_id,
                ], 200, 'Membership removed', $traceId);
            }

            return null;
        }

        // Tentukan periode default
        $startDate = $billingPeriode->start_date;
        $endDate   = $billingPeriode->end_date;

        /**
         * ==========================================================
         * CASE 2 — Belum pernah punya membership
         * ==========================================================
         */
        if (!$activeMembership) {

            return CustomerMembership::create([
                'customer_id'   => $customer->id,
                'membership_id' => $incomingMemberId,
                'start_date'    => $startDate,
                'end_date'      => $data['billing_option'] === 'monthly'
                    ? null
                    : $endDate,
                'is_active'     => 1,
            ]);
        }

        /**
         * ==========================================================
         * CASE 3 — Membership SAMA → UPDATE PERIODE
         * ==========================================================
         */
        if ((int) $activeMembership->membership_id === $incomingMemberId) {

            $activeMembership->update([
                'start_date' => $startDate,
                'end_date'   => ($data['billing_option'] ?? null) === 'monthly'
                    ? null
                    : $endDate,
                'is_active'  => 1,
            ]);

            return $activeMembership;
        }

        /**
         * ==========================================================
         * CASE 4 — Membership BERUBAH → ROTATE
         * ==========================================================
         */
        $activeMembership->update([
            'is_active' => 0,
            'end_date'  => now(),
        ]);

        if ($data['billing_option'] === 'one_time') {

            if (empty($data['billing_periode_name'])) {
                throw new InvalidArgumentException(
                    'billing_periode_name is required for one_time billing'
                );
            }

            $startDate = Carbon::createFromFormat(
                'F Y',
                $data['billing_periode_name']
            )->startOfMonth();

            $endDate = $startDate->copy()->endOfMonth();
        }

        return CustomerMembership::create([
            'customer_id'   => $customer->id,
            'membership_id' => $incomingMemberId,
            'start_date'    => $startDate,
            'end_date'      => $endDate,
            'is_active'     => 1,
        ]);
    }

    public function applyNow(
        Customer $customer,
        array $data,
        BillingPeriode $period,
        Billing $billing,
        string $traceId
    ): void {
        // deactivate existing
        CustomerMembership::where('customer_id', $customer->id)
            ->where('is_active', 1)
            ->update([
                'is_active' => 0,
                'end_date'  => now(),
            ]);

        $custMembership = $this->resolve(
            $customer,
            $data,
            $period,
            $traceId
        );

        // sync discount item into billing_items (idempotent)
        $this->syncMembershipDiscountToBilling(
            $billing,
            $customer,
            $period,
            $custMembership,
            $traceId
        );
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

        $taxAmount  = $this->billingCreateService->calculateTax($this->packageBillingService->resolveWebsystem(), $subtotal);

        $billing->update([
            'subtotal'     => $subtotal,
            'tax_amount'   => $taxAmount,
            'total_amount' => $subtotal + $taxAmount,
        ]);
    }

    public function scheduleNext(
        Customer $customer,
        array $data,
        Carbon $nextPeriod_start_date,
        Carbon $nextPeriod_end_date,
        string $traceId
    ): void {

        CustomerMembership::create([
            'customer_id'  => $customer->id,
            'membership_id' => $data['member_id'],
            'start_date'   => $nextPeriod_start_date,
            'end_date'     => $nextPeriod_end_date,
            'is_active'    => 1,
        ]);
    }
}
