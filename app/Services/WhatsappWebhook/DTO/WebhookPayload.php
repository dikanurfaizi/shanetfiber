<?php

namespace App\Services\WhatsappWebhook\DTO;

use App\Models\Whatsapp\WhatsappNumber;

class WebhookPayload
{
    public function __construct(
        public readonly ?string $deviceId,
        public readonly ?string $event,
        public readonly array   $payload,
        public readonly array   $raw,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            deviceId: $data['device_id'] ?? null,
            event: $data['event'] ?? $data['action'] ?? null,
            payload: $data['payload'] ?? [],
            raw: $data,
        );
    }

    // ===== Accessors =====

    public function sender(): ?string
    {
        return $this->payload['from'] ?? null;
    }

    public function timestamp(): ?int
    {
        if (!isset($this->payload['timestamp'])) {
            return null;
        }

        return strtotime($this->payload['timestamp']);
    }

    public function isText(): bool
    {
        return isset($this->payload['body']);
    }

    public function text(): ?string
    {
        return $this->payload['body'] ?? null;
    }

    public function isImage(): bool
    {
        return isset($this->payload['image']);
    }

    public function isVideo(): bool
    {
        return isset($this->payload['video']);
    }

    public function isDocument(): bool
    {
        return isset($this->payload['document']);
    }

    public function mediaPath(): ?string
    {
        return $this->payload['image']
            ?? $this->payload['video']
            ?? $this->payload['document']
            ?? null;
    }

    public function deviceId(): ?string
    {
        $numbers = WhatsappNumber::where('jid', $this->deviceId)->first();
        if ($numbers) {
            return $numbers->device_id;
        }
        return $this->deviceId;
    }
}
