<?php

namespace App\Services\Customers\Billing;

class ProratedAmountsService
{
    public function calculate(
        array $segments,
        float $oldPackagePrice,
        float $newPackagePrice
    ): array {

        $oldAmount = round(
            ($segments['old'] / $segments['total']) * $oldPackagePrice,
            0
        );

        $newAmount = round(
            ($segments['new'] / $segments['total']) * $newPackagePrice,
            0
        );

        return [
            'old_amount' => $oldAmount, // reference only
            'new_amount' => $newAmount, // WILL be billed
        ];
    }
}
