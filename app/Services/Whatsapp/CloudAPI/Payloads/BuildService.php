<?php

namespace App\Services\Whatsapp\CloudAPI\Payloads;

class BuildService
{
    /**
     * Build payload template WhatsApp berdasarkan metadata template + parameter yang dikirim app.
     *
     * Fokus: BODY (text) + BUTTON URL.
     */
    public function build(
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
}
