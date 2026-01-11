<?php

namespace App\Services\Whatsapp\CloudAPI\Payloads\Components;

use RuntimeException;

class ButtonComponentBuilder
{
    public function build($button, array $values = []): ?array
    {
        return match ($button->type) {
            'QUICK_REPLY' => $this->buildQuickReply($button),
            'URL' => $this->buildUrlButton($button, $values),
            'PHONE_NUMBER' => $this->buildPhoneButton($button),
            default => throw new RuntimeException("Unsupported button type {$button->type} for button {$button->id}")
        };
    }

    protected function buildQuickReply($button): array
    {
        return [
            'type' => 'button',
            'sub_type' => 'quick_reply',
            'index' => (string) $button->position
        ];
    }

    protected function buildUrlButton($button, array $values): ?array
    {
        $urlTemplate = $button->url ?? null;

        if (!$urlTemplate) {
            throw new RuntimeException("URL missing on button {$button->id}");
        }

        // === STATIC URL ===
        if (!str_contains($urlTemplate, '{{')) {
            return null; // WAJIB skip
        }

        // === DYNAMIC URL ===
        $example = is_array($button->example)
            ? $button->example
            : json_decode($button->example ?? '{}', true);

        $key = $example['key'] ?? null;

        if (!$key) {
            throw new RuntimeException("Dynamic URL key missing for button {$button->id}");
        }

        if (!isset($values[$key])) {
            throw new RuntimeException("Dynamic URL value missing for key {$key}");
        }

        return [
            'type' => 'button',
            'sub_type' => 'url',
            'index' => (string) $button->position,
            'parameters' => [
                [
                    'type' => 'text',
                    'text' => $values[$key]
                ]
            ]
        ];
    }

    protected function buildPhoneButton($button): array
    {
        return [
            'type' => 'button',
            'sub_type' => 'phone_number',
            'index' => (string) $button->position
        ];
    }
}
