<?php

namespace App\Services\WhatsApp\CloudAPI\Templates\Runtime;

use App\Models\WhatsApp\WhatsAppGatewayAccount;

class TemplateContext
{
    public function __construct(
        public string $recipient,
        public string $templateName,
        public string $language,
        public WhatsAppGatewayAccount $account,

        // ===== domain-specific parameters =====
        public array $bodyValues = [],     // e.g. ["Rasna", "Jan 2026", ...]
        public array $headerValues = [],   // e.g. ["Pembayaran diterima"] OR media meta
        public array $buttonValues = [],   // e.g. [0 => ["<receipt_code>"]]

        public ?string $text = null,
        public ?string $messageId = null
    ) {}

    /**
     * Backward compat helper (optional):
     * jika ada kode lama yang masih pakai $context->values.
     * Anda bisa hapus nanti setelah semua handler beres.
     */
    public function values(): array
    {
        return [
            'body' => $this->bodyValues,
            'header' => $this->headerValues,
            'buttons' => $this->buttonValues,
        ];
    }
}
