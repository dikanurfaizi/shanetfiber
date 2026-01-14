<?php

namespace App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands;

use App\Models\Whatsapp\WhatsappGatewayAccount;

class TestMessageCommand
{
    public function __construct(
        public readonly string $recipient,
        public readonly WhatsappGatewayAccount $account,
    ) {}
}
