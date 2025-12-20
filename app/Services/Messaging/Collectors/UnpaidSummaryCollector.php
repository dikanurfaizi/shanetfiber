<?php

namespace App\Services\Messaging\Collectors;

use App\Models\Billings\Billing;
use App\Helpers\LogHelper;

class UnpaidSummaryCollector
{
    public function getUnpaidSummary(int $customerId): array
    {
        $result = Billing::query()
            ->where('customer_id', $customerId)
            ->whereIn('status', ['BL', 'PL'])
            ->selectRaw('COUNT(*) AS count_unpaid, SUM(total_amount) AS total_unpaid')
            ->first();

        LogHelper::logInfo(null, [
            'customer_id' => $customerId,
            'count' => $result->count_unpaid ?? 0,
            'total' => $result->total_unpaid ?? 0,
        ], 200, "Unpaid summary retrieved");

        return [
            'count_unpaid' => (int) ($result->count_unpaid ?? 0),
            'total_unpaid' => (float) ($result->total_unpaid ?? 0),
        ];
    }
}
