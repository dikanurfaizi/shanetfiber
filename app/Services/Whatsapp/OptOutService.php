<?php

namespace App\Services\Whatsapp;

use App\Models\Customer;
use App\Helpers\LogHelper;
use App\Helpers\FormatReceiver;

class OptOutService
{
    private function formatNumber()
    {
        return $formatter = new FormatReceiver();
    }

    public function store(string $phone, string $traceId): void
    {
        $format = $this->formatNumber();

        $customer = Customer::where('contact', $format->formatReceiver($phone, null, false))
            ->orWhere('contact', $format->formatReceiver($phone, 'display', false))->first();

        if ($customer) {
            $customer->update(['is_opt_out' => true]);

            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
                'phone'       => $phone,
            ], 200, '[OptOut] Customer opted out', $traceId);
        }
    }

    public function shouldBlock(string $phone): bool
    {
        $format = $this->formatNumber();

        return Customer::where('contact', $format->formatReceiver($phone, null, false))
            ->orWhere('contact', $format->formatReceiver($phone, 'display', false))
            ->where('is_opt_out', true)
            ->exists();
    }
}
