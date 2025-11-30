<?php

namespace App\Services\Whatsapp;

use App\Helpers\LogHelper;
use App\Helpers\WhatsappGraph;
use App\Models\Whatsapp\WhatsappGateway;
use App\Models\Whatsapp\WhatsappGatewayAccount;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Throwable;

class CloudAPIService
{
    private array $httpOptions;

    public function __construct()
    {
        $this->httpOptions = [
            'timeout' => config('services.whatsapp.timeout', 10),
            'verify' => config('services.whatsapp.ssl_verify', ! app()->environment('local')),
        ];
    }

    /**
     * Fetch all WhatsApp message templates for the given WABA ID using the provided account.
     */
    public function getAllTemplates(WhatsappGatewayAccount $account, string $metaWabaId, string $traceId): ?array
    {
        $url = WhatsappGraph::messageTemplates(
            graphUrl: $account->meta_graph_url,
            graphVersion: $account->meta_graph_version,
            wabaId: $metaWabaId,
        );

        $response = Http::withOptions($this->httpOptions)
            ->withToken($account->meta_user_access_token)
            ->get($url);

        if (! $response->successful()) {
            LogHelper::logWarning(null, [
                'account_id' => $account->id,
                'status' => $response->status(),
                'response' => $response->body(),
            ], 400, 'WhatsApp Cloud API: Failed to fetch templates.', $traceId);

            return null;
        }

        LogHelper::logInfo(null, [
            'account_id' => $account->id,
            'status' => $response->status(),
        ], 200, 'WhatsApp Cloud API: Fetched templates successfully.', $traceId);

        return $response->json('data') ?? [];
    }

    /**
     * Ambil template by name dari Meta, dengan sedikit sanity-check.
     */
    private function fetchTemplateByName(
        WhatsappGatewayAccount $account,
        string $metaWabaId,
        string $templateName,
        string $traceId
    ): ?array {
        $url = WhatsappGraph::templateByName(
            graphUrl: $account->meta_graph_url,
            graphVersion: $account->meta_graph_version,
            wabaId: $metaWabaId,
            templateName: $templateName
        );

        $response = Http::withOptions($this->httpOptions)
            ->withToken($account->meta_user_access_token)
            ->get($url);

        if (! $response->successful()) {
            LogHelper::logWarning(null, [
                'account_id' => $account->id,
                'status' => $response->status(),
                'response' => $response->json(),
                'template' => $templateName,
            ], 400, 'WhatsApp Cloud API: Failed to fetch template by name.', $traceId);

            return null;
        }

        LogHelper::logInfo(null, [
            'account_id' => $account->id,
            'status' => $response->status(),
            'template' => $templateName,
        ], 200, 'WhatsApp Cloud API: Fetched template by name successfully.', $traceId);

        $list = $response->json('data') ?? [];

        if (empty($list)) {
            LogHelper::logWarning(null, [
                'account_id' => $account->id,
                'template' => $templateName,
            ], 404, 'WhatsApp Cloud API: Template not found.', $traceId);

            return null;
        }

        // Ambil template pertama yang nama-nya exact match.
        // Kalau mau lebih strict bisa filter status == APPROVED.
        $template = collect($list)->first(
            fn($t) => isset($t['name']) && $t['name'] === $templateName
        );

        if (! $template) {
            LogHelper::logWarning(null, [
                'account_id' => $account->id,
                'template' => $templateName,
            ], 404, 'WhatsApp Cloud API: Template name not matched in response.', $traceId);

            return null;
        }

        return $template;
    }

    /**
     * Fetch a WhatsApp message template by its ID.
     */
    public function fetchTemplateById(WhatsappGatewayAccount $account, string $templateId, string $traceId): ?array
    {
        $url = WhatsappGraph::templateById(
            graphUrl: $account->meta_graph_url,
            graphVersion: $account->meta_graph_version,
            templateId: $templateId
        );

        $response = Http::withOptions($this->httpOptions)
            ->withToken($account->meta_user_access_token)
            ->get($url);

        if (! $response->successful()) {
            LogHelper::logWarning(null, [
                'account_id' => $account->id,
                'status' => $response->status(),
                'response' => $response->body(),
            ], 400, 'WhatsApp Cloud API: Failed to fetch template by ID.', $traceId);

            return null;
        }

        LogHelper::logInfo(null, [
            'account_id' => $account->id,
            'status' => $response->status(),
        ], 200, 'WhatsApp Cloud API: Fetched template by ID successfully.', $traceId);

        return $response->json();
    }

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
    public function sendTemplateMessage(
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
            $template = $this->fetchTemplateByName(
                $account,
                $account->meta_waba_id,
                $templateName,
                $traceId
            );

            if (! $template) {
                // Sudah dilog di dalam fetchTemplateByName
                return null;
            }

            LogHelper::logInfo(null, [
                'account_id' => $account->id,
                'template' => $templateName,
                'to' => $to,
            ], 200, 'WhatsApp Cloud API: Template metadata fetched successfully.', $traceId);

            // 2) Build payload sesuai struktur template
            $payload = $this->buildTemplatePayloadFromTemplate(
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
            $response = $this->sendMessageRequest(
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
                return null;
            }

            LogHelper::logInfo(null, [
                'account_id' => $account->id,
                'template' => $templateName,
                'to' => $to,
            ], 200, 'WhatsApp Cloud API: Template message sent successfully.', $traceId);

            return $response->json();
        } catch (Throwable $e) {
            LogHelper::logError(null, [
                'exception' => $e->getMessage(),
            ], 500, 'WhatsApp Cloud API: Unexpected error while sending template message.', $traceId);

            return null;
        }
    }

