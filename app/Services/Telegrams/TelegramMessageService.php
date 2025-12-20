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
        // Context dasar untuk seluruh log
        $context = [
            'action'      => $dto->action,
            'customer_id' => $dto->customerId,
            'billing_id'  => $dto->billingId,
            'receipt_id'  => $dto->receiptId,
        ];

        LogHelper::logInfo(
            null,
            $context,
            100,
            'START Telegram message processing',
            $traceId
        );

        try {

            // --- Step 1 : Resolve Chat ID ---
            LogHelper::logInfo(null, $context, 120, 'Step 1 — Resolving chat_id', $traceId);

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

            // --- Step 2 : Resolve Strategy ---
            LogHelper::logInfo(null, array_merge($context, [
                'resolved_chat_id' => $chatId
            ]), 140, 'Step 2 — Resolving strategy', $traceId);

            $strategy = $this->resolver->resolve($dto->action);

            // --- Step 3 : Build Placeholders ---
            LogHelper::logInfo(null, $context, 160, 'Step 3 — Building placeholders', $traceId);

            $placeholders = $strategy->build(
                $dto->customerId ?? 0,
                $dto->billingId,
                $dto->receiptId
            );

            if (!empty($dto->extra)) {
                $placeholders = array_merge($placeholders, $dto->extra);
            }

            // --- Step 4 : Build Message ---
            LogHelper::logInfo(null, $context, 180, 'Step 4 — Building message', $traceId);

            $message = $this->builder->build(
                $strategy->slug(),
                $placeholders,
                $traceId
            );

            if (!$message) {
                throw new RuntimeException("Template not found for slug: " . $strategy->slug());
            }

            // --- Step 5 : Sending Message ---
            LogHelper::logInfo(null, array_merge($context, [
                'chat_id' => $chatId,
                'strategy' => $strategy->slug()
            ]), 200, 'Step 5 — Sending to Telegram API', $traceId);

            $this->handler->send($chatId, $message, $traceId);

            // --- SUCCESS ---
            LogHelper::logInfo(null, array_merge($context, [
                'chat_id'  => $chatId,
                'strategy' => $strategy->slug()
            ]), 250, 'Telegram message sent successfully', $traceId);
        } catch (\Throwable $e) {

            LogHelper::logError(
                null,
                $context,
                500,
                'Telegram processing error: ' . $e->getMessage(),
                $traceId
            );

            throw $e; // biar job retry / DLQ
        }
    }
}
