<?php
// app/Services/Telegrams/TelegramStrategyResolver.php
namespace App\Services\Telegrams;

use App\Enums\TelegramAction;
use App\Services\Telegrams\Strategies\{
    TelegramMessageStrategy,
    ActivationMessageStrategy,
    FullPaymentMessageStrategy,
    PaylaterMessageStrategy,
    OtherPaymentMessageStrategy,
    TestMessageStrategy,
    UnpaymentMessageStrategy
};

class TelegramStrategyResolver
{
    public function resolve(string $action): TelegramMessageStrategy
    {
        return match ($action) {
            TelegramAction::ACTIVATION->value            => new ActivationMessageStrategy(),
            TelegramAction::FULL_PAYMENT_TRANSFER->value => new FullPaymentMessageStrategy($action),
            TelegramAction::FULL_PAYMENT_CASH->value     => new FullPaymentMessageStrategy($action),
            TelegramAction::PAYLATER_PAYMENT->value      => new PaylaterMessageStrategy(),
            TelegramAction::UNPAYMENT->value             => new UnpaymentMessageStrategy(),
            TelegramAction::TEST_MESSAGE->value          => new TestMessageStrategy(),
            default                                      => new OtherPaymentMessageStrategy(),
        };
    }
}
