<?php

namespace App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands;

use App\Models\Whatsapp\WhatsappGatewayAccount;

class InfoPayLaterCommand
{
    public function __construct(
        public readonly string $recipient,
        public readonly string $customerId,
        public readonly string $customerName,
        public readonly string $period,
        public readonly string $tellerName,
        public readonly string $paylaterCode,
        public readonly WhatsappGatewayAccount $account,
    ) {}
}
