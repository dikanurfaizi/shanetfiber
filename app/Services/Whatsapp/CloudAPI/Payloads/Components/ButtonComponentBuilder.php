<?php

namespace App\Services\WhatsApp\CloudAPI\Payloads\Components;

use RuntimeException;

class ButtonComponentBuilder
{
    public function build($btn, array $buttonValues): ?array
    {
        // hanya URL button yang pernah ikut payload
        if ($btn->type !== 'URL') {
            return null;
        }

        return $this->buildUrlButton($btn, $buttonValues);
    }

    protected function buildUrlButton($button, array $buttonValues): ?array
    {
        $index = (int) $button->position;
        $urlTemplate = $button->url ?? null;

        if (!$urlTemplate) {
            throw new RuntimeException("URL missing on button {$button->id}");
        }

        // STATIC URL → baked in template → jangan dikirim
        if (!str_contains($urlTemplate, '{{')) {
            return null;
        }

        // DYNAMIC URL → wajib parameter
        $params = $buttonValues[$index] ?? null;

        if (empty($params) || !is_array($params)) {
            throw new RuntimeException("Dynamic URL key missing for button {$button->id}");
        }

        return [
            'type' => 'button',
            'sub_type' => 'url',
            'index' => (string) $index,
            'parameters' => array_map(
                fn($v) => [
                    'type' => 'text',
                    'text' => (string) $v
                ],
                $params
            ),
        ];
    }
}
