<?php

namespace App\Services\Messaging\Handlers;

use App\Enums\WhatsAppAction;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;
use Throwable;

class BillingStatusHandler extends BaseBroadcastAwareHandler implements MessageActionHandler
{
    public function __construct(
        private WhatsAppAction|string $action,
        private MessageBuilder $builder,
        private array $billingData,
        string $category
    ) {
        $this->setCategory($category);
        $this->setRawContact($this->billingData['target_contact'] ?? '');
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
    }

    public function build(string $traceId): array
    {
        $timer = new ExecutionTimer();

        LogHelper::logInfo(
            null,
            [
                'handler'            => static::class,
                'action'             => $this->action(),
                'customer_id'        => $this->billingData['customer_id'] ?? null,
                'customer_unique_id' => $this->billingData['customer_code'] ?? null,
                'billing_id'         => $this->billingData['billing_id'] ?? null,
                'target_contact'     => $this->billingData['target_contact'] ?? null,
            ],
            200,
            '[BillingStatusHandler] Building payload message for billing status/paylater notification',
            $traceId
        );

        try {
            $payload = [
                'template_slug' => $this->action(),
                'to'            => $this->billingData['target_contact'],
                'customer_id'   => $this->billingData['customer_id'],
                'replacements'  => [
                    '%name%'                    => $this->billingData['customer_name'] ?? '-',
                    '%customer_id%'             => $this->billingData['customer_code'] ?? '-',
                    '%billing_number%'          => $this->billingData['billing_number'] ?? 'Nomor tidak tersedia',
                    '%package%'                 => $this->billingData['package_name'] ?? '-',
                    '%periode%'                 => $this->billingData['periode'] ?? '-',
                    '%bill%'                    => $this->builder->currency($this->billingData['base_price'] ?? 0),
                    '%addons_section%'          => $this->builder->addonsSection($this->billingData['addons'] ?? []),
                    '%member_discount_section%' => $this->builder->memberDiscountSection(
                        $this->billingData['membership_name'] ?? null,
                        $this->billingData['discount_amount'] ?? 0
                    ),
                    '%total%'                   => $this->builder->currency($this->billingData['total_amount'] ?? 0),
                    '%account_bank%'            => $this->billingData['admin_bank_info'] ?? '-',
                    '%admin%'                   => $this->billingData['admin_name'] ?? 'Admin',
                    '%cs_contact%'              => $this->billingData['admin_contact'] ?? '-',
                    '%teller%'                  => $this->billingData['teller_name'] ?? 'System',
                    '%paylater%'                => $this->billingData['paylater_formatted'] ?? '-',
                ],
            ];

            LogHelper::logInfo(
                null,
                [
                    'handler'            => static::class,
                    'customer_id'        => $this->billingData['customer_id'],
                    'customer_unique_id' => $this->billingData['customer_code'],
                    'billing_id'         => $this->billingData['billing_id'],
                    'action_type'        => $this->action(),
                    'total_amount'       => $this->billingData['total_amount'] ?? 0,
                    'target_phone'       => $this->billingData['target_contact'],
                ],
                200,
                '[BillingStatusHandler] Payload successfully built',
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
                    'customer_id'     => $this->billingData['customer_id'] ?? null,
                    'billing_id'      => $this->billingData['billing_id'] ?? null,
                    'error_message'   => $e->getMessage(),
                    'exception_class' => get_class($e),
                    'file'            => $e->getFile(),
                    'line'            => $e->getLine(),
                ],
                $statusCode,
                '[BillingStatusHandler] Unexpected error occurred while building payload',
                $traceId,
                $timer->lap()
            );

            throw $e;
        }
    }
}
