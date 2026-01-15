<?php

namespace App\Services\Whatsapp\CloudAPI\Templates\Runtime;

use App\Models\Whatsapp\WhatsappGatewayAccount;

class TemplateContext
{
    public function __construct(
        public string $recipient,
        public string $templateName,
        public string $language,
        public WhatsappGatewayAccount $account,

        // ===== domain-specific parameters =====
        public array $bodyValues = [],     // e.g. ["Rasna", "Jan 2026", ...]
        public array $headerValues = [],   // e.g. ["Pembayaran diterima"] OR media meta
        public array $buttonValues = [],   // e.g. [0 => ["<receipt_code>"]]
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
