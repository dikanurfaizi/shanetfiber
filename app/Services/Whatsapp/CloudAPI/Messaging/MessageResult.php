<?php

namespace App\Services\WhatsApp\CloudAPI\Messaging;

use App\Services\WhatsApp\CloudAPI\Payloads\DTO\TemplateMessageDTO;

class MessageResult
{
    public function __construct(
        public TemplateMessageDTO $payload,
        public array $response
    ) {}

    public function getResponse(): array
    {
        return $this->response;
    }

    public function getPayload(): TemplateMessageDTO
    {
        return $this->payload;
    }

    public function isSuccess(): bool
    {
        return ($this->response['success'] ?? false) && (($this->response['status'] ?? 0) === 200);
    }
}
