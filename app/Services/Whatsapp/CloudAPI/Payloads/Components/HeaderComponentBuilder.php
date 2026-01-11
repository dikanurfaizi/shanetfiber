<?php

namespace App\Services\Whatsapp\CloudAPI\Payloads\Components;

use RuntimeException;

class HeaderComponentBuilder
{
    public function build($component, array $values): array
    {
        $example = is_array($component->example)
            ? $component->example
            : json_decode($component->example ?? '{}', true);

        $format = strtoupper($component->format ?? 'TEXT');

        if (empty($example)) {
            return ['type' => 'header'];
        }

        return match ($format) {
            'TEXT' => $this->buildTextHeader($example, $values, $component),
            'IMAGE', 'VIDEO', 'DOCUMENT' => $this->buildMediaHeader($example, $format, $component),
            'LOCATION' => $this->buildLocationHeader($example, $component),
            default => throw new RuntimeException("Unsupported header format {$format} on component {$component->id}")
        };
    }

    protected function buildTextHeader(array $example, array $values, $component): array
    {
        $key = $example['header_text'][0] ?? null;

        if (!$key) {
            throw new RuntimeException("Header text example missing for component {$component->id}");
        }

        if (!isset($values[$key])) {
            throw new RuntimeException("Missing header value for key {$key}");
        }

        return [
            'type' => 'header',
            'parameters' => [
                [
                    'type' => 'text',
                    'text' => $values[$key]
                ]
            ]
        ];
    }

    protected function buildMediaHeader(array $example, string $format, $component): array
    {
        $handle = $example['header_handle'][0] ?? null;

        if (!$handle) {
            throw new RuntimeException("Header {$format} handle missing for component {$component->id}");
        }

        return [
            'type' => 'header',
            'parameters' => [
                [
                    'type' => strtolower($format),
                    strtolower($format) => [
                        'link' => $handle
                    ]
                ]
            ]
        ];
    }

    protected function buildLocationHeader(array $example, $component): array
    {
        $loc = $example['location'][0] ?? null;

        if (!$loc) {
            throw new RuntimeException("Header location example missing for component {$component->id}");
        }

        return [
            'type' => 'header',
            'parameters' => [
                [
                    'type' => 'location',
                    'location' => [
                        'latitude' => $loc['latitude'],
                        'longitude' => $loc['longitude'],
                        'name' => $loc['name'] ?? null,
                        'address' => $loc['address'] ?? null,
                    ]
                ]
            ]
        ];
    }
}
