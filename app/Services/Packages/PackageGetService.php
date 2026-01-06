<?php

namespace App\Services\Packages;

use App\Models\Membership\Membership;
use App\Models\Packages\Package;
use App\Models\Packages\PackageDuration;

class PackageGetService
{
    public function getDuration(int $packageId)
    {
        $durations = PackageDuration::query()
            ->where('package_id', $packageId)
            ->where('is_active', true)
            ->with([
                'duration:id,name_en' // ⬅ mengambil name dari tabel duration_labels
            ])
            ->get(['id', 'duration_id', 'discount_type', 'discount_value']);

        return $durations;
    }

    public function getDiscount(?int $packageId = null, ?int $memberId = null)
    {
        $package = $packageId ? Package::find($packageId) : null;
        $member  = $memberId ? Membership::find($memberId) : null;

        $packagePrice = $package ? $package->price : 0;

        if (!$member) {
            return [
                'package_price'      => $packagePrice,
                'effective_discount' => 0,
                'effective_percent'  => 0,
            ];
        }

        $discount = $member->discount_type === 'percent'
            ? ($packagePrice * ($member->discount_value / 100))
            : $member->discount_value;

        $discount = min($discount, $packagePrice);

        return [
            'package_price'      => $packagePrice,
            'effective_discount' => $discount,
            'effective_percent'  => $packagePrice > 0 ? ($discount / $packagePrice * 100) : 0,
        ];
    }
}
