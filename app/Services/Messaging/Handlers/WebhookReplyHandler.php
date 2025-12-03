<?php

namespace App\Services\Messaging\Handlers;

use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Models\Whatsapp\WhatsappMessage;
use App\Models\Customer;
use App\Helpers\LogHelper;
use App\Helpers\FormatReceiver;

class WebhookReplyHandler implements MessageActionHandler
{
    public function __construct(
        private string $sender,
        private WhatsappMessage $keyword,
        private ?int $customerId = null,
        string $category
    ) {}

    public function action(): string
    {
        return $this->keyword->slug;
    }

    public function validate(string $traceId): void
    {
        if (!$this->keyword?->reply_message) {
            LogHelper::logError(null, [
                'slug'     => $this->keyword->slug,
                'trace_id' => $traceId,
            ], 500, '[WebhookReplyHandler] Template reply_message kosong', $traceId);

            throw new \RuntimeException("Template reply_message kosong");
        }
    }

    public function build(string $traceId): array
    {
        return [
            'template_slug' => $this->keyword->slug,
            'to'            => $this->sender,
            'customer_id'   => $this->customerId,
            'replacements'  => $this->buildReplacements(),
        ];
    }

    private function buildReplacements(): array
    {
        $name = $this->resolveNameFromDB($this->sender);
        return [
            '%name%' => $name ?? 'Pelanggan',
        ];
    }

    private function resolveNameFromDB(string $phone): ?string
    {
        $format = new FormatReceiver();
        $cust = Customer::where('contact', $format->formatReceiver($phone, null, false))
            ->orWhere('contact', $format->formatReceiver($phone, 'display', false))
            ->first();
        return $cust?->name;
    }
}
