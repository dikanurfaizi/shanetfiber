<?php

namespace App\Helpers;

class MessageResponseNormalizer
{
    public static function summarize(string|array|null $response): string
    {
        if (!$response) {
            return '-';
        }

        $decoded = is_array($response) ? $response : json_decode($response, true);

        // Non-JSON (error text mentah, gateway legacy, dll)
        if (!is_array($decoded)) {
            return (string) $response;
        }

        /**
         * =========================================================
         * 1. GO_WHATSAPP / META TEMPLATE RESPONSE (Wrap Status & Body)
         * Contoh: {"status":200, "body":{"messages":[{"id":"wamid...", "message_status":"accepted"}]}}
         * =========================================================
         */
        $body = $decoded['body'] ?? null;
        if (is_array($body) && isset($body['messages'][0])) {
            $status = $decoded['status'] ?? 200;
            $msg = $body['messages'][0];
            $waId = $body['contacts'][0]['wa_id'] ?? $body['contacts'][0]['input'] ?? '-';

            return sprintf(
                'WA — %s to %s (message_id: %s, HTTP %s)',
                strtoupper($msg['message_status'] ?? 'SENT'),
                $waId,
                $msg['id'] ?? '-',
                $status
            );
        }

        /**
         * =========================================================
         * 2. META API REPLY_TEXT RESPONSE
         * Contoh: {"messaging_product":"whatsapp", "to":"628311...", "type":"text"}
         * =========================================================
         */
        if (isset($decoded['messaging_product']) && $decoded['messaging_product'] === 'whatsapp') {
            $to = $decoded['to'] ?? '-';
            $type = strtoupper($decoded['type'] ?? 'MESSAGE');

            return sprintf(
                'META API — %s sent to %s',
                $type,
                $to
            );
        }

        /**
         * =========================================================
         * 3. TELEGRAM RESPONSE
         * Contoh: {"message_id":18375, "chat":{"id":..., "first_name":...}}
         * =========================================================
         */
        if (isset($decoded['message_id'], $decoded['chat']['id'])) {
            $firstName = $decoded['chat']['first_name'] ?? '';
            $lastName  = $decoded['chat']['last_name'] ?? '';

            $fullName = trim($firstName . ' ' . $lastName);
            $fullName = $fullName !== '' ? $fullName : ($decoded['chat']['username'] ?? '-');

            return sprintf(
                'TELEGRAM — SENT to %s (message_id: %s, chat_id: %s)',
                $fullName,
                $decoded['message_id'],
                $decoded['chat']['id']
            );
        }

        /**
         * =========================================================
         * FALLBACK UNKNOWN STRUCTURE
         * =========================================================
         */
        return 'JSON response received (unrecognized structure)';
    }
}
