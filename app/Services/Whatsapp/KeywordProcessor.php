<?php

namespace App\Services\Whatsapp;

use App\Models\Whatsapp\WhatsappMessage;

class KeywordProcessor
{
    public function __construct(
        private KeywordNormalizer $normalizer
    ) {}

    public function resolveKeyword(string $text): ?WhatsAppMessage
    {
        $normalized = $this->normalizer->normalize($text);

        // 1. STOP keyword – highest priority
        if ($this->normalizer->isStop($text)) {
            return WhatsappMessage::where('slug', 'stop')->first();
        }

        // 2. Exact match to known command
        $msg = WhatsappMessage::where('name', $normalized)->first();
        if ($msg) {
            return $msg;
        }

        // 3. Unknown keyword
        return WhatsappMessage::where('slug', 'unknown_keyword')->first();
    }
}
