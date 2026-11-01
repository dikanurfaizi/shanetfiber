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
                'activeSubscription' => function ($query) {
                    // Hapus ini jika model Subscription juga tidak pakai SoftDeletes
                    $query->withTrashed();
                },
                'activeSubscription.package',
                'user',
            ]);

            $data = [
                'slug'            => $customer->slug,
                'customer_id'     => $customer->customer_id,
                'name'            => $customer->name,
                'address'         => $customer->address,
                'package_name'    => $customer->activePackage->name ?? '-',
                'package_price'   => $customer->activePackage->price ?? 0,
                'responsibility'  => $customer->user->name ?? '-',
                'email'           => $customer->email ?? '-',
                'contact'         => $customer->contact ? $formatter->formatReceiver($customer->contact, 'display', false) : '-',
                'activation_date' => $customer->activation_date,
                'is_deleted'      => $customer->trashed(), // Tambahan info jika butuh status delete
                'user_id'         => $customer->user_id,
                'longitude'       => $customer->longitude,
                'latitude'        => $customer->latitude
            ];

            LogHelper::logInfo(null, $data, 200, "Detail Customer Data", $traceId);

            return $data;
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
