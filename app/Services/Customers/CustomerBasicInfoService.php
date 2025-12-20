<?php

namespace App\Services\Customers;

use App\Models\Customers\Customer;
use App\Helpers\LogHelper;
use App\Helpers\FormatReceiver;

class CustomerBasicInfoService
{
    public function getCustomerBasicInfo(Customer $customer, string $traceId): array
    {
        try {
            $formatter = new FormatReceiver();

            // Hanya load relasi yang dibutuhkan
            $customer->loadMissing([
                'activeSubscription.package',
                'user',
            ]);

            return [
                'customer_id'     => $customer->customer_id,
                'name'            => $customer->name,
                'address'         => $customer->address,
                'package_name'    => $customer->activePackage->name ?? '-',
                'package_price'   => $customer->activePackage->price ?? 0,
                'responsibility'  => $customer->user->name ?? '-',
                'email'           => $customer->email ?? '-',
                'contact'         => $formatter->formatReceiver($customer->contact, 'display', false),
                'activation_date' => $customer->activation_date,
                'customer'        => $customer // kalau kamu masih mau akses object-nya
            ];
        } catch (\Throwable $e) {

            LogHelper::logError(
                null,
                [
                    'customer_id' => $customer->id,
                    'message'     => $e->getMessage(),
                    'file'        => $e->getFile(),
                    'line'        => $e->getLine()
                ],
                500,
                'Failed to get customer basic info',
                $traceId
            );

            return [];
        }
    }
}
