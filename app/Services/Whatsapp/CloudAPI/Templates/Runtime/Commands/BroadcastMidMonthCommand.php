<?php

namespace App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands;

use App\Models\Whatsapp\WhatsappGatewayAccount;

class BroadcastMidMonthCommand
{
    public function __construct(
        public readonly string $recipient,
        public readonly string $customerId,
        public readonly string $customerName,
        public readonly string $packageName,
        public readonly string $total,
        public readonly string $dueDate,
        public readonly WhatsappGatewayAccount $account,
    ) {}
}
