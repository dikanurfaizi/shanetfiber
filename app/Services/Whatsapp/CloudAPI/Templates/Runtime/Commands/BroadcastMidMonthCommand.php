<?php

namespace App\Services\WhatsApp\CloudAPI\Templates\Runtime\Commands;

use App\Models\WhatsApp\WhatsAppGatewayAccount;

class BroadcastMidMonthCommand
{
    public function __construct(
        public readonly string $recipient,
        public readonly string $customerId,
        public readonly string $customerName,
        public readonly string $packageName,
        public readonly string $accountData,
        public readonly string $total,
        public readonly string $dueDate,
        public readonly string $note,
        public readonly string $templateName,
        public readonly WhatsAppGatewayAccount $account,
        public readonly array $bankPayload,
        public readonly string $contactAdmin,
        public readonly string $contactWhatsapp,
    ) {}
}
