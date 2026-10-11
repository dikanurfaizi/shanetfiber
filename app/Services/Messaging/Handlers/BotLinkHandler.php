<?php

namespace App\Services\Messaging\Handlers;

use App\Enums\WhatsAppAction;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;
use Throwable;

class BotLinkHandler extends BaseBroadcastAwareHandler implements MessageActionHandler
{
    public function __construct(
        private WhatsAppAction|string $action,
        private MessageBuilder $builder,
        private array $botData,
        string $category
    ) {
        $this->setCategory($category);
        $this->setRawContact($this->botData['receiver_number'] ?? '');
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
                'handler'  => static::class,
                'action'   => $this->action(),
                'receiver' => $this->botData['receiver_number'] ?? null,
                'bot_name' => $this->botData['bot_name'] ?? null,
            ],
            200,
            '[BotLinkHandler] Building payload message for Telegram bot link notification',
            $traceId
        );

        try {
            $payload = [
                'template_slug' => $this->action(),
                'to'            => $this->botData['receiver_number'],
                'customer_id'   => null,
                'replacements'  => [
                    '%bot_name%' => $this->botData['bot_name'] ?? '',
                    '%bot_url%'  => $this->botData['bot_url'] ?? '',
                ],
            ];

            LogHelper::logInfo(
                null,
                [
                    'handler'     => static::class,
                    'action_type' => $this->action(),
                    'receiver'    => $this->botData['receiver_number'] ?? null,
                ],
                200,
                '[BotLinkHandler] Payload successfully built',
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
                    'receiver'        => $this->botData['receiver_number'] ?? null,
                    'error_message'   => $e->getMessage(),
                    'exception_class' => get_class($e),
                    'file'            => $e->getFile(),
                    'line'            => $e->getLine(),
                ],
                $statusCode,
                '[BotLinkHandler] Unexpected error occurred while building payload',
                $traceId,
                $timer->lap()
            );

            throw $e;
        }
    }
}
