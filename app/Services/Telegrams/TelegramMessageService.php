<?php

namespace App\Services\Telegrams;

use App\Helpers\LogHelper;
use App\Models\Customers\Customer;
use App\Models\Telegram\TelegramChatId;
use App\DataTransferObjects\TelegramJobDto;
use App\Models\Messaging\MessageEvent;
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

        $event = null;

        try {
            // Resolve chat id
            if ($dto->customerId) {
                $customer = Customer::find($dto->customerId);
                if (!$customer) {
                    throw new RuntimeException('Customer not found');
                }

                $chatId = TelegramChatId::where('user_id', $customer->user_id)->value('chat_id');
                if (!$chatId) {
                    throw new RuntimeException('Chat ID not found');
                }
            } else {
                $chatId = $dto->chatId;
                if (!$chatId) {
                    throw new RuntimeException('Chat ID not provided');
                }
            }

            // Resolve & build message
            $strategy = $this->resolver->resolve($dto->action);

            $placeholders = $strategy->build(
                $dto->customerId ?? 0,
                $dto->billingId,
                $dto->receiptId
            );

            if (!empty($dto->extra)) {
                $placeholders = array_merge($placeholders, $dto->extra);
            }

            $message = $this->builder->build(
                $strategy->slug(),
                $placeholders,
                $traceId
            );

            if (!$message) {
                throw new RuntimeException('Template not found: ' . $strategy->slug());
            }

            // =====================
            // CREATE EVENT (PENDING)
            // =====================
            $event = MessageEvent::create([
                'customer_id'     => $dto->customerId,
                'channel'         => 'telegram',
                'message_type'    => $dto->action,
                'meta'            => [
                    'chat_id' => $chatId,
                ],
                'status'          => 'pending',
                'stage'           => 'INIT',
                'request_payload' => [
                    'chat_id' => $chatId,
                    'message' => $message,
                ],
                'retry_count'     => 0,
                'trace_id'        => $traceId,
            ]);

            // =====================
            // SET PROCESSING
            // =====================
            $event->update([
                'stage'  => 'SEND_REQUEST',
            ]);

            // =====================
            // SEND
            // =====================
            $response = $this->handler->send($chatId, $message, $traceId);

            // =====================
            // SUCCESS UPDATE
            // =====================
            $event->update([
                'gateway_id'       => 1, // Telegram gateway static
                'status'           => 'success',
                'stage'            => 'SUCCESS',
                'sent_at'          => now(),
                'response_code'    => $response['status'],
                'response_message' => json_encode($response['raw']),
                'response_payload' => $response,
                'message_id'       => $response['message_id'],
                'retry_count'      => $event->retry_count + 1,
                'last_error_at'    => null,
            ]);
        } catch (\Throwable $e) {

            if ($event) {
                $event->update([
                    'status'           => 'failed',
                    'stage'            => 'FAILED_EXCEPTION',
                    'sent_at'          => now(),
                    'response_code'    => 'FAILED',
                    'response_message' => $e->getMessage(),
                    'response_payload' => [
                        'exception' => true,
                        'message'   => $e->getMessage(),
                    ],
                    'retry_count'   => $event->retry_count + 1,
                    'last_error_at' => now(),
                ]);
            }

            LogHelper::logError(
                null,
                array_merge($context, [
                    'trace_id'  => $traceId,
                    'exception' => class_basename($e),
                ]),
                500,
                'Telegram send failed',
                $traceId
            );

            throw $e;
        }
    }
}
