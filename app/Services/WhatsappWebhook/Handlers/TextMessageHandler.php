<?php

namespace App\Services\WhatsappWebhook\Handlers;

use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\WhatsApp\WhatsAppMessage;
use App\Models\Customer;
use App\Services\WhatsAppWebhook\DTO\WebhookPayload;
use App\Services\WhatsApp\KeywordProcessor;
use App\Services\Whatsapp\OptOutService;
use App\Services\Messaging\MessageDispatcher;
use App\Services\Messaging\Handlers\WebhookReplyHandler;

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
        $sender = $payload->sender();
        $text   = strtolower(trim($payload->text() ?? ''));

        // 1. Resolve keyword → table whatsapp_messages
        $keyword = $this->keywordProcessor->resolveKeyword($text);

        if (!$keyword) {
            // fallback
            $keyword = WhatsAppMessage::where('slug', 'unknown_keyword')->first();
        }

        // 2. If STOP → update DB
        if ($keyword->slug === 'stop') {
            $this->optOut->store($sender, $traceId);
        }

        // 3. Reply sesuai template
        $action = 'reply.' . $keyword->slug;

        $this->dispatcher->dispatch(
            action: $action,
            handlerFactory: fn() => new WebhookReplyHandler(
                sender: $sender,
                keyword: $keyword,
                customerId: optional(Customer::where('contact', $format->formatReceiver($sender, null, false))
                    ->orWhere('contact', $format->formatReceiver($sender, 'display', false))->first())->id
            ),
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
