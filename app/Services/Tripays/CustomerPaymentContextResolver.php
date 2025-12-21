<?php

namespace App\Services\Tripays;

use App\Helpers\ExecutionTimer;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerSubscription;
use App\Helpers\LogHelper;
use Carbon\Carbon;

class CustomerPaymentContextResolver
{
    public function resolve(
        Customer $customer,
        ?CustomerSubscription $subscription,
        string $traceId
    ): array {
        $timer = new ExecutionTimer();
        $data = [
            'customer_id'      => $customer->id,
            'paket_id'         => $subscription?->package_id,
            'secret_id'        => $customer->secret_id,
            'username'         => $customer->username,
            'activation_date'  => $customer->activation_date
                ? Carbon::parse($customer->activation_date)->format('d')
                : null,
            'mikrotik_id'      => $customer->mikrotik_id,
            'internet_service' => $customer->internet_service,
        ];

        foreach ($data as $key => $value) {
            if (is_null($value)) {
                LogHelper::logError(null, [
                    'customer_id' => $customer->id,
                    'missing'     => $key,
                ], 500, '[Tripay] Customer data incomplete', $traceId, $timer->lap());

                throw new \DomainException(
                    "Customer data incomplete: {$key}"
                );
            }
        }

        LogHelper::logInfo(null, [
            'customer_id' => $customer->id,
            'validated'   => array_keys($data),
        ], 200, '[Tripay] Customer data validated', $traceId, $timer->lap());

        return $data;
    }
}
