<?php
// app/Services/Telegrams/Strategies/FullPaymentMessageStrategy.php
namespace App\Services\Telegrams\Strategies;

class TestMessageStrategy implements TelegramMessageStrategy
{
    public function slug(): string
    {
        return 'test_message';
    }

    public function build(int $customerId, ?int $billingId, ?int $receiptId): array
    {
        return [
            '%testing_note%' => 'Pesan ini dikirim untuk pengujian koneksi gateway Telegram.'
        ];
    }
}
