<?php

namespace App\Services\Messaging\Collectors;

use App\Helpers\LogHelper;
use Carbon\Carbon;

class FormatterCollector
{
    public function formatPackageInfo(object $package): string
    {
        try {
            $periode = $package->periode_name ?? 'Tidak ada periode';
            $formattedPeriode = $periode !== 'Tidak ada periode'
                ? Carbon::parse("1 {$periode}")->locale('id')->translatedFormat('F Y')
                : $periode;

            $memberType = $package->type ?? 'Non Member';
            $discount = number_format($package->discount ?? 0, 2);
            $price = number_format($package->price ?? 0, 2);

            return implode("\n", [
                "- Paket: " . ($package->package_name ?? '-'),
                "- Periode: {$formattedPeriode}",
                "- Harga Paket: Rp. {$price}",
                "- Kategori: {$memberType}",
                "- Potongan Harga: Rp. {$discount}",
            ]);
        } catch (\Throwable $e) {
            LogHelper::logError($e, ['package' => $package->package_name ?? null], 500, "Error formatting package info");
            return "Informasi paket tidak tersedia";
        }
    }
}
