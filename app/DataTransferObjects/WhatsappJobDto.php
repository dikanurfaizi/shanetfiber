<?php

namespace App\DataTransferObjects;

use Carbon\Carbon;
use Illuminate\Support\Str;

class WhatsappJobDto
{
    public function __construct(
        public readonly ?int $customerId = null,
        public readonly ?string $action = null,
        public readonly ?string $traceId = null,
        public readonly ?int $billingId = null,
        public readonly ?int $receiptId = null,
        public readonly ?array $receiptData = null,
        public readonly ?Carbon $startDate = null,
        public readonly ?Carbon $endDate = null,
        public readonly ?int $orderId = null
    ) {}
}
