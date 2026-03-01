<?php

namespace App\Services\WhatsappWebhook\Meta;

use App\Helpers\LogHelper;
use App\Services\Whatsapp\Meta\HandleIncomingWhatsappWebhook;
use App\Services\Whatsapp\Meta\HandleStatusUpdateWhatsappWebhook;

class MetaWebhookEventRouter
{
    public function __construct(
        private HandleIncomingWhatsappWebhook $incomingHandler,
        private HandleStatusUpdateWhatsappWebhook $statusHandler
    ) {}

    public function route(array $payload, string $traceId): void
    {
        foreach ($payload['entry'] ?? [] as $entry) {

            LogHelper::logInfo(
                null,
                ['entry' => $entry],
                200,
                "Processing WhatsApp Meta Webhook Event",
                $traceId
            );

            foreach ($entry['changes'] ?? [] as $change) {

                $value = $change['value'] ?? [];

                if (empty($value)) {
                    continue;
                }

                // 1️⃣ Incoming Messages
                if (!empty($value['messages'])) {
                    $this->incomingHandler->handleMessages($value, $traceId);
                }

                // 2️⃣ Status Updates
                if (!empty($value['statuses'])) {
                    $this->statusHandler->handleStatuses($value, $traceId);
                }
            }
        }
    }
}
