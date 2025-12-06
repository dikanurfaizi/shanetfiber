<?php

namespace App\Services\WhatsappWebhook\Handlers;

use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Whatsapp\WhatsappMessage;
use App\Models\Whatsapp\WhatsappNumber;
use App\Models\Customer;
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

        // ===== SELF-MESSAGE CHECK =====
        $gateway = WhatsappNumber::whereIn('number', $normalizedSender)->first();

        if ($gateway && $this->isSelfMessage($sender, $gateway, $format)) {
            LogHelper::logInfo(null, [
                'reason' => 'self_message_detected',
                'sender' => $sender,
            ], 200, '[Webhook] Skipped self message', $traceId);
            return;
        }

        // ===== CUSTOMER LOOKUP =====
        $customer = Customer::whereIn('contact', $normalizedSender)->first();

        // ===== KEYWORD RESOLUTION =====
        $keyword = $this->keywordProcessor->resolveKeyword($text, $customer)
            ?? WhatsappMessage::where('slug', 'unknown_keyword')->first();

        // STOP keyword → opt out
        if ($keyword->slug === 'stop') {
            $this->optOut->store($sender, $traceId);
        }

        // Handler yang memerlukan customer
        $needsCustomer = in_array($keyword->slug, [
            'info_tagihan',
            'info_paket',
            'lapor_gangguan',
            'hubungi_admin'
        ]);

        // Customer wajib tapi tidak ditemukan
        if ($needsCustomer && !$customer) {
            $this->dispatchUnknownCustomer($sender, $keyword, $traceId);
            return;
        }

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
            'sender'  => $sender,
            'keyword' => $keyword->slug,
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
