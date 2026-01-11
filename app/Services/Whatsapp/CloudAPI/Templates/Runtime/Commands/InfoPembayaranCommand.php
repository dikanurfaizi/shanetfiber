<?php

namespace App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands;

use App\Models\Whatsapp\WhatsappGatewayAccount;

class InfoPembayaranCommand
{
    public function __construct(
        public readonly string $recipient,
        public readonly string $customerName,
        public readonly string $period,
        public readonly string $amount,
        public readonly string $paymentTime,
        public readonly string $tellerName,
        public readonly string $receiptCode,
        public readonly WhatsappGatewayAccount $account,
    ) {}
}
