<?php

namespace App\Services\Whatsapp\CloudAPI\Messaging;

use App\Services\Whatsapp\CloudAPI\Payloads\DTO\TemplateMessageDTO;

class MessageResult
{
    public function __construct(
        public TemplateMessageDTO $payload,
        public array $response
    ) {}
}
