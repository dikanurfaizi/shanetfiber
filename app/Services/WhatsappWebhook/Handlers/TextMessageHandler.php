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

        $normalizedSender = [
            $format->formatReceiver($sender, null, false),
            $format->formatReceiver($sender, 'display', false)
        ];

        LogHelper::logInfo(null, [
            'sender' => $sender,
        ], 200, '[Webhook] Incoming sender', $traceId);

        // ===== SELF-MESSAGE CHECK =====
        $gateway = WhatsappNumber::whereIn('number', $normalizedSender)->first();

        if ($gateway && $this->isSelfMessage($sender, $gateway, $format)) {
            LogHelper::logInfo(null, [
                'sender' => $sender,
            ], 200, '[Webhook] Self message skipped', $traceId);
            return;
        }

        // ===== CUSTOMER LOOKUP =====
        $customer = Customer::whereIn('contact', $normalizedSender)->first();

        LogHelper::logInfo(null, [
            'sender' => $sender,
            'customer_id' => $customer?->id,
        ], 200, '[Webhook] Customer lookup', $traceId);

        // ===== KEYWORD RESOLUTION =====
        $keyword = $this->keywordProcessor->resolveKeyword($text, $customer)
            ?? WhatsappMessage::where('slug', 'unknown_keyword')->first();

        LogHelper::logInfo(null, [
            'keyword' => $keyword->slug,
        ], 200, '[Webhook] Keyword resolved', $traceId);

        // STOP keyword → opt out
        if ($keyword->slug === 'stop') {
            $this->optOut->store($sender, $traceId);
        }

        // Handler yang memerlukan customer
        $needsCustomer = in_array($keyword->slug, [
            'info_tagihan',
            'info_paket',
            'lapor_gangguan',
            'hubungi_admin',
        ]);

        if (!$needsCustomer && (!$customer || empty($customer))) {

            LogHelper::logInfo(null, [
                'keyword' => $keyword->slug,
                'sender' => $sender,
            ], 200, '[Webhook] Unknown customer path', $traceId);

            $this->dispatchUnknownCustomer($sender, $keyword, $traceId);
            return;
        }

        LogHelper::logInfo(null, [
            'keyword' => $keyword->slug,
            'customer_id' => $customer?->id,
        ], 200, '[Webhook] Dispatch normal', $traceId);

        // ===== DISPATCH NORMAL HANDLER =====
        $this->dispatcher->dispatch(
            action: $keyword->slug,
            handlerFactory: fn(string $category) =>
            $this->makeHandler($keyword, $sender, $customer, $category),
            queue: 'notification',
            traceId: $traceId,
            delay: now()->addSeconds(1)
        );

        LogHelper::logInfo(null, [
            'sender' => $sender,
        ], 200, '[Webhook] Text processed', $traceId);
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

    private function dispatchUnknownCustomer(string $sender, $keyword, string $traceId): void
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
            delay: now()->addSeconds(1)
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
