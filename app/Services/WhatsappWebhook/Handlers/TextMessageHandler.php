<?php

namespace App\Services\WhatsappWebhook\Handlers;

use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Whatsapp\WhatsappMessage;
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

        // Determine customer
        $customer = Customer::where('contact', $format->formatReceiver($sender, null, false))
            ->orWhere('contact', $format->formatReceiver($sender, 'display', false))
            ->first();

        // Resolve keyword
        $keyword = $this->keywordProcessor->resolveKeyword($text, $customer);

        if (!$keyword) {
            $keyword = WhatsappMessage::where('slug', 'unknown_keyword')->first();
        }

        // STOP → save opt-out
        if ($keyword->slug === 'stop') {
            $this->optOut->store($sender, $traceId);
        }

        // Slug yang memerlukan customer
        $requiresCustomer = in_array($keyword->slug, [
            'info_tagihan',
            'info_paket',      // untuk pelanggan → paket aktif
            'lapor_gangguan',
            'hubungi_admin'
        ]);

        // Jika customer wajib tapi tidak ditemukan → gunakan UnknownCustomerHandler
        if (($requiresCustomer || !$requiresCustomer) && !$customer) {
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

            return;
        }

        // Dispatch normal handler
        $this->dispatcher->dispatch(
            action: $keyword->slug,
            handlerFactory: function (string $category) use ($keyword, $sender, $customer) {

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
            },
            queue: 'notification',
            traceId: $traceId,
            delay: now()->addSeconds(1)
        );

        LogHelper::logInfo(null, [
            'sender'  => $sender,
            'keyword' => $keyword->slug,
        ], 200, '[Webhook] Text processed', $traceId);
    }
}
