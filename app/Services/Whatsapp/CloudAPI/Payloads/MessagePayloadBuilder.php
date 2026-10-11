<?php

namespace App\Services\WhatsApp\CloudAPI\Payloads;

use App\Models\WhatsApp\Cloud\WhatsAppCloudTemplate;
use App\Services\WhatsApp\CloudAPI\Payloads\Components\ComponentFactory;
use App\Services\WhatsApp\CloudAPI\Payloads\DTO\TemplateMessageDTO;
use App\Services\WhatsApp\CloudAPI\Templates\Runtime\TemplateContext;
use App\Helpers\LogHelper;
use RuntimeException;
use Throwable;

class MessagePayloadBuilder
{
    public function __construct(
        private ComponentFactory $factory
    ) {}

    public function build(TemplateContext $context): TemplateMessageDTO
    {
        try {
            // Jalur 1: Pesan Reply / Freeform Text
            if ($context->templateName === 'REPLY_TEXT') {
                return $this->buildReplyPayload($context);
            }

            // Jalur 2: Pesan Template
            return $this->buildTemplatePayload($context);
        } catch (Throwable $e) {
            LogHelper::logError(null, [
                'template_name' => $context->templateName,
                'recipient'     => $context->recipient,
                'error'         => $e->getMessage(),
                'trace'         => $e->getTraceAsString()
            ], 500, '[WA-PAYLOAD] Critical failure during build');

            throw $e;
        }
    }

    /**
     * Membangun payload untuk balasan teks biasa.
     */
    private function buildReplyPayload(TemplateContext $context): TemplateMessageDTO
    {
        LogHelper::logInfo(null, [
            'recipient'  => $context->recipient,
            'has_context_id' => !empty($context->messageId),
            'text_length' => strlen($context->text ?? '')
        ], 200, '[WA-PAYLOAD] Building Reply Text payload');

        return new TemplateMessageDTO(
            recipient: $context->recipient,
            text: $context->text,
            messageId: $context->messageId
        );
    }

    /**
     * Membangun payload untuk pesan berbasis template Meta.
     */
    private function buildTemplatePayload(TemplateContext $context): TemplateMessageDTO
    {
        LogHelper::logInfo(null, [
            'template_name' => $context->templateName,
            'language'      => $context->language,
            'recipient'     => $context->recipient,
            'values'        => [
                'body'   => $context->bodyValues,
                'header' => $context->headerValues,
                'button' => $context->buttonValues,
            ]
        ], 200, '[WA-PAYLOAD] Start building Template payload');

        $template = WhatsAppCloudTemplate::where('name', $context->templateName)
            ->where('language', $context->language)
            ->first();

        if (!$template) {
            throw new RuntimeException("Template [{$context->templateName}] with language [{$context->language}] not found in database.");
        }

        $templateComponents = $template->components()->orderBy('position')->get();
        $builtComponents = [];

        foreach ($templateComponents as $component) {
            $built = $this->factory->build($component, $context);

            if ($built === null) {
                LogHelper::logInfo(null, ['component_id' => $component->id], 200, '[WA-PAYLOAD] Component factory returned null, skipping');
                continue;
            }

            // Handle khusus untuk Buttons (Meta butuh array flattened untuk button components)
            if ($this->isButtonComponent($built)) {
                foreach ($built as $button) {
                    $builtComponents[] = $button;
                }
                continue;
            }

            $builtComponents[] = $built;
        }

        LogHelper::logInfo(null, [
            'template_id' => $template->id,
            'total_built' => count($builtComponents)
        ], 200, '[WA-PAYLOAD] Template payload successfully built');

        return new TemplateMessageDTO(
            recipient: $context->recipient,
            templateName: $context->templateName,
            language: $context->language,
            components: $builtComponents
        );
    }

    /**
     * Helper untuk mendeteksi struktur button
     */
    private function isButtonComponent(mixed $built): bool
    {
        return isset($built[0]) && is_array($built[0]) &&
            isset($built[0]['type']) && $built[0]['type'] === 'button';
    }
}
