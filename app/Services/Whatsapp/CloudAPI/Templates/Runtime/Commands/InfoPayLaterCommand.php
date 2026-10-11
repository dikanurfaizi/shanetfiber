<?php

namespace App\Services\WhatsApp\CloudAPI\Templates\Runtime\Commands;

use App\Models\WhatsApp\WhatsAppGatewayAccount;

class InfoPayLaterCommand
{
    public function __construct(
        public readonly string $recipient,
        public readonly string $customerId,
        public readonly string $customerName,
        public readonly string $period,
        public readonly string $tellerName,
        public readonly string $paylaterCode,
        public readonly WhatsAppGatewayAccount $account,
    ) {}
}
