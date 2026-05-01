<?php

namespace App\Services\Whatsapp\Meta;

use App\Models\Whatsapp\WhatsappConversation;
use App\Models\Whatsapp\WhatsappWebhookMessage;
use App\Models\Whatsapp\Cloud\WhatsappCloudTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HandleOutgoingWhatsappMessage
{
    public function handle(
        ?int $adminId,
        string $customerNumber,
        string $businessNumber,
        string $waMessageId,
        array $payload
    ): void {

        DB::transaction(function () use (
            $adminId,
            $customerNumber,
            $businessNumber,
            $waMessageId,
            $payload
        ) {
            $conversation = $this->resolveConversation($customerNumber);
            $conversation->refresh();

            // 🔐 LOCK VALIDATION
            if ($conversation->locked_by_admin_id && $conversation->locked_by_admin_id !== $adminId) {
                throw new \Exception('Conversation locked by another admin');
            }

            if (!$conversation->locked_by_admin_id) {
                $conversation->update([
                    'locked_by_admin_id' => $adminId,
                    'locked_at' => now(),
                ]);
            }

            // === Idempotency ===
            $existing = WhatsappWebhookMessage::where('wa_message_id', $waMessageId)
                ->lockForUpdate()
                ->first();

            if ($existing) return;

            // --- LOGIKA BARU: Tentukan Text Body ---
            $type = data_get($payload, 'type', 'text');
            $textBody = '';

            if ($type === 'template') {
                $textBody = $this->formatTemplateMessage($payload);
            } else {
                $textBody = data_get($payload, 'text.body');
            }
            // ---------------------------------------

            // Create outgoing message
            WhatsappWebhookMessage::create([
                'conversation_id' => $conversation->id,
                'wa_message_id'   => $waMessageId,
                'trace_id'        => Str::uuid(),
                'direction'       => 'outgoing',
                'from_number'     => $businessNumber,
                'to_number'       => $customerNumber,
                'type'            => $type,
                'text_body'       => $textBody, // Menggunakan hasil format
                'timestamp_wa'    => now(),
                'sent_at'         => now(),
                'last_status'     => 'sent',
                'payload'         => $payload,
            ]);

            $conversation->update([
                'last_message_at' => now(),
                'has_unread'      => false,
                'has_admin_reply' => true,
            ]);
        });
    }

    /**
     * Mengambil template dari DB dan mengganti placeholder dengan value dari payload
     */
    private function formatTemplateMessage(array $payload): string
    {
        $templateName = data_get($payload, 'template.name');
        $componentsPayload = data_get($payload, 'template.components', []);

        // 1. Ambil template dari DB
        $dbTemplate = WhatsappCloudTemplate::where('name', $templateName)->first();

        if (!$dbTemplate) {
            return "Template: $templateName (Detail tidak ditemukan)";
        }

        $finalText = "";
        $dbComponents = collect($dbTemplate->components);

        // 2. Proses HEADER (Bisa statis atau dinamis {{1}})
        $headerConfig = $dbComponents->firstWhere('type', 'HEADER');
        if ($headerConfig) {
            $headerText = data_get($headerConfig, 'text', '');
            $headerParams = collect($componentsPayload)->firstWhere('type', 'header')['parameters'] ?? [];

            $processedHeader = $this->replacePlaceholders($headerText, $headerParams);
            if (!empty($processedHeader)) {
                $finalText .= "*" . $processedHeader . "*\n\n";
            }
        }

        // 3. Proses BODY (Selalu ada, biasanya dinamis)
        $bodyConfig = $dbComponents->firstWhere('type', 'BODY');
        if ($bodyConfig) {
            $bodyText = data_get($bodyConfig, 'text', '');
            $bodyParams = collect($componentsPayload)->firstWhere('type', 'body')['parameters'] ?? [];

            $finalText .= $this->replacePlaceholders($bodyText, $bodyParams);
        }

        // 4. Proses FOOTER (Biasanya statis)
        $footerConfig = $dbComponents->firstWhere('type', 'FOOTER');
        if ($footerConfig) {
            $footerText = data_get($footerConfig, 'text', '');
            if (!empty($footerText)) {
                $finalText .= "\n\n_" . $footerText . "_";
            }
        }

        // 5. Proses BUTTONS (Opsional, untuk info di UI)
        $buttonConfig = $dbComponents->firstWhere('type', 'BUTTONS');
        if ($buttonConfig && isset($buttonConfig['buttons'])) {
            $btnLabels = collect($buttonConfig['buttons'])->pluck('text')->implode(' | ');
            $finalText .= "\n\n🔘 [" . $btnLabels . "]";
        }

        return $finalText;
    }

    /**
     * Helper untuk replace {{n}} dengan parameter yang sesuai
     */
    private function replacePlaceholders(string $content, array $parameters): string
    {
        foreach ($parameters as $index => $param) {
            $placeholder = '{{' . ($index + 1) . '}}';
            $value = data_get($param, 'text', '');
            $content = str_replace($placeholder, $value, $content);
        }
        return $content;
    }

    private function resolveConversation(string $customerNumber): WhatsappConversation
    {
        return WhatsappConversation::firstOrCreate(
            ['customer_number' => $customerNumber, 'status' => 'open'],
            ['channel' => 'whatsapp', 'last_message_at' => now()]
        );
    }
}
