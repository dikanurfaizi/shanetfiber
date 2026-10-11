<?php

namespace App\Services\Messaging\Handlers;

use App\Enums\WhatsAppAction;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;
use InvalidArgumentException;
use Throwable;

class DirectMessageHandler extends BaseBroadcastAwareHandler implements MessageActionHandler
{
    public function __construct(
        private WhatsAppAction|string $action,
        private MessageBuilder $builder,
        private array $directData,
        string $category
    ) {
        $this->setCategory($category);
        $this->setRawContact($this->directData['receiver_number'] ?? '');
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

        if (empty($this->directData['receiver_number'])) {
            throw new InvalidArgumentException('Receiver number is required for direct message.');
        }
    }

    public function build(string $traceId): array
    {
        $timer = new ExecutionTimer();

        LogHelper::logInfo(
            null,
            [
                'handler'  => static::class,
                'action'   => $this->action(),
                'receiver' => $this->directData['receiver_number'] ?? null,
                'event_id' => $this->directData['event_id'] ?? null,
            ],
            200,
            '[DirectMessageHandler] Building payload message for direct message notification',
            $traceId
        );

        try {
            $payload = [
                'template_slug' => $this->action(),
                'to'            => $this->directData['receiver_number'],
                'customer_id'   => $this->directData['customer_id'] ?? null,
                'replacements'  => $this->directData['replacements'] ?? [],
                'raw_payload'   => $this->directData['raw_payload'] ?? '',
            ];

            LogHelper::logInfo(
                null,
                [
                    'handler'     => static::class,
                    'action_type' => $this->action(),
                    'receiver'    => $this->directData['receiver_number'] ?? null,
                ],
                200,
                '[DirectMessageHandler] Payload successfully built',
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
                    'receiver'        => $this->directData['receiver_number'] ?? null,
                    'error_message'   => $e->getMessage(),
                    'exception_class' => get_class($e),
                    'file'            => $e->getFile(),
                    'line'            => $e->getLine(),
                ],
                $statusCode,
                '[DirectMessageHandler] Unexpected error occurred while building payload',
                $traceId,
                $timer->lap()
            );

            throw $e;
        }
    }
}
