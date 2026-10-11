<?php

namespace App\Services\Notification\WhatsApp\Single;

use App\Enums\WhatsAppAction;
use App\Helpers\DelayMessage;
use App\Helpers\DummyReceiptHelper;
use App\Helpers\ExecutionTimer;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Telegram\TelegramGateway;
use App\Services\Messaging\Collectors\AdminBankCollector;
use App\Services\Messaging\Handlers\BotLinkHandler;
use App\Services\Messaging\MessageBuilder;
use App\Services\Messaging\MessageDispatcher;
use App\Services\WhatsApp\CloudAPI\Messages\TemplateMessageService;
use App\Services\WhatsApp\WhatsAppGatewayResolver;
use Throwable;

class TelegramBotLinkNotificationService extends AbstractSingleNotificationService
{
    public function __construct(
        WhatsAppGatewayResolver $gatewayResolver,
        TemplateMessageService $templateMessageService,
        MessageDispatcher $dispatcher,
        AdminBankCollector $bankCollector,
        FormatReceiver $formatNumber,
        MessageBuilder $builder,
        DummyReceiptHelper $dummyReceipt,
        private DelayMessage $delayMessage
    ) {
        parent::__construct($gatewayResolver, $templateMessageService, $dispatcher, $bankCollector, $formatNumber, $builder, $dummyReceipt);
    }

    public function send(
        WhatsAppAction $action,
        string $receiverNumber,
        TelegramGateway $telegramGateway,
        ?int $eventId,
        string $traceId
    ): void {
        $timer = new ExecutionTimer();

        try {
            // 1. Resolve Gateway Context
            $context = $this->resolveGatewayContext('telegram_bot', $traceId, $timer);
            if (!$context) return;

            $gateway = $context['gateway'];
            $account = $context['account'];

            $recipient = $this->formatNumber->formatReceiver($receiverNumber, null, false);

            // 2. Prepare Data Secara Terpusat
            $botData = $this->prepareBotData($recipient, $telegramGateway);

            if ($gateway->type === 'cloud') {
                $data = [
                    'recipient'    => $recipient,
                    'bot_username' => $botData['bot_name'],
                    'account'      => $account,
                    'event_id'     => $eventId,
                ];

                // $this->templateMessageService->sendTelegramBotLink($data);
            } else {
                // Direct Dispatch Handler GOWA (Local Engine)
                $this->dispatcher->dispatch(
                    action: $action->value,
                    handlerFactory: fn(string $category) => new BotLinkHandler(
                        action: $action,
                        builder: $this->builder,
                        botData: $botData,
                        category: $category
                    ),
                    traceId: $traceId,
                    eventId: $eventId,
                    deviceId: $context['deviceId']
                );
            }

            LogHelper::logInfo(null, [
                'receiver'     => $recipient,
                'gateway_type' => $gateway->type,
                'bot_name'     => $telegramGateway->name,
            ], 200, 'Telegram bot link notification dispatched successfully.', $traceId, $timer->lap());
        } catch (Throwable $e) {
            $exceptionCode = $e->getCode();
            $statusCode = (is_numeric($exceptionCode) && $exceptionCode >= 400 && $exceptionCode <= 599)
                ? (int) $exceptionCode
                : 500;

            LogHelper::logError(null, [
                'receiver'        => $receiverNumber,
                'error_message'   => $e->getMessage(),
                'exception_class' => get_class($e),
                'file'            => $e->getFile(),
                'line'            => $e->getLine(),
            ], $statusCode, 'Failed to send Telegram bot link notification', $traceId, $timer->lap());
        }
    }

    /**
     * Menyiapkan data bot dan membangun URL Telegram bot secara terpusat.
     */
    private function prepareBotData(string $recipient, TelegramGateway $telegramGateway): array
    {
        $botName = $telegramGateway->name ?? '';
        $startCommand = urlencode('/start');
        $botUrl = !empty($botName) ? "https://t.me/{$botName}?text={$startCommand}" : '';

        return [
            'receiver_number' => $recipient,
            'bot_name'        => $botName,
            'bot_url'         => $botUrl,
        ];
    }
}
