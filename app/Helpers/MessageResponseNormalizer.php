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

        // Non-JSON (error text, gateway legacy, dll)
        if (!is_array($decoded)) {
            return $response;
        }

        /**
         * ===============================
         * TELEGRAM RESPONSE
         * ===============================
         */
        if (isset($decoded['message_id'], $decoded['chat']['id'])) {

            $firstName = $decoded['chat']['first_name'] ?? '';
            $lastName  = $decoded['chat']['last_name'] ?? '';

            $fullName = trim($firstName . ' ' . $lastName);
            $fullName = $fullName !== '' ? $fullName : '-';

            return sprintf(
                'TELEGRAM — SENT to %s (message_id: %s, chat_id: %s)',
                $fullName,
                $decoded['message_id'],
                $decoded['chat']['id']
            );
        }

        /**
         * ===============================
         * META WHATSAPP API RESPONSE
         * ===============================
         */
        $body = $decoded['body'] ?? null;

        if ($body && isset($body['messages'][0])) {
            $status  = $decoded['status'] ?? '-';
            $message = $body['messages'][0];

            return sprintf(
                'HTTP %s — %s (message_id: %s)',
                $status,
                strtoupper($message['message_status'] ?? '-'),
                $message['id'] ?? '-'
            );
        }

        /**
         * ===============================
         * FALLBACK JSON
         * ===============================
         */
        return 'JSON response received (unrecognized structure)';
    }
}
