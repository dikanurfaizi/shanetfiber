<?php

namespace App\Services\Whatsapp\CloudAPI\Payloads\Components;

use App\Helpers\LogHelper;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\TemplateContext;
use RuntimeException;

class ComponentFactory
{
    public function __construct(
        private BodyComponentBuilder $body,
        private HeaderComponentBuilder $header,
        private ButtonComponentBuilder $button
    ) {}

    public function build($component, TemplateContext $context): ?array
    {
        LogHelper::logInfo(null, [
            'component_id' => $component->id,
            'type' => $component->type,
            'position' => $component->position,
            'example_raw' => $component->example,
        ], 200, '[WA-FACTORY] Resolve component');

        $result = match ($component->type) {
            'BODY'    => $this->body->build($component, $context->bodyValues),
            'HEADER'  => $this->header->build($component, $context->headerValues),
            'BUTTONS' => $this->buildButtons($component, $context->buttonValues),
            'FOOTER'  => null,
            default   => throw new RuntimeException("Unsupported component {$component->type}")
        };

        LogHelper::logInfo(null, [
            'component_id' => $component->id,
            'result' => $result,
        ], 200, '[WA-FACTORY] Component built');

        return $result;
    }

    private function buildButtons($component, $values)
    {
        LogHelper::logInfo(null, [
            'component_id' => $component->id,
            'buttons_count' => $component->buttons()->count(),
        ], 200, '[WA-FACTORY] Build buttons');

        $buttons = [];

        foreach ($component->buttons()->orderBy('position')->get() as $btn) {

            LogHelper::logInfo(null, [
                'button_id' => $btn->id,
                'type' => $btn->type,
                'position' => $btn->position,
                'example_raw' => $btn->example,
            ], 200, '[WA-FACTORY] Button before build');

            $built = $this->button->build($btn, $values);

            LogHelper::logInfo(null, [
                'button_id' => $btn->id,
                'built_result' => $built,
            ], 200, '[WA-FACTORY] Button built');

            if ($built !== null) {
                $buttons[] = $built;
            }
        }

        return empty($buttons) ? null : $buttons;
    }
}
