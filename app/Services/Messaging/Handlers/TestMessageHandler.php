<?php

namespace App\Services\Messaging\Handlers;

use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Helpers\LogHelper;

class TestMessageHandler implements MessageActionHandler
{
    public function __construct(
        private string $receiver,
        private int $gatewayId,
        private string $templateSlug,
        private string $category
    ) {}

    public function action(): string
    {
        return $this->templateSlug; // jadi dinamis dari DB
    }

    public function validate(?string $traceId = null): void
    {
        if (empty($this->receiver)) {
            throw new \InvalidArgumentException('Receiver number is required.');
        }
    }

    public function build(?string $traceId = null): array
    {
        return [
            'to' => $this->receiver,
            'customer_id' => null,
            'template_slug' => $this->templateSlug,
            'replacements' => [
                '%testing_note%' => 'Pesan ini dikirim untuk pengujian koneksi gateway WhatsApp',
            ],
            'gateway_id' => $this->gatewayId,
        ];
    }
}
