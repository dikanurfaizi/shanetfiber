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
use Carbon\Carbon;
use DomainException;

class MembershipResolver
{
    public function __construct(
        private BillingCreateService $billingCreateService
    ) {}

    /**
     * Mengelola status keaktifan membership agar tidak tumpang tindih
     * antara Monthly dan One-Time Period.
     */
    private function resolveMembershipStatus(Customer $customer, int $membershipId, string $billingOption, $startDate, $endDate, string $traceId): CustomerMembership
    {
        // 1. Bersihkan semua membership aktif milik customer ini agar tidak tumpang tindih
        CustomerMembership::where('customer_id', $customer->id)
            ->where('is_active', 1)
            ->update([
                'is_active' => 0,
                'end_date'  => now()
            ]);

        // 2. Cari apakah skema membership yang sama (Customer & Membership) sudah pernah ada di DB
        $existing = CustomerMembership::where('customer_id', $customer->id)
            ->where('membership_id', $membershipId)
            ->first();

        if ($existing) {
            $isExistingMonthly = is_null($existing->end_date);

            // JIKA AKAN MENGUBAH KE ONE-TIME PERIOD
            if ($billingOption === 'one_time') {
                if ($isExistingMonthly) {
                    $existing->update(['is_active' => 0]);
                    LogHelper::logInfo(null, ['membership_id' => $membershipId], 200, '[Membership Resolver] Deactivated existing Monthly to prepare One-Time', $traceId);
                }

                return CustomerMembership::create([
                    'customer_id'   => $customer->id,
                    'membership_id' => $membershipId,
                    'start_date'    => $startDate,
                    'end_date'      => $endDate,
                    'is_active'     => 1,
                ]);
            }

            // JIKA AKAN MENGUBAH KEMBALI KE MONTHLY
            if ($billingOption === 'monthly') {
                CustomerMembership::where('customer_id', $customer->id)
                    ->where('membership_id', $membershipId)
                    ->whereNotNull('end_date')
                    ->update(['is_active' => 0]);

                $monthlyRecord = CustomerMembership::where('customer_id', $customer->id)
                    ->where('membership_id', $membershipId)
                    ->whereNull('end_date')
                    ->first();

                if ($monthlyRecord) {
                    $monthlyRecord->update([
                        'is_active'  => 1,
                        'start_date' => $startDate
                    ]);

                    LogHelper::logInfo(null, ['membership_id' => $membershipId], 200, '[Membership Resolver] Re-activated existing Monthly and disabled One-Time items', $traceId);
                    return $monthlyRecord;
                }
            }
        }

        return CustomerMembership::create([
            'customer_id'   => $customer->id,
            'membership_id' => $membershipId,
            'start_date'    => $startDate,
            'end_date'      => $billingOption === 'monthly' ? null : $endDate,
            'is_active'     => 1,
        ]);
    }

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

        $billingOption = $data['billing_option'] ?? 'monthly';

        // Panggil pengelola status keaktifan record membership
        $custMembership = $this->resolveMembershipStatus(
            $customer,
            $data['member_id'],
            $billingOption,
            $period->start_date,
            $period->end_date,
            $traceId
        );

        // RULES: Jika berpindah ke one_time pada billing berjalan yang BELUM LUNAS,
        // diskon membership untuk bulan sekarang harus dihapus/hilang.
        $isBillingUnsettled = !in_array($billing->status, ['LS', 'VD'], true);

        if ($billingOption === 'one_time' && $isBillingUnsettled) {
            $updateBilling = false; // Override flag agar tidak men-sync diskon ke billing berjalan

            LogHelper::logInfo(null, [
                'billing_id' => $billing->id,
                'status' => $billing->status
            ], 200, '[Membership Resolver] Switch to One-Time detected on Unsettled current billing. Discount will be removed.', $traceId);
        }

