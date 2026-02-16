<?php

namespace App\Services\WhatsappWebhook\Meta;

use Illuminate\Support\Facades\DB;
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

            foreach ($entry['changes'] ?? [] as $change) {

                $value = $change['value'] ?? [];

                if (empty($value)) {
                    continue;
                }

                DB::transaction(function () use ($value) {

                    // 1️⃣ Incoming Messages
                    if (!empty($value['messages'])) {
                        $this->incomingHandler->handleMessages($value);
                    }

                    // 2️⃣ Status Updates
                    if (!empty($value['statuses'])) {
                        $this->statusHandler->handleStatuses($value);
                    }
                });
            }
        }
    }
}
