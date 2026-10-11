<?php

namespace App\Services\Notification\WhatsApp\Single;

use App\Enums\WhatsAppAction;
use App\Helpers\DummyReceiptHelper;
use App\Helpers\ExecutionTimer;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Messaging\MessageEvent;
use App\Services\Messaging\Collectors\AdminBankCollector;
use App\Services\Messaging\Handlers\DirectMessageHandler;
use App\Services\Messaging\MessageBuilder;
use App\Services\Messaging\MessageDispatcher;
use App\Services\WhatsApp\CloudAPI\Messages\TemplateMessageService;
use App\Services\WhatsApp\WhatsAppGatewayResolver;
use RuntimeException;
use Throwable;

class DirectMessageNotificationService extends AbstractSingleNotificationService
{
    public function __construct(
        WhatsAppGatewayResolver $gatewayResolver,
        TemplateMessageService $templateMessageService,
        MessageDispatcher $dispatcher,
        AdminBankCollector $bankCollector,
        FormatReceiver $formatNumber,
        MessageBuilder $builder,
        DummyReceiptHelper $dummyReceipt
    ) {
        parent::__construct($gatewayResolver, $templateMessageService, $dispatcher, $bankCollector, $formatNumber, $builder, $dummyReceipt);
    }

    public function send(int $eventId, string $traceId): void
    {
        $timer = new ExecutionTimer();

        try {
            $eventModel = MessageEvent::find($eventId);
            if (!$eventModel) {
                throw new RuntimeException("MessageEvent with ID {$eventId} not found.");
            }

            // 1. Resolve Context Gateway
            $featureAlias = $eventModel->message_type ?: 'update_rekening_juni_2026';
            $context = $this->resolveGatewayContext($featureAlias, $traceId, $timer);
            if (!$context) return;

            $gateway = $context['gateway'];
            $account = $context['account'];

            $formattedReceiver = $this->formatNumber->formatReceiver($eventModel->phone_number, null, false);

            // 2. Prepare Data DTO secara terpusat
            $directData = $this->prepareDirectData($eventModel, $formattedReceiver);

            if ($gateway->type === 'cloud') {
                // cloud API implementation jika ada
            } else {
                // Direct Dispatch Handler GOWA (Local Engine)
                $actionEnum = WhatsAppAction::tryFrom($eventModel->message_type) ?? WhatsAppAction::TEST_MESSAGE;

                $this->dispatcher->dispatch(
                    action: $actionEnum->value,
                    handlerFactory: fn(string $category) => new DirectMessageHandler(
                        action: $actionEnum,
                        builder: $this->builder,
                        directData: $directData,
                        category: $category
                    ),
                    traceId: $traceId,
                    eventId: $eventId,
                    deviceId: $context['deviceId']
                );
            }

            LogHelper::logInfo(null, [
                'event_id'     => $eventId,
                'message_type' => $eventModel->message_type,
                'receiver'     => $formattedReceiver,
            ], 200, '[DirectMessageNotificationService] Message dispatched successfully.', $traceId, $timer->lap());
        } catch (Throwable $e) {
            $exceptionCode = $e->getCode();
            $statusCode = (is_numeric($exceptionCode) && $exceptionCode >= 400 && $exceptionCode <= 599)
                ? (int) $exceptionCode
                : 500;

            LogHelper::logError(null, [
                'event_id'        => $eventId,
                'error_message'   => $e->getMessage(),
                'exception_class' => get_class($e),
                'file'            => $e->getFile(),
                'line'            => $e->getLine(),
            ], $statusCode, '[DirectMessageNotificationService] Failed to dispatch direct message', $traceId, $timer->lap());

            throw $e;
        }
    }

    private function prepareDirectData(MessageEvent $eventModel, string $formattedReceiver): array
    {
        $payloadString = is_array($eventModel->request_payload)
            ? json_encode($eventModel->request_payload)
            : ($eventModel->request_payload ?? '');

        return [
            'event_id'        => $eventModel->id,
            'customer_id'     => $eventModel->customer_id,
            'receiver_number' => $formattedReceiver,
            'message_type'    => $eventModel->message_type,
            'raw_payload'     => $payloadString,
            'replacements'    => is_array($eventModel->request_payload) ? $eventModel->request_payload : [],
        ];
    }
}
