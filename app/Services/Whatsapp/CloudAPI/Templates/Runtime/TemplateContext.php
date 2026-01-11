<?php

namespace App\Services\Whatsapp\CloudAPI\Templates\Runtime;

use App\Models\Whatsapp\WhatsappGatewayAccount;

class TemplateContext
{
    public function __construct(
        public string $recipient,
        public string $templateName,
        public string $language,
        public array $values,
        public WhatsappGatewayAccount $account,
    ) {}
}
