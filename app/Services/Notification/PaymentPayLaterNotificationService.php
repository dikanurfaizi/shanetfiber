<?php

namespace App\Services\Notification;

use App\DataTransferObjects\TelegramJobDto;
use App\Enums\TelegramAction;
use App\Enums\WhatsappAction;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use App\Models\Customers\CustomerSubscription;
use App\Services\Whatsapp\WhatsappGatewayResolver;
use App\Services\Whatsapp\GowaService;
use App\Services\Telegrams\TelegramMessageService;
use App\Services\Whatsapp\CloudAPI\Messages\TemplateMessageService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;

class PaymentPayLaterNotificationService
{
    public function __construct(
        private TemplateMessageService $templateMessageService,
        private GowaService $gowaService,
        private TelegramMessageService $telegramMessageService,
        private WhatsappGatewayResolver $gatewayResolver,
    ) {}

    public function send(
        int $billingId,
        ?int $eventId,
        string $traceId
    ): void {
        try {
            [$gateway, $account, $number] = $this->gatewayResolver->resolveActiveGatewayAndAccount($traceId, 'paylater')
                ?? [null, null];

            if (! $gateway || ! $account || !$number) {
                return;
            }

            $billing = Billing::with(['customer', 'billingSubscription'])
                ->findOrFail($billingId);

            if (! $billing || ! $billing->customer) {
                LogHelper::logError(null, [
                    'billing_id' => $billingId,
                    'customer_id' => $billing?->customer_id,
                ], 404, 'Billing or Customer not found for payment cancellation notification', $traceId);

                return;
            }

            if (empty($billing->customer->contact)) {
                LogHelper::logWarning(null, [
                    'customer_id' => $billing->customer->id,
                ], 404, 'Customer has no contact.', $traceId);

                return;
            }

            $formatter = new FormatReceiver;

            $recipient = $formatter->formatReceiver($billing->customer->contact, null, false);

            $token = Crypt::encryptString($billing->slug);
            $token = str_replace(['+', '/', '='], ['-', '_', ''], $token);

            $data = [
                'recipient' => $recipient,
                'customer_id' => $billing->customer_id,
                'customer_name' => $billing->customer->name,
                'period' => $billing->periode->display_name,
                'teller_name' => $billing->updatedByUser->name,
                'account' => $account,
                'paylater_code' => $token,
                'event_id' => $eventId
            ];

            if ($gateway->type === 'cloud') {
                $this->templateMessageService->sendInfoPayLater($data);
            } else {
                $subscription = CustomerSubscription::where('customer_id', $billing->customer->id)
                    ->where('status', 'active')->first();

                $deviceId = $number->device_id;
                $this->gowaService->notifyPayLater(
                    $subscription,
                    $billing,
                    $eventId,
                    $deviceId,
                    WhatsAppAction::PAYLATER->value,
                    $traceId
                );
            }

            // TELEGRAM
            $telegramDto = new TelegramJobDto(
                customerId: $billing->customer->id,
                action: TelegramAction::PAYLATER_PAYMENT->value,
                traceId: $traceId,
                billingId: $billingId,
                receiptId: null,
                extra: [],
                chatId: null
            );

            $this->telegramMessageService->process($telegramDto, $traceId);

            LogHelper::logInfo(null, [
                'billing_id' => $billingId,
                'customer_id' => $billing->customer->id,
            ], 200, 'PayLater notification dispatched', $traceId);
        } catch (\Throwable $e) {
            LogHelper::logError($e, [
                'billing_id' => $billingId,
                'customer_id' => $billing->customer->id ?? null,
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            ], 500, 'Failed sending PayLater notification', $traceId);
        }
    }
}
