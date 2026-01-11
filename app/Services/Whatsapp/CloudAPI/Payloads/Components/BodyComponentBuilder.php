<?php

namespace App\Services\Whatsapp\CloudAPI\Payloads\Components;

use RuntimeException;

class BodyComponentBuilder
{
    public function build($component, array $values): array
    {
        $example = is_array($component->example)
            ? $component->example
            : json_decode($component->example ?? '{}', true);

        $exampleRow = $example['body_text'][0] ?? [];

        if (!is_array($exampleRow)) {
            throw new RuntimeException("Invalid body example format for component {$component->id}");
        }

        // pakai urutan, bukan key
        $orderedValues = array_values($values);

        $params = [];

        foreach ($exampleRow as $i => $_) {
            if (!isset($orderedValues[$i])) {
                throw new RuntimeException("Missing body value at position {$i}");
            }

            $params[] = [
                'type' => 'text',
                'text' => $orderedValues[$i]
            ];
        }

        return [
            'type' => 'body',
            'parameters' => $params
        ];
    }
}
