<?php

namespace App\Services\WhatsApp;

use App\Helpers\LogHelper;
use App\Models\WhatsApp\WhatsAppMessage;

class KeywordProcessor
{
    public function __construct(
        private KeywordNormalizer $normalizer
    ) {}

    public function resolveKeyword(string $text, $customer): ?WhatsAppMessage
    {
        $normalized = strtolower(trim($this->normalizer->normalize($text)));

        $scope = $customer
            ? ['registered', 'both']
            : ['unregistered', 'both'];

        // STOP prioritas
        if ($this->normalizer->isStop($normalized)) {
            LogHelper::logInfo(null, [
                'text' => $normalized,
                'scope' => $scope,
            ], 200, '[KEYWORD] Stop detected');

            return WhatsAppMessage::where('slug', 'stop')
                ->whereIn('message_scope', $scope)
                ->first();
        }

        // Exact match
        $msg = WhatsAppMessage::where('slug', $normalized)
            ->whereIn('message_scope', $scope)
            ->first();

        if ($msg) {
            LogHelper::logInfo(null, [
                'slug' => $msg->slug,
                'scope' => $scope,
            ], 200, '[KEYWORD] Exact match');

            return $msg;
        }

        // Alias match
        $alias = WhatsAppMessage::whereJsonContains('keywords', $normalized)
            ->whereIn('message_scope', $scope)
            ->first();

        if ($alias) {
            LogHelper::logInfo(null, [
                'slug' => $alias->slug,
                'scope' => $scope,
            ], 200, '[KEYWORD] Alias match');

            return $alias;
        }

        // Fallback
        LogHelper::logInfo(null, [
            'text' => $normalized,
            'scope' => $scope,
        ], 200, '[KEYWORD] Fallback to unknown');

        return WhatsAppMessage::where('slug', 'unknown_keyword')
            ->where('message_scope', 'both')
            ->first();
    }
}
