<?php

namespace App\Services\Messaging\Handlers;

use App\Enums\WhatsAppAction;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;
use InvalidArgumentException;
use Throwable;

class BroadcastMessageHandler extends BaseBroadcastAwareHandler implements MessageActionHandler
{
    public function __construct(
        private WhatsAppAction|string $action,
        private MessageBuilder $builder,
        private array $broadcastData,
        string $category
    ) {
        $this->setCategory($category);
        $this->setRawContact($this->broadcastData['target_contact'] ?? '');
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

        if (empty($this->broadcastData['target_contact'])) {
            throw new InvalidArgumentException('Target contact is required for broadcast message.');
        }
    }

    public function build(string $traceId): array
    {
        $timer = new ExecutionTimer();
        $customerId = $this->broadcastData['customer_id'] ?? null;
        $customerCode = $this->broadcastData['customer_code'] ?? $customerId;

        LogHelper::logInfo(
            null,
            [
                'handler'        => static::class,
                'action'         => $this->action(),
                'customer_id'    => $customerId,
                'customer_code'  => $customerCode,
                'target_contact' => $this->broadcastData['target_contact'] ?? null,
                'is_override'    => !empty($this->broadcastData['is_override']),
            ],
            200,
            '[BroadcastMessageHandler] Building payload message for broadcast notification',
            $traceId
        );

        try {
            $payload = [
                'template_slug' => $this->action(),
                'to'            => $this->broadcastData['target_contact'],
                'customer_id'   => $customerId,
                'replacements'  => [
                    // === Standard Billing Replacements ===
                    '%name%'            => $this->broadcastData['customer_name'] ?? '-',
                    '%address%'         => $this->broadcastData['customer_address'] ?? '-',
                    '%customer_id%'     => $customerCode,
                    '%billing_number%'  => $this->broadcastData['billing_number'] ?? '-',
                    '%account_bank%'    => $this->broadcastData['admin_bank_info'] ?? '-',
                    '%cs_contact%'      => $this->broadcastData['cs_contact'] ?? '-',
                    '%packages%'        => $this->broadcastData['packages_detail'] ?? '-',
                    '%total_bill%'      => $this->broadcastData['total_bill_formatted'] ?? 'Rp 0',
                    '%total-bill%'      => $this->broadcastData['total_bill_formatted'] ?? 'Rp 0',
                    '%due_date%'        => $this->broadcastData['due_date'] ?? '-',
                    '%deadline%'        => $this->broadcastData['due_date'] ?? '-',
                    '%day%'             => $this->broadcastData['day'] ?? '0',
                    '%count_bill%'      => $this->broadcastData['count_bill'] ?? '1',
                    '%admin%'           => $this->broadcastData['admin_name'] ?? 'Admin',
                    '%company_address%' => $this->broadcastData['company_address'] ?? '-',

                    // === Payment Channel Update Specific Replacements ===
                    '%customer_name%'   => $this->broadcastData['customer_name'] ?? '-',
                    '%effective_date%'  => $this->broadcastData['effective_date'] ?? '-',
                    '%services%'        => $this->broadcastData['services'] ?? 'Layanan Berlangganan',
                    '%account_total%'   => $this->broadcastData['account_total'] ?? '0',
                    '%account_list%'    => $this->broadcastData['account_list'] ?? '-',
                    '%whatsapp_number%' => $this->broadcastData['whatsapp_number'] ?? '-',
                ],
            ];

            LogHelper::logInfo(
                null,
                [
                    'handler'     => static::class,
                    'customer_id' => $customerId,
                    'action_type' => $this->action(),
                ],
                200,
                '[BroadcastMessageHandler] Payload successfully built',
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
                    'customer_id'     => $customerId,
                    'error_message'   => $e->getMessage(),
                    'exception_class' => get_class($e),
                    'file'            => $e->getFile(),
                    'line'            => $e->getLine(),
                ],
                $statusCode,
                '[BroadcastMessageHandler] Unexpected error occurred while building payload',
                $traceId,
                $timer->lap()
            );

            throw $e;
        }
    }
}
