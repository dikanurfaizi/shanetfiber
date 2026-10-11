<?php

namespace App\Services\Messaging\Handlers;

use App\Enums\WhatsAppAction;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\Customers\CustomerSubscription;
use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;
use Throwable;

class ActivationHandler extends BaseBroadcastAwareHandler implements MessageActionHandler
{
    public function __construct(
        private WhatsAppAction|string $action,
        private CustomerSubscription $customerSubscription,
        private MessageBuilder $builder,
        private array $activationData,
        string $category
    ) {
        $this->setCategory($category);
        $this->setRawContact($customerSubscription->customer->contact);
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
        $customer = $this->customerSubscription->customer;

        LogHelper::logInfo(
            null,
            [
                'handler'     => static::class,
                'action'      => $this->action(),
                'customer_id' => $customer->id,
            ],
            200,
            '[ActivationHandler] Building payload message for customer activation notification',
            $traceId
        );

        try {
            $payload = [
                'template_slug' => $this->action(),
                'to'            => $customer->contact,
                'customer_id'   => $customer->id,
                'replacements'  => [
                    '%name%'               => $customer->name,
                    '%address%'            => $customer->address,
                    '%email%'              => $customer->email ?? '-',
                    '%cs_contact%'         => $this->activationData['admin_contact'] ?? '-',
                    '%customer_id%'        => $customer->customer_id,
                    '%admin%'              => $this->activationData['admin_name'] ?? '-',
                    '%package_name%'       => $this->customerSubscription->package->name ?? '-',
                    '%package_price%'      => $this->builder->currency($this->customerSubscription->package->price ?? 0),
                    '%member_type%'        => $this->activationData['member_type'] ?? 'Non Member',
                    '%account_bank%'       => $this->activationData['admin_bank_info'] ?? '-',
                    '%calculation_method%' => $this->activationData['calculation_method'] ?? 'Biaya Penuh',
                    '%prorate_note%'       => $this->activationData['prorate_note'] ?? '',
                    '%grand_total%'        => $this->activationData['grand_total_formatted']
                        ?? $this->builder->currency($this->activationData['fallback_price'] ?? 0),
                ],
            ];

            LogHelper::logInfo(
                null,
                [
                    'handler'     => static::class,
                    'customer_id' => $customer->id,
                    'action_type' => $this->action(),
                ],
                200,
                '[ActivationHandler] Payload successfully built',
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
                    'customer_id'     => $customer->id ?? null,
                    'error_message'   => $e->getMessage(),
                    'exception_class' => get_class($e),
                    'file'            => $e->getFile(),
                    'line'            => $e->getLine(),
                ],
                $statusCode,
                '[ActivationHandler] Unexpected error occurred while building payload',
                $traceId,
                $timer->lap()
            );

            throw $e;
        }
    }
}
