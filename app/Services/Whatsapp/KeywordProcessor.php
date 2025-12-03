<?php

namespace App\Services\Whatsapp;

use App\Models\Whatsapp\WhatsappMessage;

class KeywordProcessor
{
    public function __construct(
        private KeywordNormalizer $normalizer
    ) {}

    public function resolveKeyword(string $text): ?WhatsappMessage
    {
        $normalized = strtolower(trim($this->normalizer->normalize($text)));

        // STOP adalah prioritas tertinggi
        if ($this->normalizer->isStop($text)) {
            return WhatsappMessage::where('slug', 'stop')->first();
        }

        // 1. Exact match ke incoming_message
        $msg = WhatsappMessage::where('incoming_message', $normalized)->first();
        if ($msg) {
            return $msg;
        }

        // 2. Match ke keywords (JSON array)
        $alias = WhatsappMessage::whereJsonContains('keywords', $normalized)->first();
        if ($alias) {
            return $alias;
        }

        // 3. Fallback → unknown keyword
        return WhatsappMessage::where('slug', 'unknown_keyword')->first();
    }
}
