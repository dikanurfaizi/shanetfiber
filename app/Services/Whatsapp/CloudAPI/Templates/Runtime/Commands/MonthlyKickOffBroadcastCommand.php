<?php

namespace App\Services\WhatsApp\CloudAPI\Templates\Runtime\Commands;

use App\Models\WhatsApp\WhatsAppGatewayAccount;

class MonthlyKickOffBroadcastCommand
{
    public function __construct(
        public readonly WhatsAppGatewayAccount $account,
        public readonly string $templateName,
        public readonly string $customerId,
        public readonly string $customerName,
        public readonly string $recipient,
        public readonly string $initialDate,
        public readonly string $channel,
        public readonly array $bankPayload,
        public readonly string $contactAdmin,
        public readonly string $contactWhatsapp,
    ) {}
}
