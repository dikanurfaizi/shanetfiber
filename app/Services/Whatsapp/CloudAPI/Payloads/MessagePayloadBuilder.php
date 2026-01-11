<?php

namespace App\Services\Whatsapp\CloudAPI\Payloads;

use App\Models\Whatsapp\Cloud\WhatsappCloudTemplate;
use App\Services\Whatsapp\CloudAPI\Payloads\Components\ComponentFactory;
use App\Services\Whatsapp\CloudAPI\Payloads\DTO\TemplateMessageDTO;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\TemplateContext;
use App\Helpers\LogHelper;

class MessagePayloadBuilder
{
    public function __construct(
        private ComponentFactory $factory
    ) {}

    public function build(TemplateContext $context): TemplateMessageDTO
    {
        LogHelper::logInfo(null, [
            'template_name' => $context->templateName,
            'language' => $context->language,
            'recipient' => $context->recipient,
            'values' => $context->values,
        ], 200, '[WA-PAYLOAD] Start build payload');

        $template = WhatsappCloudTemplate::where('name', $context->templateName)
            ->where('language', $context->language)
            ->firstOrFail();

        $templateComponents = $template->components()->orderBy('position')->get();

        LogHelper::logInfo(null, [
            'template_id' => $template->id,
            'components_count' => $templateComponents->count(),
        ], 200, '[WA-PAYLOAD] Template loaded');

        $components = [];

        foreach ($templateComponents as $component) {

            LogHelper::logInfo(null, [
                'component_id' => $component->id,
                'type' => $component->type,
                'position' => $component->position,
                'example_raw' => $component->example,
            ], 200, '[WA-PAYLOAD] Component before build');

            $built = $this->factory->build($component, $context->values);

            if ($built === null) {
                continue;
            }

            LogHelper::logInfo(null, [
                'component_id' => $component->id,
                'built_result' => $built,
            ], 200, '[WA-PAYLOAD] Component after build');

            if (isset($built[0]) && is_array($built[0])) {
                foreach ($built as $btn) {
                    $components[] = $btn;
                }
            } else {
                $components[] = $built;
            }
        }

        LogHelper::logInfo(null, [
            'final_components' => $components,
        ], 200, '[WA-PAYLOAD] Final components built');

        return new TemplateMessageDTO(
            recipient: $context->recipient,
            templateName: $context->templateName,
            language: $context->language,
            components: $components
        );
    }
}
