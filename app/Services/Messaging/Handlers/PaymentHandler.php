<?php

namespace App\Services\Messaging\Handlers;

use App\Enums\WhatsAppAction;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;
use RuntimeException;
use Throwable;

class PaymentHandler extends BaseBroadcastAwareHandler implements MessageActionHandler
{
    public function __construct(
        private WhatsAppAction|string $action,
        private MessageBuilder $builder,
        private array $paymentData,
        string $category
    ) {
        $this->setCategory($category);
        $this->setRawContact($this->paymentData['customer_contact'] ?? '');
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

        if (empty($this->paymentData['receipt_id'])) {
            throw new RuntimeException('Receipt ID is required for payment notification.');
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
                'billing_id'  => $this->paymentData['billing_id'] ?? null,
                'receipt_id'  => $this->paymentData['receipt_id'] ?? null,
                'customer_id' => $this->paymentData['customer_id'] ?? null,
            ],
            200,
            '[PaymentHandler] Building payload message for payment notification',
            $traceId
        );

        try {
            $payload = [
                'template_slug' => $this->action(),
                'to'            => $this->paymentData['customer_contact'],
                'customer_id'   => $this->paymentData['customer_id'],
                'replacements'  => [
                    '%name%'                    => $this->paymentData['customer_name'] ?? '-',
                    '%address%'                 => $this->paymentData['customer_address'] ?? '-',
                    '%customer_id%'             => $this->paymentData['customer_code'] ?? '-',
                    '%billing_number%'          => $this->paymentData['receipt_number'] ?? '-',
                    '%package%'                 => $this->paymentData['package_name'] ?? '-',
                    '%periode%'                 => $this->paymentData['periode'] ?? '-',
                    '%bill%'                    => $this->builder->currency($this->paymentData['package_price'] ?? 0),
                    '%addons_section%'          => $this->builder->addonsSection($this->paymentData['addons'] ?? []),
                    '%member_discount_section%' => $this->builder->memberDiscountSection(
                        $this->paymentData['membership_name'] ?? null,
                        $this->paymentData['discount_amount'] ?? 0
                    ),
                    '%total%'                   => $this->builder->currency($this->paymentData['total_price'] ?? 0),
                    '%payment_time%'            => $this->paymentData['payment_time_formatted'] ?? '-',
                    '%teller%'                  => $this->paymentData['teller_name'] ?? '-',
                    '%payment_methode%'         => $this->paymentData['payment_method_text'] ?? '-',
                    '%cs_contact%'              => $this->paymentData['cs_contact'] ?? '-',
                ],
            ];

            LogHelper::logInfo(
                null,
                [
                    'handler'     => static::class,
                    'customer_id' => $this->paymentData['customer_id'],
                    'receipt_id'  => $this->paymentData['receipt_id'],
                    'action_type' => $this->action(),
                ],
                200,
                '[PaymentHandler] Payload successfully built',
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
                    'billing_id'      => $this->paymentData['billing_id'] ?? null,
                    'receipt_id'      => $this->paymentData['receipt_id'] ?? null,
                    'error_message'   => $e->getMessage(),
                    'exception_class' => get_class($e),
                    'file'            => $e->getFile(),
                    'line'            => $e->getLine(),
                ],
                $statusCode,
                '[PaymentHandler] Unexpected error occurred while building payload',
                $traceId,
                $timer->lap()
            );

            throw $e;
        }
    }
}
