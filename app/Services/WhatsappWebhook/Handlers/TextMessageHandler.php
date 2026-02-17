<?php

namespace App\Services\WhatsappWebhook\Handlers;

use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Whatsapp\WhatsappMessage;
use App\Models\Whatsapp\WhatsappNumber;
use App\Models\Customers\Customer;
use App\Services\WhatsappWebhook\DTO\WebhookPayload;
use App\Services\Whatsapp\KeywordProcessor;
use App\Services\Whatsapp\OptOutService;
use App\Services\Messaging\MessageDispatcher;
use App\Services\Messaging\MessageBuilder;
use App\Services\Messaging\Handlers\WebhookReplyHandler;
use App\Services\Messaging\Handlers\BillingInfoHandler;
use App\Services\Messaging\Handlers\PackageInfoHandler;
use App\Services\Messaging\Handlers\UnknownCustomerHandler;
use App\Services\Messaging\Handlers\CallAdminHandler;
use App\Services\Messaging\Collectors\BankInfoCollector;

class TextMessageHandler extends BaseHandler
{
    public function __construct(
        private KeywordProcessor $keywordProcessor,
        private OptOutService $optOut,
        private MessageDispatcher $dispatcher,
    ) {}

    public function handle(WebhookPayload $payload, string $traceId): void
    {
        $format = new FormatReceiver();

        $sender = $format->normalizeSender($payload->sender());
        $text   = strtolower(trim($payload->text() ?? ''));
        $deviceId = $payload->deviceId();

        $normalizedSender = [
            $format->formatReceiver($sender, null, false),
            $format->formatReceiver($sender, 'display', false)
        ];

        // ===== SELF MESSAGE =====
        $gateway = WhatsappNumber::whereIn('number', $normalizedSender)->first();

        if ($gateway && $this->isSelfMessage($sender, $gateway, $format)) {

            LogHelper::logInfo(null, [
                'trace_id'  => $traceId,
                'sender'    => $sender,
                'device_id' => $deviceId,
            ], 200, '[Webhook] Self message skipped', $traceId);

            return;
        }

        // ===== CUSTOMER LOOKUP =====
        $customer = Customer::whereIn('contact', $normalizedSender)->first();

        // ===== KEYWORD RESOLUTION =====
        $keyword = $this->keywordProcessor->resolveKeyword($text, $customer)
            ?? WhatsappMessage::where('slug', 'unknown_keyword')->first();

        // STOP keyword
        if ($keyword->slug === 'stop') {
            $this->optOut->store($sender, $traceId);
        }

        $needsCustomer = in_array($keyword->slug, [
            'info_tagihan',
            'info_paket',
            'lapor_gangguan',
            'hubungi_admin',
        ]);

        if (!$needsCustomer && !$customer) {

            LogHelper::logInfo(null, [
                'trace_id'  => $traceId,
                'sender'    => $sender,
                'keyword'   => $keyword->slug,
                'device_id' => $deviceId,
            ], 200, '[Webhook] Unknown customer dispatch', $traceId);

            $this->dispatchUnknownCustomer($sender, $keyword, $deviceId, $traceId);
            return;
        }

        // ===== NORMAL DISPATCH =====
        LogHelper::logInfo(null, [
            'trace_id'    => $traceId,
            'sender'      => $sender,
            'customer_id' => $customer?->id,
            'keyword'     => $keyword->slug,
            'device_id'   => $deviceId,
        ], 200, '[Webhook] Dispatching message', $traceId);

        $this->dispatcher->dispatch(
            action: $keyword->slug,
            handlerFactory: fn(string $category) =>
            $this->makeHandler($keyword, $sender, $customer, $category),
            queue: 'notification',
            traceId: $traceId,
            delay: now()->addSeconds(1),
            eventId: null,
            deviceId: $deviceId
        );
    }

    // ===============================================
    //                  HELPER METHODS
    // ===============================================

    private function isSelfMessage(string $sender, WhatsappNumber $gateway, FormatReceiver $format): bool
    {
        $variants = array_filter([
            $gateway->number,
            $format->formatReceiver($gateway->number, null, false),
            $format->formatReceiver($gateway->number, 'display', false)
        ]);

        return in_array($sender, $variants, true);
    }

    private function dispatchUnknownCustomer(string $sender, $keyword, string $deviceId, string $traceId): void
    {
        $this->dispatcher->dispatch(
            action: 'unknown_customer',
            handlerFactory: fn(string $category) =>
            new UnknownCustomerHandler(
                sender: $sender,
                whatsappMessage: $keyword,
                builder: app(MessageBuilder::class),
                category: $category
            ),
            queue: 'notification',
            traceId: $traceId,
            delay: now()->addSeconds(1),
            eventId: null,
            deviceId: $deviceId
        );
    }

    private function makeHandler($keyword, string $sender, ?Customer $customer, string $category)
    {
        return match ($keyword->slug) {

            'info_tagihan' => new BillingInfoHandler(
                customer: $customer,
                builder: app(MessageBuilder::class),
                getBankInfo: fn($userId) => app(BankInfoCollector::class)->getBankInfo($userId),
                category: $category
            ),

            'info_paket' => new PackageInfoHandler(
                customer: $customer,
                builder: app(MessageBuilder::class),
                category: $category
            ),

            'hubungi_admin' => new CallAdminHandler(
                customer: $customer,
                builder: app(MessageBuilder::class),
                category: $category
            ),

            default => new WebhookReplyHandler(
                sender: $sender,
                keyword: $keyword,
                customerId: $customer?->id,
                category: $category
            ),
        };
    }
}
