<?php

namespace App\Services\Whatsapp;

use App\Helpers\LogHelper;
use App\Models\Whatsapp\WhatsappMessage;

class KeywordProcessor
{
    public function __construct(
        private KeywordNormalizer $normalizer
    ) {}

    public function resolveKeyword(string $text, $customer): ?WhatsappMessage
    {
        $normalized = strtolower(trim($this->normalizer->normalize($text)));

        $scope = $customer
            ? ['registered', 'both']
            : ['unregistered', 'both'];

        LogHelper::logInfo(null, ['scope' => $scope], 200, "Scope");

        // STOP tetap prioritas
        if ($this->normalizer->isStop($text)) {
            return WhatsappMessage::where('keywords', 'stop')
                ->whereIn('message_scope', $scope)
                ->first();
        }

        // 1. Exact match ke incoming_message
        $msg = WhatsappMessage::where('keywords', $normalized)
            ->whereIn('message_scope', $scope)
            ->first();
        if ($msg) return $msg;

        // 2. JSON array match
        $alias = WhatsappMessage::whereJsonContains('keywords', $normalized)
            ->whereIn('message_scope', $scope)
            ->first();
        if ($alias) return $alias;

        // 3. Fallback → unknown keyword
        return WhatsappMessage::where('slug', 'unknown_keyword')
            ->where('message_scope', 'both')
            ->first();
    }
}
