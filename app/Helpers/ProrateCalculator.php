<?php

namespace App\Helpers;

use Carbon\Carbon;

class ProrateCalculator
{
    /**
     * Menghitung harga final berdasarkan status prorate, durasi, dan diskon.
     * 
     * @param float|int $packagePrice Harga dasar paket asli
     * @param float|int $discountNominal Potongan diskon nominal membership
     * @param int|string|bool|null $isProrate Status keputusan prorate (1/0/true/false)
     * @param int $durationMonths Jumlah durasi paket dalam bulan (default: 1)
     * @return array
     */
    public static function calculate(
        float $packagePrice,
        float $discountNominal,
        $isProrate,
        int $durationMonths = 1
    ): array {
        $now = Carbon::now();
        $currentDay = $now->day;
        $totalDaysInMonth = $now->daysInMonth;

        // Sisa hari aktif pendaftaran
        $activeDays = ($totalDaysInMonth - $currentDay) + 1;

        // Normalisasi is_prorate ke boolean
        $isProrateTrue = filter_var($isProrate, FILTER_VALIDATE_BOOLEAN) || $isProrate == '1';

        // 1. Hitung harga bulan pertama SEBELUM diskon
        if ($isProrateTrue && $currentDay > 15) {
            $exactProratePriceRaw = ($activeDays / $totalDaysInMonth) * $packagePrice;
            // Pembulatan ke kelipatan Rp 500 ke atas untuk harga prorate mentah
            $priceFirstMonthBeforeDiscount = ceil($exactProratePriceRaw / 500) * 500;
            $label = __('customer.wizard.label.prorate');
        } else {
            $priceFirstMonthBeforeDiscount = $packagePrice;
            $label = __('customer.wizard.label.full_cost');
        }

        // 2. Hitung harga bulan pertama SETELAH diskon
        $priceFirstMonthFinal = max(0, $priceFirstMonthBeforeDiscount - $discountNominal);

        // 3. Tambahkan akumulasi harga jika mengambil paket multi-months (durasi > 1 bulan)
        $extraMonthsCost = 0;
        if ($durationMonths > 1) {
            $extraMonthsCost = $packagePrice * ($durationMonths - 1);
        }

        $totalPriceBeforeDiscount = $priceFirstMonthBeforeDiscount + $extraMonthsCost;
        $totalPriceFinal = $priceFirstMonthFinal + $extraMonthsCost;

        return [
            'price_before_discount'  => (float) $totalPriceBeforeDiscount, // 💡 Harga prorate + multi-month (Tanpa Diskon)
            'price_final'            => (float) $totalPriceFinal,          // 💡 Harga prorate + multi-month (Sudah Potong Diskon)
            'calculation_type_label' => $label,
            'active_days'            => $activeDays,
            'total_days'             => $totalDaysInMonth,
            'current_day'            => $currentDay,
            'current_month'          => $now->translatedFormat('F'),
        ];
    }
}
