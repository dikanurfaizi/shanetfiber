<?php

namespace App\Services\Messaging\Handlers;

use App\Enums\WhatsAppAction;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;
use InvalidArgumentException;
use Throwable;

class CreateOrderHandler extends BaseBroadcastAwareHandler implements MessageActionHandler
{
    public function __construct(
        private WhatsAppAction|string $action,
        private MessageBuilder $builder,
        private array $orderData,
        string $category
    ) {
        $this->setCategory($category);
        $this->setRawContact($this->orderData['target_contact'] ?? '');
    }

    public function action(): string
    {
        return $this->action instanceof WhatsAppAction
            ? $this->action->value
            : $this->action;
    }

    public function validate(string $traceId): void
    {
        $this->validateBase($traceId);

        if (empty($this->orderData['order_id'])) {
            throw new InvalidArgumentException('Order ID is required for order created notification.');
        }
    }

    public function build(string $traceId): array
    {
        $timer = new ExecutionTimer();

        LogHelper::logInfo(
            null,
            [
                'handler'     => static::class,
                'action'      => $this->action(),
                'billing_id'  => $this->orderData['billing_id'] ?? null,
                'order_id'    => $this->orderData['order_id'] ?? null,
                'customer_id' => $this->orderData['customer_id'] ?? null,
            ],
            200,
            '[CreateOrderHandler] Building payload message for order created notification',
            $traceId
        );

        try {
            $payload = [
                'template_slug' => $this->action(),
                'to'            => $this->orderData['target_contact'],
                'customer_id'   => $this->orderData['customer_id'],
                'replacements'  => [
                    '%name%'            => $this->orderData['customer_name'] ?? '-',
                    '%customer_id%'     => $this->orderData['customer_code'] ?? '-',
                    '%billing_number%'  => $this->orderData['billing_number'] ?? '-',
                    '%package%'         => $this->orderData['package_name'] ?? '-',
                    '%admin%'           => $this->orderData['admin_name'] ?? '-',
                    '%cs_contact%'      => $this->orderData['admin_contact'] ?? '-',
                    '%payment_details%' => $this->orderData['payment_details'] ?? '-',
                ],
            ];

            LogHelper::logInfo(
                null,
                [
                    'handler'     => static::class,
                    'customer_id' => $this->orderData['customer_id'],
                    'order_id'    => $this->orderData['order_id'],
                    'action_type' => $this->action(),
                ],
                200,
                '[CreateOrderHandler] Payload successfully built',
                $traceId,
                $timer->lap()
            );

            return $payload;
        } catch (Throwable $e) {
            $exceptionCode = $e->getCode();
            $statusCode = (is_numeric($exceptionCode) && $exceptionCode >= 400 && $exceptionCode <= 599)
                ? (int) $exceptionCode
                : 500;

            LogHelper::logError(
                null,
                [
                    'handler'         => static::class,
                    'billing_id'      => $this->orderData['billing_id'] ?? null,
                    'order_id'        => $this->orderData['order_id'] ?? null,
                    'error_message'   => $e->getMessage(),
                    'exception_class' => get_class($e),
                    'file'            => $e->getFile(),
                    'line'            => $e->getLine(),
                ],
                $statusCode,
                '[CreateOrderHandler] Unexpected error occurred while building payload',
                $traceId,
                $timer->lap()
            );

            throw $e;
        }
    }
}
