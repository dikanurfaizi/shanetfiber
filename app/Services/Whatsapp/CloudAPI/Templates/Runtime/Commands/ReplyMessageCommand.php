<?php

namespace App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands;

class ReplyMessageCommand
{
    public function __construct(
        public string $recipient,
        public string $text,
        public object $account, // WhatsappGatewayAccount
        public ?string $messageId = null, // ID pesan customer (wamid)
        public ?int $customerId = null
    ) {}
}
