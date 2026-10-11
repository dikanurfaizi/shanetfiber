<?php

namespace App\Services\WhatsApp\CloudAPI\Templates\Runtime\Commands;

use App\Models\WhatsApp\WhatsAppGatewayAccount;

class OverdueBroadcastCommand
{
    public function __construct(
        public readonly string $recipient,
        public readonly string $customerId,
        public readonly string $packageName,
        public readonly string $total,
        public readonly string $lateDays,
        public readonly string $note,
        public readonly string $templateName,
        public readonly WhatsAppGatewayAccount $account,
        public readonly array $bankPayload,
        public readonly string $contactAdmin,
        public readonly string $contactWhatsapp,
    ) {}
}
