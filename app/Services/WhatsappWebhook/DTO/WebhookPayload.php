<?php

namespace App\Services\WhatsappWebhook\DTO;

class WebhookPayload
{
    public function __construct(
        public readonly ?string $event,
        public readonly ?array  $payload,
        public readonly ?array  $message,
        public readonly ?array  $image,
        public readonly ?array  $video,
        public readonly ?array  $document,
        public readonly ?string $from,
        public readonly ?string $senderId,
        public readonly ?int    $timestamp,
        public readonly array   $raw,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            event: $data['event'] ?? $data['action'] ?? null,
            payload: $data['payload'] ?? null,
            message: $data['message'] ?? null,
            image: $data['image'] ?? null,
            video: $data['video'] ?? null,
            document: $data['document'] ?? null,
            from: $data['from'] ?? null,
            senderId: $data['sender_id'] ?? null,
            timestamp: isset($data['timestamp']) ? (int) $data['timestamp'] : null,
            raw: $data,
        );
    }

    public function sender(): ?string
    {
        return $this->from ?? $this->senderId;
    }

    public function text(): ?string
    {
        return $this->message['text'] ?? null;
    }

    public function media(): ?array
    {
        return $this->image ?? $this->video ?? $this->document ?? null;
    }
}
