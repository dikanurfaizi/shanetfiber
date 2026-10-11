<?php

namespace App\Services\Messaging\Handlers;

use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Models\WhatsApp\WhatsAppMessage;
use App\Models\Customers\Customer;
use App\Helpers\LogHelper;
use App\Helpers\FormatReceiver;

class WebhookReplyHandler implements MessageActionHandler
{
    private ?array $resolvedCustomer = null;

    public function __construct(
        private string $sender,
        private WhatsAppMessage $keyword,
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
        $customer = $this->resolveCustomer($this->sender);

        return [
            'template_slug' => $this->keyword->slug,
            'to'            => $this->sender,
            'customer_id'   => $customer['id'],   // aman, karena dijamin selalu ada key id
            'replacements'  => [
                '%name%' => $customer['name'] ?? 'Pelanggan'
            ]
        ];
    }

    private function resolveCustomer(string $phone): array
    {
        // Cache supaya tidak query DB dua kali
        if ($this->resolvedCustomer !== null) {
            return $this->resolvedCustomer;
        }

        $format = new FormatReceiver();

        /**
         * 1. Jika customerId ada → coba pakai ID
         */
        if (!empty($this->customerId)) {
            $cust = Customer::find($this->customerId);

            if ($cust) {
                return $this->resolvedCustomer = [
                    'id'   => $cust->id,
                    'name' => $cust->name,
                ];
            }
        }

        /**
         * 2. Fallback cari berdasarkan nomor
         */
        $normalized = $format->formatReceiver($phone, null, false);
        $displayed  = $format->formatReceiver($phone, 'display', false);

        $cust = Customer::where('contact', $normalized)
            ->orWhere('contact', $displayed)
            ->first();

        if ($cust) {
            return $this->resolvedCustomer = [
                'id'   => $cust->id,
                'name' => $cust->name,
            ];
        }

        /**
         * 3. Benar-benar bukan customer → tetap return array konsisten
         */
        return $this->resolvedCustomer = [
            'id'   => null,
            'name' => null,
        ];
    }
}
