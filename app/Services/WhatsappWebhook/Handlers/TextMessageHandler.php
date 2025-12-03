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
use App\Services\Messaging\Handlers\WebhookReplyHandler;
use App\Services\Messaging\Handlers\BillingInfoHandler;
use App\Services\Messaging\Handlers\PackageInfoHandler;
use App\Services\Messaging\MessageBuilder;
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

        // 1. Resolve keyword → table whatsapp_messages
        $keyword = $this->keywordProcessor->resolveKeyword($text);

        if (!$keyword) {
            // fallback
            $keyword = WhatsappMessage::where('slug', 'unknown_keyword')->first();
        }

        // 2. If STOP → update DB
        if ($keyword->slug === 'stop') {
            $this->optOut->store($sender, $traceId);
        }

        // 3. Reply sesuai template
        $action = $keyword->slug;

        $this->dispatcher->dispatch(
            action: $action,
            handlerFactory: function (string $category) use ($keyword, $sender, $format) {

                $customer = Customer::where('contact', $format->formatReceiver($sender, null, false))
                    ->orWhere('contact', $format->formatReceiver($sender, 'display', false))
                    ->first();

                // Pilih handler berdasarkan slug keyword
                return match ($keyword->slug) {

                    // === INFO TAGIHAN (billing BL/PL, bisa no_bill)
                    'info_tagihan' => new BillingInfoHandler(
                        customer: $customer,
                        builder: app(MessageBuilder::class),
                        getBankInfo: fn($userId) => app(BankInfoCollector::class)->getBankInfo($userId),
                        category: $category
                    ),

                    // === INFO PAKET (bisa no_paket)
                    'info_paket' => new PackageInfoHandler(
                        customer: $customer,
                        builder: app(MessageBuilder::class),
                        category: $category
                    ),

                    // === handler default
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

        // log
        LogHelper::logInfo(null, [
            'sender' => $sender,
            'keyword' => $keyword->slug,
        ], 200, '[Webhook] Text processed', $traceId);
    }
}
