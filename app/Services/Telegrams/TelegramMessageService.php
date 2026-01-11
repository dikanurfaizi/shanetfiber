<?php

namespace App\Services\Telegrams;

use App\Helpers\LogHelper;
use App\Models\Customers\Customer;
use App\Models\Telegram\TelegramChatId;
use App\DataTransferObjects\TelegramJobDto;
use RuntimeException;

class TelegramMessageService
{
    public function __construct(
        protected TelegramStrategyResolver $resolver,
        protected MessageBuilder $builder,
        protected TelegramHandler $handler,
    ) {}

    public function process(TelegramJobDto $dto, string $traceId): void
    {
        $context = [
            'action'      => $dto->action,
            'customer_id' => $dto->customerId,
            'billing_id'  => $dto->billingId,
            'receipt_id'  => $dto->receiptId,
        ];

        // START
        LogHelper::logInfo(null, $context, 100, 'Telegram processing started', $traceId);

        try {

            // Resolve chat id
            if (!empty($dto->customerId)) {

                $customer = Customer::find($dto->customerId);
                if (!$customer) {
                    throw new RuntimeException("Customer not found");
                }

                $chatId = TelegramChatId::where('user_id', $customer->user_id)->value('chat_id');
                if (!$chatId) {
                    throw new RuntimeException("Chat ID not found for given customer");
                }
            } else {

                $chatId = $dto->chatId;
                if (!$chatId) {
                    throw new RuntimeException("Chat ID not provided");
                }
            }

            // Resolve strategy
            $strategy = $this->resolver->resolve($dto->action);

            // Build placeholders
            $placeholders = $strategy->build(
                $dto->customerId ?? 0,
                $dto->billingId,
                $dto->receiptId
            );

            if (!empty($dto->extra)) {
                $placeholders = array_merge($placeholders, $dto->extra);
            }

            // Build message
            $message = $this->builder->build(
                $strategy->slug(),
                $placeholders,
                $traceId
            );

            if (!$message) {
                throw new RuntimeException("Template not found for slug: " . $strategy->slug());
            }

            // Send
            $this->handler->send($chatId, $message, $traceId);

            // SUCCESS
            LogHelper::logInfo(null, array_merge($context, [
                'chat_id'  => $chatId,
                'strategy' => $strategy->slug()
            ]), 200, 'Telegram message sent', $traceId);
        } catch (\Throwable $e) {

            LogHelper::logError(
                null,
                array_merge($context, [
                    'exception' => class_basename($e)
                ]),
                500,
                'Telegram processing failed: ' . $e->getMessage(),
                $traceId
            );

            throw $e;
        }
    }
}
