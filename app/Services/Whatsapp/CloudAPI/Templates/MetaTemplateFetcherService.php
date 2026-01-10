<?php

namespace App\Services\Whatsapp\CloudAPI\Templates;

use App\Helpers\LogHelper;
use App\Helpers\WhatsappGraph;
use App\Models\Whatsapp\WhatsappGatewayAccount;
use Illuminate\Support\Facades\Http;

class MetaTemplateFetcherService
{
    private array $httpOptions;

    public function __construct()
    {
        $this->httpOptions = [
            'timeout' => config('services.whatsapp.timeout', 10),
            'verify' => config('services.whatsapp.ssl_verify', ! app()->environment('local')),
        ];
    }

    public function fetchAllTemplates(WhatsappGatewayAccount $account): array
    {
        $templates = [];
        $nextUrl = WhatsappGraph::messageTemplates(
            graphUrl: $account->meta_graph_url,
            graphVersion: $account->meta_graph_version,
            wabaId: $account->meta_waba_id
        );

        while ($nextUrl) {
            $response = Http::withOptions($this->httpOptions)
                ->withToken(decrypt($account->meta_user_access_token))
                ->get($nextUrl);

            LogHelper::logInfo(null, [
                'status' => $response->status(),
                'body' => $response->json(),
                'raw' => $response->body(),
            ], 200, "Response from Meta API");

            if (!$response->successful()) {
                break;
            }

            $json = $response->json();

            $templates = array_merge(
                $templates,
                $json['data'] ?? []
            );

            $nextUrl = $json['paging']['next'] ?? null;
        }

        return $templates;
    }

    public function fetchTemplateByName(
        WhatsappGatewayAccount $account,
        string $name
    ): ?array {

        $url = WhatsappGraph::templateByName(
            graphUrl: $account->meta_graph_url,
            graphVersion: $account->meta_graph_version,
            wabaId: $account->meta_waba_id,
            templateName: $name
        );

        $response = Http::withOptions($this->httpOptions)
            ->withToken(decrypt($account->meta_user_access_token))
            ->get($url);

        if (!$response->successful()) {
            return null;
        }

        $list = $response->json('data') ?? [];

        return collect($list)->first(
            fn($t) => $t['name'] === $name
        );
    }

    public function fetchTemplateById(
        WhatsappGatewayAccount $account,
        string $metaTemplateId
    ): ?array {

        $url = WhatsappGraph::templateById(
            graphUrl: $account->meta_graph_url,
            graphVersion: $account->meta_graph_version,
            templateId: $metaTemplateId
        );

        $response = Http::withOptions($this->httpOptions)
            ->withToken(decrypt($account->meta_user_access_token))
            ->get($url);

        if (!$response->successful()) {
            return null;
        }

        return $response->json();
    }
}