    /**
     * Build payload template WhatsApp berdasarkan metadata template + parameter yang dikirim app.
     *
     * Fokus: BODY (text) + BUTTON URL.
     */
    private function buildTemplatePayloadFromTemplate(
        array $template,
        string $to,
        array $bodyParams,
        array $buttonParams
    ): array {
        $components = $template['components'] ?? [];

        /*
     |--------------------------------------------------------------------------
     | BODY PARAMETER HANDLING
     |--------------------------------------------------------------------------
     */
        $bodyComponent = collect($components)->firstWhere('type', 'BODY');
        $bodyText = $bodyComponent['text'] ?? '';

        $expectedBodyParamCount = preg_match_all('/\{\{\d+\}\}/', $bodyText);

        if ($expectedBodyParamCount !== count($bodyParams)) {
            throw new \InvalidArgumentException(sprintf(
                'Body parameter mismatch. Expected %d parameter(s), got %d.',
                $expectedBodyParamCount,
                count($bodyParams)
            ));
        }

        $bodyParameters = array_map(
            fn($value) => [
                'type' => 'text',
                'text' => (string) $value,
            ],
            $bodyParams
        );

        $waComponents = [];

        /*
     |--------------------------------------------------------------------------
     | HEADER COMPONENT (TEXT / IMAGE / DOCUMENT)
     |--------------------------------------------------------------------------
     */
        $headerComponent = collect($components)->firstWhere('type', 'HEADER');

        if ($headerComponent) {
            $format = strtoupper($headerComponent['format'] ?? '');

            // TEXT header tidak punya parameter
            if ($format === 'TEXT') {
                $waComponents[] = ['type' => 'header'];
            }

            // IMAGE header → harus dikirim parameter image
            elseif ($format === 'IMAGE') {
                // Cloud API TIDAK meminta parameter image karena Anda
                // harus mengirim image_url di sendTemplateMessage level lain.
                $waComponents[] = ['type' => 'header'];
            }

            // DOCUMENT header → sama seperti image
            elseif ($format === 'DOCUMENT') {
                $waComponents[] = ['type' => 'header'];
            }
        }

        /*
     |--------------------------------------------------------------------------
     | BODY COMPONENT
     |--------------------------------------------------------------------------
     */
        $waComponents[] = [
            'type' => 'body',
            'parameters' => $bodyParameters,
        ];

        /*
     |--------------------------------------------------------------------------
     | BUTTONS HANDLING (STATIC URL, DYNAMIC URL, QUICK_REPLY)
     |--------------------------------------------------------------------------
     */
        $buttonsComponent = collect($components)->firstWhere('type', 'BUTTONS');

        if ($buttonsComponent && !empty($buttonsComponent['buttons'])) {

            foreach ($buttonsComponent['buttons'] as $i => $buttonDef) {

                $buttonType = strtoupper($buttonDef['type'] ?? '');

                /*
             |--------------------------------------------------------------------------
             | URL BUTTON
             |--------------------------------------------------------------------------
             */
                if ($buttonType === 'URL') {

                    $url = $buttonDef['url'] ?? '';

                    // Hitung jumlah placeholder {{n}}
                    $expectedButtonParamCount = preg_match_all('/\{\{\d+\}\}/', $url);

                    // CASE 1: Dynamic URL → butuh parameter
                    if ($expectedButtonParamCount > 0) {

                        $value = $buttonParams[$i] ?? null;

                        if ($value === null) {
                            throw new \InvalidArgumentException(sprintf(
                                'Missing button parameter for dynamic URL button index %d.',
                                $i
                            ));
                        }

                        $waComponents[] = [
                            'type' => 'button',
                            'sub_type' => 'url',
                            'index' => (string)$i,
                            'parameters' => [
                                [
                                    'type' => 'text',
                                    'text' => (string)$value
                                ]
                            ]
                        ];
                    }

                    // CASE 2: Static URL → no parameters
                    else {
                        $waComponents[] = [
                            'type' => 'button',
                            'sub_type' => 'url',
                            'index' => (string)$i,
                        ];
                    }
                }

                /*
             |--------------------------------------------------------------------------
             | QUICK_REPLY BUTTON
             |--------------------------------------------------------------------------
             */ elseif ($buttonType === 'QUICK_REPLY') {
                    $waComponents[] = [
                        'type' => 'button',
                        'sub_type' => 'quick_reply',
                        'index' => (string)$i,
                    ];
                }
            }
        }

        /*
     |--------------------------------------------------------------------------
     | FINAL PAYLOAD
     |--------------------------------------------------------------------------
     */
        return [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $template['name'],
                'language' => [
                    'code' => $template['language'] ?? 'id',
                ],
                'components' => $waComponents,
            ],
        ];
    }

    /**
     * Kirim request ke endpoint /messages.
     */
    private function sendMessageRequest(
        WhatsappGatewayAccount $account,
        string $phoneNumberId,
        array $payload,
        string $traceId
    ): ?Response {
        $url = WhatsappGraph::sendMessage(
            graphUrl: $account->meta_graph_url,
            graphVersion: $account->meta_graph_version,
            phoneNumberId: $phoneNumberId
        );

        $response = Http::withOptions($this->httpOptions)
            ->withToken($account->meta_user_access_token)
            ->post($url, $payload);

        if (! $response->successful()) {
            LogHelper::logWarning(null, [
                'account_id' => $account->id,
                'status' => $response->status(),
                'response' => $response->json(),
                'payload' => $payload,
            ], 400, 'WhatsApp Cloud API: Failed to send message.', $traceId);

            return null;
        }

        LogHelper::logInfo(null, [
            'account_id' => $account->id,
            'status' => $response->status(),
        ], 200, 'WhatsApp Cloud API: Message sent successfully.', $traceId);

        return $response;
    }
}
