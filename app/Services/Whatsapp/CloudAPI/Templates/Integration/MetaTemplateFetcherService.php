<?php

namespace App\Services\Whatsapp\CloudAPI\Templates\Integration;

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
        $traceId   = (string) \Illuminate\Support\Str::uuid();
        $templates = [];
        $page      = 1;

        $nextUrl = WhatsappGraph::messageTemplates(
            graphUrl: $account->meta_graph_url,
            graphVersion: $account->meta_graph_version,
            wabaId: $account->meta_waba_id
        );

        LogHelper::logInfo(null, [
            'account_id'   => $account->id,
            'meta_waba_id' => $account->meta_waba_id,
        ], 100, "[Meta-Template] Starting fetch all templates", $traceId);

        while ($nextUrl) {
            $response = Http::withOptions($this->httpOptions)
                ->withToken(decrypt($account->meta_user_access_token))
                ->get($nextUrl);

            $statusCode = $response->status();
            $json       = $response->json();
            $chunkCount = count($json['data'] ?? []);

            // Log Ringkas untuk Trace di Production
            LogHelper::logInfo(null, [
                'waba_id'     => $account->meta_waba_id,
                'page'        => $page,
                'status'      => $statusCode,
                'chunk_count' => $chunkCount,
                'has_next'    => isset($json['paging']['next']),
            ], $statusCode, "[Meta-Template] Paging response received", $traceId);

            // Log Data Lengkap dipindah ke Debug Mode
            LogHelper::logDebug(null, [
                'url'      => $nextUrl,
                'response' => $json,
            ], $statusCode, "[Meta-Template] Debug raw paging body", $traceId);

            if (!$response->successful()) {
                LogHelper::logError(null, [
                    'waba_id'  => $account->meta_waba_id,
                    'status'   => $statusCode,
                    'response' => $response->body()
                ], $statusCode, "[Meta-Template] Failed HTTP connection to Meta API", $traceId);
                break;
            }

            $templates = array_merge(
                $templates,
                $json['data'] ?? []
            );

            $nextUrl = $json['paging']['next'] ?? null;
            $page++;
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
