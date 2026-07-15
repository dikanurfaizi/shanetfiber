<?php

namespace App\Services\Packages;

use App\Models\Membership\Membership;
use App\Models\Packages\Package;
use App\Models\Packages\PackageDuration;
use App\Helpers\LogHelper;
use Illuminate\Support\Str;

class PackageGetService
{
    public function getDuration(int $packageId)
    {
        $traceId = (string) Str::uuid();

        $durations = PackageDuration::query()
            ->where('package_id', $packageId)
            ->where('is_active', true)
            ->with([
                'duration:id,name_en'
            ])
            ->get(['id', 'duration_id', 'discount_type', 'discount_value']);

        // 🟢 Log Info: Memantau durasi yang berhasil diambil
        LogHelper::logInfo(null, [
            'package_id' => $packageId,
            'count_durations' => $durations->count(),
            'duration_ids' => $durations->pluck('duration_id')->toArray()
        ], 200, "[Package Service] Package durations retrieved successfully", $traceId);

        return $durations;
    }

    public function getDiscount(?int $packageId = null, ?int $memberId = null)
    {
        $traceId = (string) Str::uuid();

        $package = $packageId ? Package::find($packageId) : null;
        $member  = $memberId ? Membership::find($memberId) : null;

        $packagePrice = $package ? (float) $package->price : 0.0;

        // 🟡 Log Warning: Jika paket tidak ditemukan saat ID dikirim
        if ($packageId && !$package) {
            LogHelper::logWarning(null, [
                'package_id' => $packageId,
                'member_id'  => $memberId
            ], 404, "[Package Service] Package ID provided but not found in database", $traceId);
        }

        if (!$member) {
            $result = [
                'package_price'      => $packagePrice,
                'effective_discount' => 0.0,
                'effective_percent'  => 0,
            ];

            LogHelper::logInfo(null, [
                'package_id' => $packageId,
                'member_id'  => $memberId,
                'result'     => $result
            ], 200, "[Package Service] Discount calculated (No member/membership applied)", $traceId);

            return $result;
        }

        // 👉 PERBAIKAN: Bersihkan karakter koma ribuan dari DB agar dibaca angka murni oleh PHP
        $rawValue = str_replace(',', '', $member->discount_value);
        $discountValue = (float) $rawValue;

        // Kalkulasi Diskon
        $discount = $member->discount_type === 'percent'
            ? ($packagePrice * ($discountValue / 100))
            : $discountValue;

        // Proteksi agar nilai diskon tidak melebihi harga paket utama
        $discount = min($discount, $packagePrice);

        $result = [
            'package_price'      => $packagePrice,
            'effective_discount' => $discount,
            // Gunakan round untuk merapikan hasil persentase desimal di frontend
            'effective_percent'  => $packagePrice > 0 ? round(($discount / $packagePrice * 100), 2) : 0,
        ];

        // 🟢 Log Info: Catat detail kalkulasi diskon untuk keperluan audit keuangan / billing
        LogHelper::logInfo(null, [
            'package_id'          => $packageId,
            'package_name'        => $package->name ?? null,
            'member_id'           => $memberId,
            'membership_name'     => $member->name ?? null,
            'input_discount_type' => $member->discount_type,
            'input_discount_val'  => $member->discount_value,
            'calculation_result'  => $result
        ], 200, "[Package Service] Discount calculation completed successfully", $traceId);

        return $result;
    }
}
