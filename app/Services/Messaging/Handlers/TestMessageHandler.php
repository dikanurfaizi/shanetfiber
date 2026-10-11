<?php

namespace App\Services\Messaging\Handlers;

use App\Enums\WhatsAppAction;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;
use InvalidArgumentException;
use Throwable;

class TestMessageHandler extends BaseBroadcastAwareHandler implements MessageActionHandler
{
    public function __construct(
        private WhatsAppAction|string $action,
        private MessageBuilder $builder,
        private array $testData,
        string $category
    ) {
        $this->setCategory($category);
        $this->setRawContact($this->testData['receiver_number'] ?? '');
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

        if (empty($this->testData['receiver_number'])) {
            throw new InvalidArgumentException('Receiver number is required.');
        }
    }

    public function build(string $traceId): array
    {
        $timer = new ExecutionTimer();

        LogHelper::logInfo(
            null,
            [
                'handler'    => static::class,
                'action'     => $this->action(),
                'receiver'   => $this->testData['receiver_number'] ?? null,
                'gateway_id' => $this->testData['gateway_id'] ?? null,
            ],
            200,
            '[TestMessageHandler] Building payload message for test message notification',
            $traceId
        );

        try {
            $payload = [
                'template_slug' => $this->action(),
                'to'            => $this->testData['receiver_number'],
                'customer_id'   => null,
                'replacements'  => [
                    '%testing_note%' => $this->testData['testing_note'] ?? 'Pesan ini dikirim untuk pengujian koneksi gateway WhatsApp',
                ],
                'gateway_id'    => $this->testData['gateway_id'] ?? null,
            ];

            LogHelper::logInfo(
                null,
                [
                    'handler'     => static::class,
                    'action_type' => $this->action(),
                    'receiver'    => $this->testData['receiver_number'] ?? null,
                ],
                200,
                '[TestMessageHandler] Payload successfully built',
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
                    'receiver'        => $this->testData['receiver_number'] ?? null,
                    'error_message'   => $e->getMessage(),
                    'exception_class' => get_class($e),
                    'file'            => $e->getFile(),
                    'line'            => $e->getLine(),
                ],
                $statusCode,
                '[TestMessageHandler] Unexpected error occurred while building payload',
                $traceId,
                $timer->lap()
            );

            throw $e;
        }
    }
}
