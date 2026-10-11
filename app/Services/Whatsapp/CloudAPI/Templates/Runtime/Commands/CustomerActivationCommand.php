<?php

namespace App\Services\WhatsApp\CloudAPI\Templates\Runtime\Commands;

use App\Models\WhatsApp\WhatsAppGatewayAccount;

class CustomerActivationCommand
{
    public function __construct(
        public readonly string $recipient,
        public readonly string $customerName,
        public readonly string $customerId,
        public readonly string $packageName,
        public readonly string $packagePrice,
        public readonly string $memberName,
        public readonly string $adminName,
        public readonly string $adminContact,
        public readonly string $bank,
        public readonly WhatsAppGatewayAccount $account,
    ) {}
}
