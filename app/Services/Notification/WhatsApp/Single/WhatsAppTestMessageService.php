<?php

namespace App\Services\Notification\WhatsApp\Single;

use App\Enums\WhatsAppAction;
use App\Helpers\DummyReceiptHelper;
use App\Helpers\ExecutionTimer;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Services\Messaging\Collectors\AdminBankCollector;
use App\Services\Messaging\Handlers\TestMessageHandler;
use App\Services\Messaging\MessageBuilder;
use App\Services\Messaging\MessageDispatcher;
use App\Services\WhatsApp\CloudAPI\Messages\TemplateMessageService;
use App\Services\WhatsApp\WhatsAppGatewayResolver;
use Throwable;

class WhatsAppTestMessageService extends AbstractSingleNotificationService
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

    public function send(
        WhatsAppAction $action,
        string $receiverNumber,
        string $traceId
    ): void {
        $timer = new ExecutionTimer();

        try {
            // 1. Resolve Gateway Context dari Base Class
            $context = $this->resolveGatewayContext($action->value, $traceId, $timer);
            if (!$context) return;

            $gateway = $context['gateway'];
            $account = $context['account'];

            $recipient = $this->formatNumber->formatReceiver($receiverNumber, null, false);

            // 2. Prepare Data Secara Terpusat
            $testData = $this->prepareTestData($recipient, (int) $gateway->id);

            if ($gateway->type === 'cloud') {
                $data = [
                    'recipient' => $recipient,
                    'account'   => $account,
                ];

                // $this->templateMessageService->sendTestMessage($data);
            } else {
                // Direct Dispatch Handler GOWA (Local Engine)
                $this->dispatcher->dispatch(
                    action: $action->value,
                    handlerFactory: fn(string $category) => new TestMessageHandler(
                        action: $action,
                        builder: $this->builder,
                        testData: $testData,
                        category: $category
                    ),
                    traceId: $traceId,
                    eventId: null,
                    deviceId: $context['deviceId']
                );
            }

            LogHelper::logInfo(null, [
                'receiver'     => $recipient,
                'gateway_type' => $gateway->type,
                'action'       => $action->value,
            ], 200, '[WhatsAppTestMessageService] WhatsApp test message dispatched successfully.', $traceId, $timer->lap());
        } catch (Throwable $e) {
            $exceptionCode = $e->getCode();
            $statusCode = (is_numeric($exceptionCode) && $exceptionCode >= 400 && $exceptionCode <= 599)
                ? (int) $exceptionCode
                : 500;

            LogHelper::logError(null, [
                'receiver'        => $receiverNumber,
                'action'          => $action->value,
                'error_message'   => $e->getMessage(),
                'exception_class' => get_class($e),
                'file'            => $e->getFile(),
                'line'            => $e->getLine(),
            ], $statusCode, '[WhatsAppTestMessageService] Failed to send WhatsApp test message', $traceId, $timer->lap());
        }
    }

    /**
     * Menyiapkan array data terstruktur untuk kebutuhan pesan pengujian.
     */
    private function prepareTestData(string $recipient, int $gatewayId): array
    {
        return [
            'receiver_number' => $recipient,
            'gateway_id'      => $gatewayId,
            'testing_note'    => 'Pesan ini dikirim untuk pengujian koneksi gateway WhatsApp',
        ];
    }
}
