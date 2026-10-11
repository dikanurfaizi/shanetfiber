<?php

namespace App\Services\WhatsApp\CloudAPI\Templates\Runtime\Commands;

use App\Models\WhatsApp\WhatsAppGatewayAccount;

class InfoPembayaranCommand
{
    public function __construct(
        public readonly string $recipient,
        public readonly string $customerId,
        public readonly string $customerName,
        public readonly string $period,
        public readonly string $amount,
        public readonly string $paymentTime,
        public readonly string $tellerName,
        public readonly string $receiptCode,
        public readonly WhatsAppGatewayAccount $account,
    ) {}
}
