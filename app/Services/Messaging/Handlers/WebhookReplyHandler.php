<?php

namespace App\Services\Messaging\Handlers;

use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Models\Whatsapp\WhatsappMessage;
use App\Models\Customer;
use App\Helpers\LogHelper;

class WebhookReplyHandler implements MessageActionHandler
{
    public function __construct(
        private string $sender,
        private WhatsappMessage $keyword,  // row dari table whatsapp_messages
        private ?int $customerId = null    // optional
    ) {}

    public function action(): string
    {
        // Pastikan sama dengan slug atau override action
        return $this->keyword->slug;
    }

    public function validate(string $traceId): void
    {
        if (!$this->keyword?->reply_message) {
            LogHelper::logError(null, [
                'slug'     => $this->keyword->slug,
                'trace_id' => $traceId,
            ], 500, '[WebhookReplyHandler] Template reply_message kosong', $traceId);

            throw new \RuntimeException("Template reply_message kosong untuk slug {$this->keyword->slug}");
        }
    }

    public function build(string $traceId): array
    {
        return [
            'template_slug' => $this->keyword->slug,
            'to'            => $this->sender,
            'customer_id'   => $this->customerId, // bisa null
            'replacements'  => $this->buildReplacements(),
        ];
    }

    private function buildReplacements(): array
    {
        // Minimal placeholder agar %name% tidak error
        $name = $this->resolveNameFromDB($this->sender);

        return [
            '%name%' => $name ?? 'Pelanggan',
        ];
    }

    private function resolveNameFromDB(string $phone): ?string
    {
        $cust = Customer::where('contact', $phone)->first();
        return $cust?->name;
    }
}
