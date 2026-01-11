<?php

namespace App\Services\Whatsapp\CloudAPI\Payloads\DTO;

use RuntimeException;

class TemplateMessageDTO
{
    public function __construct(
        public string $recipient,
        public string $templateName,
        public string $language,
        public array $components
    ) {}

    public function validate(): void
    {
        if (!$this->recipient) {
            throw new RuntimeException("Recipient is required");
        }

        if (!$this->templateName) {
            throw new RuntimeException("Template name is required");
        }

        if (empty($this->components)) {
            throw new RuntimeException("Template components is empty");
        }
    }

    public function toArray(): array
    {
        $this->validate();

        return [
            'messaging_product' => 'whatsapp',
            'to' => $this->recipient,
            'type' => 'template',
            'template' => [
                'name' => $this->templateName,
                'language' => [
                    'code' => $this->language
                ],
                'components' => $this->components
            ]
        ];
    }

    public function toLogArray(): array
    {
        return [
            'to' => $this->recipient,
            'template' => $this->templateName,
            'language' => $this->language,
            'components_count' => count($this->components),
        ];
    }
}
