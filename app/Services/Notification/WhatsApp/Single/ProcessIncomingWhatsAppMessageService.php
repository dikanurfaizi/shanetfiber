<?php

namespace App\Services\Notification\WhatsApp\Single;

use App\Helpers\ExecutionTimer;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Customers\Customer;
use App\Models\WhatsApp\WhatsAppChatSession;
use App\Models\WhatsApp\WhatsAppIncomingMessage;
use App\Models\WhatsApp\WhatsAppMessage;
use App\Models\WhatsApp\WhatsAppNumber;
use App\Services\Messaging\Collectors\AdminBankCollector;
use App\Services\Messaging\Handlers\BillingInfoHandler;
use App\Services\Messaging\Handlers\CallAdminHandler;
use App\Services\Messaging\Handlers\PackageInfoHandler;
use App\Services\Messaging\Handlers\UnknownCustomerHandler;
use App\Services\Messaging\Handlers\WebhookReplyHandler;
use App\Services\Messaging\MessageBuilder;
use App\Services\Messaging\MessageDispatcher;
use App\Services\WhatsApp\KeywordProcessor;
use App\Services\WhatsApp\OptOutService;
use App\Services\WhatsAppWebhook\DTO\WebhookPayload;
use Carbon\Carbon;

class ProcessIncomingWhatsAppMessageService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly KeywordProcessor $keywordProcessor,
        private readonly OptOutService $optOut,
        private readonly MessageDispatcher $dispatcher,
        private readonly AdminBankCollector $bankCollector
    ) {
        //
    }

    public function process(WebhookPayload $payload, string $traceId, ExecutionTimer $timer): void
    {
        $format = new FormatReceiver();

        $sender     = $format->normalizeSender($payload->sender());
        $text       = strtolower(trim($payload->text() ?? ''));
        $deviceId   = $payload->deviceId();
        $senderName = $payload->payload['from_name'] ?? null;

        $normalizedSender = [
            $format->formatReceiver($sender, null, false),
            $format->formatReceiver($sender, 'display', false),
        ];

        LogHelper::logInfo(null, [
            'trace_id'          => $traceId,
            'sender'            => $sender,
            'sender_name'       => $senderName,
            'normalized_sender' => $normalizedSender,
            'raw_text'          => $payload->text(),
            'device_id'         => $deviceId,
            'is_from_me'        => $payload->isFromMe(),
        ], 200, '[Webhook Service] Payload initialized.', $traceId, $timer->lap());

        // ===== SELF MESSAGE =====
        $gateway = WhatsAppNumber::whereIn('number', $normalizedSender)->first();

        if ($gateway && $this->isSelfMessage($sender, $gateway, $format)) {
            LogHelper::logInfo(null, [
                'trace_id'  => $traceId,
                'sender'    => $sender,
                'device_id' => $deviceId,
            ], 200, '[Webhook Service] Self message skipped', $traceId, $timer->lap());
            return;
        }

        // ===== ADMIN REPLY DETECTION =====
        if ($payload->isFromMe()) {
            try {
                WhatsAppChatSession::updateOrCreate(
                    ['phone_number' => $sender],
                    [
                        'customer_id'            => Customer::whereIn('contact', $normalizedSender)->first()?->id,
                        'chat_mode'              => 'human',
                        'device_id'              => $deviceId,
                        'last_human_activity_at' => Carbon::now(),
                    ]
                );

                LogHelper::logInfo(null, [
                    'trace_id'    => $traceId,
                    'to_customer' => $sender,
                    'text_length' => strlen($text),
                    'device_id'   => $deviceId,
                ], 200, '[Webhook Service] Admin reply detected. Session locked to Human Mode.', $traceId, $timer->lap());
            } catch (\Throwable $e) {
                LogHelper::logError(null, [
                    'trace_id' => $traceId,
                    'sender'   => $sender,
                    'error'    => $e->getMessage(),
                    'file'     => $e->getFile() . ':' . $e->getLine(),
                ], 500, '[Webhook Service] Error updating session on admin reply', $traceId, $timer->lap());
            }

            return;
        }

        // ===== CUSTOMER LOOKUP =====
        $customer = Customer::whereIn('contact', $normalizedSender)->first();

        // ===== KEYWORD RESOLUTION =====
        $keyword = $this->keywordProcessor->resolveKeyword($text, $customer)
            ?? WhatsAppMessage::where('slug', 'unknown_keyword')->first();

        // ===== LOG INCOMING MESSAGE =====
        try {
            WhatsAppIncomingMessage::create([
                'trace_id'              => $traceId,
                'phone_number'          => $sender,
                'customer_id'           => $customer?->id,
                'sender_name'           => $senderName,
                'message_text'          => $payload->text(),
                'device_id'             => $deviceId,
                'resolved_keyword_slug' => $keyword?->slug ?? 'unknown_keyword',
            ]);
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'trace_id' => $traceId,
                'error'    => $e->getMessage(),
            ], 500, '[IncomingMessage] Failed to log message history', $traceId, $timer->lap());
        }

        // ===== MANAGE CHAT SESSION =====
        try {
            $session = WhatsAppChatSession::firstOrCreate(
                ['phone_number' => $sender],
                [
                    'customer_id' => $customer?->id,
                    'chat_mode'   => 'bot',
                    'device_id'   => $deviceId,
                ]
            );

            $session->update(['device_id' => $deviceId]);
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'trace_id' => $traceId,
                'error'    => $e->getMessage(),
            ], 500, '[Session] Critical state tracking error', $traceId, $timer->lap());
            return;
        }

        $isHumanTrigger = $keyword && in_array($keyword->slug, ['hubungi_admin', 'gangguan', 'unknown_call_admin'], true);

        if ($session->chat_mode === 'human' && !$isHumanTrigger) {
            $lastActivity = $session->last_human_activity_at;

            if ($lastActivity && Carbon::now()->diffInMinutes($lastActivity) >= 15) {
                $session->update([
                    'chat_mode'              => 'bot',
                    'last_human_activity_at' => null,
                ]);

                LogHelper::logInfo(null, [
                    'trace_id'     => $traceId,
                    'phone_number' => $sender,
                ], 200, '[Session] Passive timeout triggered. Switched back to Bot.', $traceId, $timer->lap());
            }
        }

        if ($isHumanTrigger) {
            $session->update([
                'chat_mode'              => 'human',
                'last_human_activity_at' => Carbon::now(),
            ]);
        } elseif ($session->chat_mode === 'human') {
            $validBotMenus = ['info_paket', 'info_tagihan', 'menu', 'stop'];

            if ($keyword && in_array($keyword->slug, $validBotMenus, true)) {
                $session->update([
                    'chat_mode'              => 'bot',
                    'last_human_activity_at' => null,
                ]);
            } else {
                $session->update(['last_human_activity_at' => Carbon::now()]);
                return;
            }
        }

        if ($keyword && $keyword->slug === 'stop') {
            $this->optOut->store($sender, $traceId);
        }

        $needsCustomer = $keyword && in_array($keyword->slug, [
            'info_tagihan',
            'info_paket',
            'gangguan',
            'hubungi_admin',
        ], true);

        if (!$needsCustomer && !$customer && $keyword) {
            $this->dispatchUnknownCustomer($sender, $keyword, $deviceId, $traceId);
            return;
        }

        if ($keyword) {
            $this->dispatcher->dispatch(
                action: $keyword->slug,
                handlerFactory: fn(string $category) => $this->makeHandler($keyword, $sender, $customer, $category, $traceId),
                traceId: $traceId,
                eventId: null,
                deviceId: $deviceId
            );
        }
    }

    private function isSelfMessage(string $sender, WhatsAppNumber $gateway, FormatReceiver $format): bool
    {
        $variants = array_filter([
            $gateway->number,
            $format->formatReceiver($gateway->number, null, false),
            $format->formatReceiver($gateway->number, 'display', false),
        ]);

        return in_array($sender, $variants, true);
    }

    private function dispatchUnknownCustomer(string $sender, WhatsAppMessage $keyword, string $deviceId, string $traceId): void
    {
        $this->dispatcher->dispatch(
            action: 'unknown_customer',
            handlerFactory: fn(string $category) => new UnknownCustomerHandler(
                sender: $sender,
                whatsappMessage: $keyword,
                builder: app(MessageBuilder::class),
                category: $category
            ),
            traceId: $traceId,
            eventId: null,
            deviceId: $deviceId
        );
    }

    private function makeHandler(WhatsAppMessage $keyword, string $sender, ?Customer $customer, string $category, string $traceId)
    {
        return match ($keyword->slug) {
            'info_tagihan' => new BillingInfoHandler(
                customer: $customer,
                builder: app(MessageBuilder::class),
                adminBankInfo: $this->bankCollector->getFormattedBankInfo($customer->user_id ?? 1, $traceId),
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
