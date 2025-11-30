<?php

namespace App\Services\WhatsappWebhook;

use App\Helpers\LogHelper;
use Illuminate\Http\Request;

class WebhookValidator
{
    public function __construct(
        private ?string $secret = null
    ) {
        $this->secret = config('whatsapp.webhook_secret');;
    }

    public function validateSignature(string $payload, Request $request): bool
    {
        $signature = $request->header('X-Hub-Signature-256');

        if (! $signature || ! str_starts_with($signature, 'sha256=')) {
            LogHelper::logWarning(null, [
                'signature' => $signature ?? '(missing)',
            ], 401, '[WebhookValidator] Signature missing or invalid format');
            return false;
        }

        $expected = hash_hmac('sha256', $payload, $this->secret);
        $provided = substr($signature, 7);

        $isValid = hash_equals($expected, $provided);

        // ==== LOG VALIDATION ====
        LogHelper::logInfo(null, [
            'expected_hmac' => $expected,
            'provided_hmac' => $provided,
            'match'         => $isValid,
        ], 200, '[WebhookValidator] Signature Comparison Result');

        return $isValid;
    }

    public function parseJson(string $payload, string $traceId): ?array
    {
        $data = json_decode($payload, true);

        if (! is_array($data)) {
            LogHelper::logWarning(null, [], 400, '[Webhook] Invalid JSON payload', $traceId);
            return null;
        }

        LogHelper::logInfo(null, [
            'event'     => $data['event'] ?? $data['action'] ?? 'unknown',
            'timestamp' => $data['timestamp'] ?? null,
        ], 200, '[Webhook] Received WhatsApp event', $traceId);

        return $data;
    }
}
