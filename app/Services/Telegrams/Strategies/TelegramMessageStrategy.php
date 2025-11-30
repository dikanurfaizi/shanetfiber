<?php
// app/Services/Telegrams/Strategies/TelegramMessageStrategy.php
namespace App\Services\Telegrams\Strategies;

interface TelegramMessageStrategy
{
    /**
     * Build placeholder mapping for message template
     *
     * @return array<string,string|int|float|null>
     */
    public function build(
        int $customerId,
        ?int $billingId,
        ?int $receiptId
    ): array;

    /**
     * Slug/template key yang dipakai MessageBuilder::build()
     */
    public function slug(): string;
}
