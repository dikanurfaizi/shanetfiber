<?php

namespace App\Services\WhatsApp\CloudAPI\Templates\Sync;

use App\Models\WhatsApp\Cloud\WhatsAppCloudTemplate;
use App\Models\WhatsApp\Cloud\WhatsAppCloudTemplateComponent;
use App\Models\WhatsApp\Cloud\WhatsAppCloudTemplateButton;
use App\Models\WhatsApp\WhatsAppGatewayAccount;
use Illuminate\Support\Facades\DB;
use Throwable;

class TemplateSyncService
{
    public function syncFromMeta(
        WhatsAppGatewayAccount $account,
        array $metaTemplates
    ): array {
        $synced = 0;
        $failed = 0;

        $metaNames = collect($metaTemplates)
            ->map(fn($t) => $t['name'])
            ->filter()
            ->values()
            ->all();

        foreach ($metaTemplates as $tpl) {
            try {
                DB::transaction(function () use ($account, $tpl, &$synced) {
                    $this->syncSingleTemplate($account, $tpl);
                    $synced++;
                });
            } catch (Throwable $e) {
                $failed++;
            }
        }

        // 🔥 DELETE TEMPLATE YANG TIDAK ADA DI META
        WhatsAppCloudTemplate::where('account_id', $account->id)
            ->whereNotIn('name', $metaNames)
            ->delete();

        return [
            'synced' => $synced,
            'failed' => $failed,
            'deleted' => count($metaNames),
        ];
    }

    protected function syncSingleTemplate(
        WhatsAppGatewayAccount $account,
        array $tpl
    ): void {
        $template = WhatsAppCloudTemplate::updateOrCreate(
            [
                'account_id' => $account->id,
                'name' => $tpl['name'],
                'language' => $tpl['language'] ?? 'id',
            ],
            [
                'waba_template_id' => $tpl['id'] ?? null,
                'category' => $tpl['category'] ?? null,
                'sub_category' => $tpl['sub_category'] ?? null,
                'status' => $tpl['status'] ?? null,
                'parameter_format' => $tpl['parameter_format'] ?? null,
                'raw_payload' => $tpl,
            ]
        );

        $this->syncComponents($template, $tpl['components'] ?? []);
    }

    protected function syncComponents(
        WhatsAppCloudTemplate $template,
        array $components
    ): void {
        $existingIds = [];

        foreach ($components as $pos => $component) {
            $model = WhatsAppCloudTemplateComponent::updateOrCreate(
                [
                    'template_id' => $template->id,
                    'position' => $pos,
                ],
                [
                    'type' => $component['type'] ?? 'UNKNOWN',
                    'format' => $component['format'] ?? null,
                    'text' => $component['text'] ?? null,
                    'example' => $component['example'] ?? null,
                    'raw_component' => $component,
                ]
            );

            $existingIds[] = $model->id;

            if (($component['type'] ?? null) === 'BUTTONS') {
                $this->syncButtons($model, $component['buttons'] ?? []);
            }
        }

        WhatsAppCloudTemplateComponent::where('template_id', $template->id)
            ->whereNotIn('id', $existingIds)
            ->delete();
    }

    protected function syncButtons(
        WhatsAppCloudTemplateComponent $component,
        array $buttons
    ): void {
        $existingIds = [];

        foreach ($buttons as $pos => $btn) {
            $model = WhatsAppCloudTemplateButton::updateOrCreate(
                [
                    'component_id' => $component->id,
                    'position' => $pos,
                ],
                [
                    'type' => $btn['type'] ?? 'UNKNOWN',
                    'text' => $btn['text'] ?? null,
                    'url' => $btn['url'] ?? null,
                    'phone_number' => $btn['phone_number'] ?? null,
                    'payload' => $btn['payload'] ?? null,
                    'code' => $btn['code'] ?? null,
                    'raw_button' => $btn,
                ]
            );

            $existingIds[] = $model->id;
        }

        WhatsAppCloudTemplateButton::where('component_id', $component->id)
            ->whereNotIn('id', $existingIds)
            ->delete();
    }
}
