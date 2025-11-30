<?php

namespace App\Services\Whatsapp;

use App\Helpers\LogHelper;
use App\Models\Whatsapp\WhatsappGateway;
use App\Models\Whatsapp\WhatsappGatewayAccount;
use App\Models\Whatsapp\WhatsappTemplate;

class TemplateSyncService
{
    private ?WhatsappGateway $gateway;

    private CloudAPIService $cloudApiService;

    public function __construct(CloudAPIService $cloudApiService)
    {
        $this->gateway = WhatsappGateway::where('disabled', 0)->first();
        $this->cloudApiService = $cloudApiService;
    }

    /**
     * Sync all Meta templates for the configured WABA ID.
     */
    public function syncAll(string $traceId): bool
    {
        if (! $this->gateway) {
            LogHelper::logWarning(null, [], 404, 'WhatsApp Template Sync: No active gateway found.', $traceId);

            return false;
        }

        if (! $this->gateway->meta_waba_id) {
            LogHelper::logWarning(null, [], 404, 'WhatsApp Template Sync: WABA ID missing.', $traceId);

            return false;
        }

        $accounts = WhatsappGatewayAccount::where('is_active', 1)->get();

        if ($accounts->isEmpty()) {
            LogHelper::logWarning(null, [], 404, 'WhatsApp Template Sync: No active gateway accounts (access tokens).', $traceId);

            return false;
        }

        // Try all accounts until one successfully fetch templates
        foreach ($accounts as $account) {
            if ($this->syncWabaTemplates($account, $traceId)) {
                LogHelper::logInfo(null, [
                    'phone_number_id' => $account->meta_phone_number_id,
                ], 200, 'WhatsApp Template Sync Successfully', $traceId);

                return true;
            }
        }

        LogHelper::logError(null, [], 500, 'WhatsApp Template Sync: All accounts failed to sync.', $traceId);

        return false;
    }

    /**
     * Sync template for the configured WABA ID.
     */
    private function syncWabaTemplates(WhatsappGatewayAccount $account, string $traceId): bool
    {
        $metaWabaId = $this->gateway->meta_waba_id;

        try {
            $templates = $this->cloudApiService->getAllTemplates($account, $metaWabaId, $traceId);

            foreach ($templates as $t) {
                $this->storeOrUpdateTemplate($metaWabaId, $t, $account, $traceId);
            }

            return true;

        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'account_id' => $account->id,
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                ],
            ], 500, 'WhatsApp Template Sync: Exception for account.');

            return false;
        }
    }

    /**
     * Save or update template record.
     */
    private function storeOrUpdateTemplate(string $metaWabaId, array $t, WhatsappGatewayAccount $account, string $traceId): WhatsappTemplate
    {
        $components = $t['components'] ?? [];
        $examples = $this->extractExamples($components);

        // Gunakan find first, lalu update manual (lebih aman daripada updateOrCreate)
        $template = WhatsappTemplate::where('meta_waba_id', $metaWabaId)
            ->where('meta_id', $t['id'])
            ->first();

        $data = [
            'phone_number_id' => $account->meta_phone_number_id ?? null,
            'name' => $t['name'],
            'language' => $t['language'],
            'category' => $t['category'],
            'sub_category' => $t['sub_category'] ?? null,
            'parameter_format' => $t['parameter_format'] ?? 'POSITIONAL',
            'status' => $t['status'],
            'components' => $components,
            'examples' => $examples,
            'placeholders_count' => $this->countPlaceholders($components),
            'has_header' => $this->hasType($components, 'HEADER'),
            'has_body' => $this->hasType($components, 'BODY'),
            'has_footer' => $this->hasType($components, 'FOOTER'),
            'has_buttons' => $this->hasType($components, 'BUTTONS'),
            'synced_at' => now(),
        ];

        if ($template) {
            $template->update($data);
            LogHelper::logInfo(null, [
                'template_id' => $template->id,
                'template_name' => $template->name,
                'category' => $template->category,
            ], 200, 'WhatsApp Template Sync: Updated existing template.', $traceId);

            return $template;
        }

        LogHelper::logInfo(null, [
            'template_name' => $t['name'],
            'category' => $t['category'],
        ], 201, 'WhatsApp Template Sync: Created new template.', $traceId);

        return WhatsappTemplate::create(array_merge([
            'meta_waba_id' => $metaWabaId,
            'meta_id' => $t['id'],
        ], $data));
    }

    private function extractExamples(array $components): array
    {
        return collect($components)
            ->whereNotNull('example')
            ->pluck('example')
            ->values()
            ->toArray();
    }

    private function countPlaceholders(array $components): int
    {
        preg_match_all('/{{\d+}}/', json_encode($components), $matches);

        return count($matches[0]);
    }

    private function hasType(array $components, string $type): bool
    {
        return collect($components)->contains(fn ($c) => ($c['type'] ?? null) === $type);
    }
}
