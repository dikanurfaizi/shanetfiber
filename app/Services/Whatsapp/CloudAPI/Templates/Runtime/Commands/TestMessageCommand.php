<?php

namespace App\Services\WhatsApp\CloudAPI\Templates\Runtime\Commands;

use App\Models\WhatsApp\WhatsAppGatewayAccount;

class TestMessageCommand
{
    public function __construct(
        public readonly string $recipient,
        public readonly WhatsAppGatewayAccount $account,
    ) {}
}
