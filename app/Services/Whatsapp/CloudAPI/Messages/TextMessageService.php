<?php

namespace App\Services\Whatsapp\CloudAPI\Messages;

use App\Helpers\LogHelper;
use App\Models\Whatsapp\WhatsappGatewayAccount;
use App\Services\Whatsapp\CloudAPI\Templates\GetTemplateByNameService;
use App\Services\Whatsapp\CloudAPI\Payloads\BuildService;
use App\Services\Whatsapp\CloudAPI\Messages\MessageService;
use Throwable;

class TextMessageService
{
    public function __construct(
        private GetTemplateByNameService $getTemplate,
        private BuildService $buildTemplate,
        private MessageService $messageService
    ) {}

    /**
     * Entry point utama untuk kirim pesan template single-send.
     *
     * @param  string  $templateName  ex: 'info_pembayaran'
     * @param  string  $phoneNumberId  ID WA phone (from Meta)
     * @param  string  $to  MSISDN penerima (628xxx)
     * @param  array  $bodyParams  urutan sesuai {{1}}, {{2}}, ...
     * @param  array  $buttonParams  index sesuai urutan button URL (optional)
     * @return array|null response JSON dari Meta, atau null jika gagal
     */
    public function send(
        WhatsappGatewayAccount $account,
        string $templateName,
        string $phoneNumberId,
        string $to,
        array $bodyParams,
        array $buttonParams,
        string $traceId
    ): ?array {
        try {
            LogHelper::logInfo(null, [
                'account_id' => $account->id,
                'waba_id' => $account->meta_waba_id,
                'template' => $templateName,
                'to' => $to,
            ], 200, 'WhatsApp Cloud API: Preparing to send template message.', $traceId);

            // 1) Ambil metadata template dari Meta
            $template = $this->getTemplate->fetchTemplate(
                $account,
                $account->meta_waba_id,
                $templateName,
                $traceId
            );

            if (! $template) {
                // Sudah dilog di dalam fetchTemplateByName
                return ['status' => 'failed', 'step' => 'fetch'];
            }

            LogHelper::logInfo(null, [
                'account_id' => $account->id,
                'template' => $templateName,
                'to' => $to,
            ], 200, 'WhatsApp Cloud API: Template metadata fetched successfully.', $traceId);

            // 2) Build payload sesuai struktur template
            $payload = $this->buildTemplate->build(
                $template,
                $to,
                $bodyParams,
                $buttonParams
            );

            LogHelper::logInfo(null, [
                'account_id' => $account->id,
                'template' => $templateName,
                'to' => $to,
                'payload' => $payload,
            ], 200, 'WhatsApp Cloud API: Template payload built successfully.', $traceId);

            // 3) Kirim request ke Cloud API
            $response = $this->messageService->send(
                $account,
                $phoneNumberId,
                $payload,
                $traceId
            );

            LogHelper::logInfo(null, [
                'account_id' => $account->id,
                'template' => $templateName,
                'to' => $to,
            ], 200, 'WhatsApp Cloud API: Send message request completed.', $traceId);

            if (! $response || ! $response->successful()) {
                return ['status' => 'failed', 'step' => 'send'];
            }

            LogHelper::logInfo(null, [
                'account_id' => $account->id,
                'template' => $templateName,
                'to' => $to,
            ], 200, 'WhatsApp Cloud API: Template message sent successfully.', $traceId);

            return [
                'status' => 'success',
                'meta_response' => $response->json()
            ];
        } catch (Throwable $e) {
            LogHelper::logError(null, [
                'exception' => $e->getMessage(),
            ], 500, 'WhatsApp Cloud API: Unexpected error while sending template message.', $traceId);

            return ['status' => 'failed', 'error' => $e->getMessage()];
        }
    }
}
