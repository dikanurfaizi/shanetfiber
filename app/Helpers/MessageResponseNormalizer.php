<?php

namespace App\Helpers;

class MessageResponseNormalizer
{
    public static function summarize(?string $response): string
    {
        if (!$response) {
            return '-';
        }

        $decoded = json_decode($response, true);

        if (!is_array($decoded)) {
            return $response; // non-JSON (GOWA, error text, dll)
        }

        // Meta API case
        $body = $decoded['body'] ?? null;

        if (!$body) {
            return 'Invalid response format';
        }

        $status = $decoded['status'] ?? '-';

        $message = $body['messages'][0] ?? null;

        if (!$message) {
            return "HTTP {$status} — No message payload";
        }

        return sprintf(
            'HTTP %s — %s (message_id: %s)',
            $status,
            strtoupper($message['message_status'] ?? '-'),
            $message['id'] ?? '-'
        );
    }
}
