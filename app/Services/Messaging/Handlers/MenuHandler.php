<?php

namespace App\Services\Messaging\Handlers;

use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;
use App\Helpers\LogHelper;

class MenuHandler implements MessageActionHandler
{
    public function __construct(
        private string $type,
        private MessageBuilder $builder,
        private string $custContact,
        private string $custName
    ) {}

    public function action(): string
    {
        return $this->type;
    }

    public function validate(string $traceId): void
    {
        if (empty($this->billing->customer?->contact)) {
            LogHelper::logError(null, [
                'trace_id' => $traceId,
            ], 400, "Contact kosong saat validasi BillingStatusHandler", $traceId);
            throw new \RuntimeException('Contact kosong');
        }
    }

    public function build(string $traceId): array
    {
        return [
            'template_slug' => $this->type,
            'to'            => $this->custContact,
            'customer_id'   => null,
            'replacements'  => [
                '%name%'           => $this->custName
            ],
        ];
    }
}
