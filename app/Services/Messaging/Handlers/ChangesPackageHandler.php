<?php

namespace App\Services\Messaging\Handlers;

use App\Enums\WhatsAppAction;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;
use InvalidArgumentException;
use Throwable;

class ChangesPackageHandler extends BaseBroadcastAwareHandler implements MessageActionHandler
{
    public function __construct(
        private WhatsAppAction|string $action,
        private MessageBuilder $builder,
        private array $packageData,
        string $category
    ) {
        $this->setCategory($category);
        $this->setRawContact($this->packageData['target_contact'] ?? '');
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

        if (empty($this->packageData['customer_id'])) {
            throw new InvalidArgumentException('Customer ID is required for package change notification.');
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
                'customer_id' => $this->packageData['customer_id'] ?? null,
            ],
            200,
            '[ChangesPackageHandler] Building payload message for package change notification',
            $traceId
        );

        try {
            $payload = [
                'template_slug' => $this->action(),
                'to'            => $this->packageData['target_contact'],
                'customer_id'   => $this->packageData['customer_id'],
                'replacements'  => [
                    '%name%'         => $this->packageData['customer_name'] ?? '-',
                    '%address%'      => $this->packageData['customer_address'] ?? '-',
                    '%email%'        => $this->packageData['customer_email'] ?? '-',
                    '%old_package%'  => $this->packageData['old_package_name'] ?? '-',
                    '%old_bill%'     => $this->builder->currency($this->packageData['old_package_price'] ?? 0),
                    '%new_package%'  => $this->packageData['new_package_name'] ?? '-',
                    '%new_bill%'     => $this->builder->currency($this->packageData['new_package_price'] ?? 0),
                    '%admin%'        => $this->packageData['admin_name'] ?? '-',
                    '%cs_contact%'   => $this->packageData['admin_contact'] ?? '-',
                    '%customer_id%'  => $this->packageData['customer_code'] ?? '-',
                    '%account_bank%' => $this->packageData['admin_bank_info'] ?? '-',
                    '%member_type%'  => $this->packageData['member_type'] ?? 'Non Member',
                ],
            ];

            LogHelper::logInfo(
                null,
                [
                    'handler'     => static::class,
                    'customer_id' => $this->packageData['customer_id'],
                    'action_type' => $this->action(),
                ],
                200,
                '[ChangesPackageHandler] Payload successfully built',
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
                    'customer_id'     => $this->packageData['customer_id'] ?? null,
                    'error_message'   => $e->getMessage(),
                    'exception_class' => get_class($e),
                    'file'            => $e->getFile(),
                    'line'            => $e->getLine(),
                ],
                $statusCode,
                '[ChangesPackageHandler] Unexpected error occurred while building payload',
                $traceId,
                $timer->lap()
            );

            throw $e;
        }
    }
}
