<?php

namespace App\Helpers;

class MaskCustomerName
{
    public static function maskName(string $name): string
    {
        $name = trim($name);

        if (strlen($name) <= 5) {
            return substr($name, 0, 2) . '***';
        }

        $firstWord = strtok($name, ' ');
        return $firstWord . ' *****';
    }

    public static function buildAccountData(string $customerName, string $packageName): string
    {
        $separator = ' - ';
        $maxTotal  = 30;

        $packageName = trim($packageName);
        $customerName = trim($customerName);

        $maxCustomerLength = $maxTotal - (strlen($packageName) + strlen($separator));

        if ($maxCustomerLength <= 0) {
            return '*' . $separator . $packageName;
        }

        // kalau customer masih muat utuh
        if (strlen($customerName) <= $maxCustomerLength) {
            return $customerName . $separator . $packageName;
        }

        // masking adaptif
        $visible = max(1, $maxCustomerLength - 3); // sisakan untuk ***
        $maskedCustomer = substr($customerName, 0, $visible) . '***';

        return $maskedCustomer . $separator . $packageName;
    }
}
