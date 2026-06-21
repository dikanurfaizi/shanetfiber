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

    public function isFromMe(): bool
    {
        return (bool) ($this->payload['is_from_me'] ?? false);
    }

    public function isGroupMessage(): bool
    {
        // Mengambil chat_id dari properti payload internal
        $chatId = $this->payload['chat_id'] ?? null;

        if (! $chatId) {
            return false;
        }

        // Mengembalikan true jika chat_id mengandung kode akhiran grup WhatsApp (@g.us)
        return str_contains($chatId, '@g.us');
    }

    // ===== Tambahan / Penyesuaian Accessors di WebhookPayload =====

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

    // 🌟 BARU: Deteksi Stiker, Audio/Voice Note, dan GIF (biasanya masuk kategori video dengan gifPlayback)
    public function isSticker(): bool
    {
        return isset($this->payload['sticker']);
    }

    public function isAudio(): bool
    {
        return isset($this->payload['audio']) || isset($this->payload['voice']);
    }

    /**
     * 🌟 BARU: Method universal untuk mengecek apakah payload mengandung file/media apa pun
     */
    public function hasMedia(): bool
    {
        return $this->isImage() || $this->isVideo() || $this->isDocument() || $this->isSticker() || $this->isAudio();
    }

    /**
     * 🌟 BARU: Menjawab kebutuhan MediaMessageHandler yang sempat hilang
     * Mengembalikan data path dan perkiraan jenis media secara dinamis
     */
    public function media(): ?array
    {
        $path = $this->payload['image']
            ?? $this->payload['video']
            ?? $this->payload['document']
            ?? $this->payload['sticker']
            ?? $this->payload['audio']
            ?? $this->payload['voice']
            ?? null;

        if (!$path) {
            return null;
        }

        // Tentukan tipe media secara sederhana berdasarkan key yang tersedia di payload
        $type = 'unknown';
        if ($this->isImage()) $type = 'image';
        elseif ($this->isVideo()) $type = 'video/gif';
        elseif ($this->isDocument()) $type = 'document';
        elseif ($this->isSticker()) $type = 'sticker';
        elseif ($this->isAudio()) $type = 'audio/voice';

        return [
            'media_path' => $path,
            'mime_type'  => $type
        ];
    }
}
