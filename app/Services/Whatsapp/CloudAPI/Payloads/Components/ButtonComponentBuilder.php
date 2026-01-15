<?php

namespace App\Services\Whatsapp\CloudAPI\Payloads\Components;

use RuntimeException;

class ButtonComponentBuilder
{
    public function build($btn, array $buttonValues): ?array
    {
        $index = (int) $btn->position;

        return match ($btn->type) {
            'QUICK_REPLY'  => $this->buildQuickReply($btn),
            'URL'          => $this->buildUrlButton($btn, $buttonValues, $index),
            'PHONE_NUMBER' => $this->buildPhoneButton($btn),
            default => throw new RuntimeException(
                "Unsupported button type {$btn->type} for button {$btn->id}"
            )
        };
    }

    protected function buildQuickReply($button): array
    {
        return [
            'type'     => 'button',
            'sub_type' => 'quick_reply',
            'index'    => (string) $button->position
        ];
    }

    protected function buildPhoneButton($button): array
    {
        return [
            'type'     => 'button',
            'sub_type' => 'phone_number',
            'index'    => (string) $button->position
        ];
    }

    protected function buildUrlButton($button, array $buttonValues, int $index): array
    {
        $urlTemplate = $button->url ?? null;

        if (!$urlTemplate) {
            throw new RuntimeException("URL missing on button {$button->id}");
        }

        // === STATIC URL ===
        if (!str_contains($urlTemplate, '{{')) {
            return [
                'type'     => 'button',
                'sub_type' => 'url',
                'index'    => (string) $index,
            ];
        }

        // === DYNAMIC URL ===
        $params = $buttonValues[$index] ?? null;

        if (empty($params) || !is_array($params)) {
            throw new RuntimeException("Dynamic URL key missing for button {$button->id}");
        }

        return [
            'type'     => 'button',
            'sub_type' => 'url',
            'index'    => (string) $index,
            'parameters' => array_map(fn($v) => [
                'type' => 'text',
                'text' => (string) $v
            ], $params),
        ];
    }
}
