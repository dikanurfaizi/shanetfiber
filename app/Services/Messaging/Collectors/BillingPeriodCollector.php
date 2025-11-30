<?php

namespace App\Services\Messaging\Collectors;

use App\Models\Billing\BillingPeriode;
use App\Helpers\LogHelper;

class BillingPeriodCollector
{
    public function getLastBillingPeriod(string $traceId): ?BillingPeriode
    {
        $periode = BillingPeriode::latest('created_at')->first();

        if (!$periode) {
            LogHelper::logWarning(null, null, 404, "No billing period found.", $traceId);
            return null;
        }

        LogHelper::logInfo(null, [
            'periode_id' => $periode->id,
            'periode_name' => $periode->name,
        ], 200, "Billing period retrieved successfully.", $traceId);

        return $periode;
    }
}
