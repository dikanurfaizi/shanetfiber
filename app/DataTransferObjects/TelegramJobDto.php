<?php

namespace App\DataTransferObjects;


class TelegramJobDto
{
    public function __construct(
        public readonly ?int $customerId = null,
        public readonly ?string $action = null,
        public readonly ?string $traceId = null,
        public readonly ?int $billingId = null,
        public readonly ?int $receiptId = null,
        public readonly ?array $extra = [],
        public readonly ?int $chatId = null
    ) {}
}
