<?php

namespace App\Helpers;

use App\Models\Packages\PackageDuration;
use App\Models\Membership\CustomerMembership;
use Carbon\Carbon;

class SubscriptionPricingCalculator
{
    /**
     * Hitung harga berlangganan dasar (Package Price + Duration Discount + Prorate)
     * beserta metadata kalkulasi untuk UI Wizard/AJAX.
     * 
     * @param PackageDuration $packageDuration Model paket durasi terikat
     * @param mixed $isProrateInput Input request prorate (1/0/true/false)
     * @param float $discountNominal Input potongan diskon dari membership/wizard
     * @return array
     */
    public static function calculateSubscriptionPrice(
        PackageDuration $packageDuration,
        mixed $isProrateInput,
        float $discountNominal = 0
    ): array {
        $package  = $packageDuration->package;
        $duration = $packageDuration->duration;

        $basePrice      = (float) ($package->price ?? 0);
        $durationMonths = (int) ($duration->duration_months ?? 1);

        // 1. Hitung Diskon Durasi Paket (Diskon Paket Kontrak)
        $discountType  = $packageDuration->discount_type ?? 'none';
        $discountValue = (float) ($packageDuration->discount_value ?? 0);

        $priceAfterDurationDiscount = $basePrice;
        if ($discountType === 'percent' && $discountValue > 0) {
            $priceAfterDurationDiscount = max(0, $basePrice - ($basePrice * ($discountValue / 100)));
        } elseif ($discountType === 'amount' && $discountValue > 0) {
            $priceAfterDurationDiscount = max(0, $basePrice - $discountValue);
        }

        // 2. Info Tanggal & Hari Aktif
        $now              = Carbon::now();
        $currentDay       = $now->day;
        $totalDaysInMonth = $now->daysInMonth;
        $activeDays       = ($totalDaysInMonth - $currentDay) + 1;

        // 3. Keputusan Prorate Aktual (Meminta DAN Dibuat > tgl 15)
        $isRequestedProrate = filter_var($isProrateInput, FILTER_VALIDATE_BOOLEAN) || $isProrateInput == '1';
        $isActualProrated   = $isRequestedProrate && ($currentDay > 15);

        if ($isActualProrated) {
            $exactProratePriceRaw   = ($activeDays / $totalDaysInMonth) * $priceAfterDurationDiscount;
            $firstMonthPackagePrice = ceil($exactProratePriceRaw / 500) * 500; // Pembulatan ke 500 ke atas
            $label                  = __('customer.wizard.label.prorate');
        } else {
            $firstMonthPackagePrice = $priceAfterDurationDiscount;
            $label                  = __('customer.wizard.label.full_cost');
        }

        // Akumulasi harga jika multi-bulan (> 1 bulan)
        $extraMonthsCost = 0;
        if ($durationMonths > 1) {
            $extraMonthsCost = $priceAfterDurationDiscount * ($durationMonths - 1);
        }

        // HARGA SUBSCRIPTION BERSIH (Murni Paket + Durasi + Prorate, TANPA Diskon Membership)
        $subscriptionPrice = $firstMonthPackagePrice + $extraMonthsCost;

        // ALOKASI DISKON MEMBERSHIP / PROMO TERPISAH
        $finalDiscountAmount = min($subscriptionPrice, max(0, $discountNominal));
        $totalAmountToPay    = max(0, $subscriptionPrice - $finalDiscountAmount);

        return [
            // 🔑 Field Kunci untuk Database & Billing Items
            'base_package_price'      => (float) $basePrice,
            'subscription_price'      => (float) $subscriptionPrice,     // 💡 Disimpan ke customer_subscriptions.price_final
            'discount_amount'         => (float) $finalDiscountAmount,    // 💡 Untuk billing_items (item_type = discount)
            'total_amount_to_pay'     => (float) $totalAmountToPay,       // 💡 Total tagihan awal
            'duration_discount_type'  => $discountType,
            'duration_discount_value' => $discountValue,
            'is_requested_prorate'    => $isRequestedProrate,
            'is_prorated'             => $isActualProrated,              // 💡 Disimpan ke customer_subscriptions.is_prorated

            // ℹ️ Informasi Metadata Kalkulasi (LENGKAP UNTUK UI WIZARD & AJAX)
            'calculation_type_label'  => $label,
            'active_days'             => $activeDays,
            'total_days'              => $totalDaysInMonth,
            'current_day'             => $currentDay,
            'current_month'           => $now->translatedFormat('F'),
            'duration_months'         => $durationMonths,
        ];
    }

    /**
     * Hitung Diskon Membership yang berlaku untuk Periode Billing saat ini.
     */
    public static function resolveMembershipDiscount(
        int $customerId,
        float $basePriceForDiscount,
        Carbon $periodStart,
        Carbon $periodEnd
    ): ?array {
        $membership = CustomerMembership::with('membership')
            ->where('customer_id', $customerId)
            ->where('is_active', 1)
            ->where('start_date', '<=', $periodEnd)
            ->where(function ($q) use ($periodStart) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $periodStart);
            })
            ->first();

        if (!$membership || !$membership->membership) {
            return null;
        }

        if ($membership->end_date && $membership->end_date < $periodEnd) {
            $membership->update(['is_active' => false]);
            return null;
        }

        $rule           = $membership->membership;
        $discountAmount = 0;

        if ($rule->discount_type === 'percent') {
            $discountAmount = ($rule->discount_value / 100) * $basePriceForDiscount;
        } elseif ($rule->discount_type === 'amount') {
            $discountAmount = $rule->discount_value;
        }

        if ($discountAmount <= 0) {
            return null;
        }

        return [
            'membership_id'   => $rule->id,
            'item_name'       => 'Diskon ' . $rule->name,
            'discount_amount' => (float) $discountAmount,
        ];
    }
}
