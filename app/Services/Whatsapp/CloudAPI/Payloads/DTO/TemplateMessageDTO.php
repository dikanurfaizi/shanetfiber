<?php

namespace App\Services\Whatsapp\CloudAPI\Payloads\DTO;

use RuntimeException;

class TemplateMessageDTO
{
    public function __construct(
        public string $recipient,
        public ?string $templateName = null, // Jadikan nullable
        public ?string $language = 'id',    // Jadikan nullable
        public array $components = [],      // Default array kosong
        public ?string $text = null,        // Tambahan untuk Reply
        public ?string $messageId = null    // Tambahan untuk Context/Reply ID
    ) {}

    public function validate(): void
    {
        if (!$this->recipient) {
            throw new RuntimeException("Recipient is required");
        }

        // Jika bukan tipe text, maka wajib validasi template
        if (!$this->text) {
            if (!$this->templateName) {
                throw new RuntimeException("Template name is required for template messages");
            }
            if (empty($this->components)) {
                throw new RuntimeException("Template components is empty");
            }
        }
    }

    public function toArray(): array
    {
        $this->validate();

        // Base Payload
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $this->recipient,
        ];

        // Logic Jika tipe TEXT (Reply)
        if ($this->text) {
            $payload['type'] = 'text';
            $payload['text'] = ['body' => $this->text];

            if ($this->messageId) {
                $payload['context'] = ['message_id' => $this->messageId];
            }

            return $payload;
        }

        // Logic Jika tipe TEMPLATE (Existing)
        $payload['type'] = 'template';
        $payload['template'] = [
            'name' => $this->templateName,
            'language' => ['code' => $this->language],
            'components' => $this->components
        ];

        return $payload;
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