        if ($updateBilling) {
            $this->syncMembershipDiscountToBilling(
                $billing,
                $customer,
                $period,
                $custMembership,
                $traceId
            );
        } else {
            // Hapus item diskon lama jika ada di database & hitung ulang total billing
            $hasDeleted = BillingItem::where('billing_id', $billing->id)
                ->where('item_type', 'discount')
                ->where('source_type', 'membership')
                ->delete();

            if ($hasDeleted && $isBillingUnsettled) {
                $this->recalculateBillingHeader($billing);
            }

            LogHelper::logInfo(null, [
                'step' => 'MEMBERSHIP_BILLING_SYNC_SKIPPED_AND_CLEANED'
            ], 200, 'Membership Resolver', $traceId);
        }
    }

    public function scheduleNext(
        Customer $customer,
        array $data,
        Carbon $start,
        Carbon $end,
        string $traceId,
        ?Billing $currentBilling = null // <-- Terima object billing saat ini
    ): void {
        LogHelper::logInfo(null, [
            'step' => 'MEMBERSHIP_SCHEDULE_NEXT',
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'billing_id' => $currentBilling?->id ?? null
        ], 200, 'Membership Resolver', $traceId);

        $billingOption = $data['billing_option'] ?? 'monthly';

        // 1. Kelola antrean data membership di DB (set is_active = 0 karena untuk masa depan)
        $custMembership = $this->resolveMembershipStatus(
            $customer,
            $data['member_id'],
            $billingOption,
            $start,
            $end,
            $traceId
        );
        $custMembership->update(['is_active' => 1]);

        // 2. CRUCIAL EDGE CASE PROTECTION:
        // Jika tipe baru adalah 'one_time', dan billing berjalan (current) saat ini BELUM LUNAS,
        // maka kita harus menghapus diskon membership dari billing bulan ini karena skema kontinuitas (monthly) sudah dibatalkan.
        if ($currentBilling && $billingOption === 'one_time') {
            $isBillingUnsettled = !in_array($currentBilling->status, ['LS', 'VD'], true);

            if ($isBillingUnsettled) {
                // Hapus item diskon membership dari invoice bulan ini
                $hasDeleted = BillingItem::where('billing_id', $currentBilling->id)
                    ->where('item_type', 'discount')
                    ->where('source_type', 'membership')
                    ->delete();

                if ($hasDeleted) {
                    // Hitung ulang subtotal, pajak, dan total invoice berjalan
                    $this->recalculateBillingHeader($currentBilling);

                    LogHelper::logInfo(null, [
                        'billing_id' => $currentBilling->id
                    ], 200, '[Membership Resolver] Future One-Time scheduled. Removed monthly membership discount from current unsettled billing.', $traceId);
                }
            }
        }
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

    /**
     * Menghitung ulang nilai header Billing (subtotal, tax, total_amount)
     */
    private function recalculateBillingHeader(Billing $billing): void
    {
        $subtotal = (float) BillingItem::where('billing_id', $billing->id)->sum('subtotal');
        $taxAmount = $this->billingCreateService->calculateTax(Websystem::where('slug', 'websystem')->first(), $subtotal);

        $billing->update([
            'subtotal'     => $subtotal,
            'tax_amount'   => $taxAmount,
            'total_amount' => $subtotal + $taxAmount,
        ]);
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

        $base = (float) BillingItem::where('billing_id', $billing->id)
            ->whereIn('item_type', ['package', 'addon'])
            ->sum('subtotal');

        if ($base <= 0) {
            BillingItem::where('billing_id', $billing->id)
                ->where('item_type', 'discount')
                ->where('source_type', 'membership')
                ->delete();
            return;
        }

        $discount = match ($membership->discount_type) {
            'percent' => ($membership->discount_value / 100) * $base,
            'amount'  => (float) $membership->discount_value,
            default   => 0.0,
        };

        $discount = max(0.0, (float) $discount);
        $discount = min($discount, $base);

        if ($discount <= 0) {
            BillingItem::where('billing_id', $billing->id)
                ->where('item_type', 'discount')
                ->where('source_type', 'membership')
                ->delete();
            return;
        }

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

        $this->recalculateBillingHeader($billing);
    }
}
