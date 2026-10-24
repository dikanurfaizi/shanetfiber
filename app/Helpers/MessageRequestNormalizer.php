<?php

namespace App\Helpers;

use App\Models\WhatsApp\Cloud\WhatsAppCloudTemplate;

class MessageRequestNormalizer
{
    /**
     * Parsing dan ekstrak isi pesan dari request payload berdasarkan channel
     */
    public static function extractMessage(string|array|null $payload, string $channel = 'go_whatsapp'): string
    {
        if (!$payload) {
            return '-';
        }

        // Jika $payload berupa string JSON, decode ke array. Jika sudah array, gunakan langsung.
        $decoded = is_array($payload) ? $payload : json_decode($payload, true);

        // Jika gagal di-decode dan merupakan string mentah/plain text
        if (!is_array($decoded)) {
            return static::parseWhatsAppFormatting((string) $payload);
        }

        return match ($channel) {
            'go_whatsapp' => static::extractGoWhatsApp($decoded),
            'telegram'    => static::extractTelegram($decoded),
            'meta_api'    => static::extractMetaWhatsApp($decoded),
            default       => static::parseWhatsAppFormatting($decoded['message'] ?? $decoded['text'] ?? json_encode($decoded))
        };
    }

    /**
     * Ekstraksi spesifik untuk Channel Go WhatsApp
     */
    protected static function extractGoWhatsApp(array $decoded): string
    {
        $message = $decoded['message'] ?? '-';
        return static::parseWhatsAppFormatting($message);
    }

    /**
     * Converter format WhatsApp (*bold*, _italic_, *_bold-italic_*) ke HTML
     */
    public static function parseWhatsAppFormatting(string $text): string
    {
        // 1. Normalisasi karakter newline tunggal & ganda
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // 2. Kombinasi Bold + Italic: *__teks__* atau _*teks*_ atau _**teks**_
        $text = preg_replace('/(\*|_)+_?\*?(.*?)\*?_?(\*|_)+/s', '<b><i>$2</i></b>', $text);
        $text = preg_replace('/\*_(.*?)_\*/s', '<b><i>$1</i></b>', $text);
        $text = preg_replace('/_\*(.*?)\*_/s', '<b><i>$1</i></b>', $text);

        // 3. Bold saja: *teks*
        $text = preg_replace('/(?<!\w)\*(.*?)\*(?!\w)/s', '<b>$1</b>', $text);

        // 4. Italic saja: _teks_
        $text = preg_replace('/(?<!\w)_(.*?)_(?!\w)/s', '<i>$1</i>', $text);

        // 5. Strikethrough/Coret (Opsional WA): ~teks~
        $text = preg_replace('/(?<!\w)~(.*?)~(?!\w)/s', '<del>$1</del>', $text);

        return $text;
    }

    /**
     * Ekstraksi spesifik untuk Channel Telegram
     */
    protected static function extractTelegram(array $decoded): string
    {
        $message = $decoded['message'] ?? $decoded['text'] ?? $decoded['caption'] ?? '-';
        return static::parseWhatsAppFormatting($message);
    }

    /**
     * Ekstraksi spesifik untuk Channel Meta WhatsApp API (Text & Template)
     */
    protected static function extractMetaWhatsApp(array $decoded): string
    {
        $type = $decoded['type'] ?? null;

        // 1. Tipe Text Biasa (REPLY_TEXT)
        if ($type === 'text') {
            $rawText = $decoded['text']['body'] ?? '-';
            return static::parseWhatsAppFormatting($rawText);
        }

        // 2. Tipe Template
        if ($type === 'template' && isset($decoded['template']['name'])) {
            $templateName = $decoded['template']['name'];
            $languageCode = $decoded['template']['language']['code'] ?? 'id';

            // Ambil data template beserta relasi components dan buttons
            $template = WhatsAppCloudTemplate::with(['components.buttons'])
                ->where('name', $templateName)
                ->where('language', $languageCode)
                ->first();

            if (!$template) {
                return "[Template: {$templateName}]";
            }

            // Kelompokkan komponen payload berdasarkan tipe
            $payloadComponents = collect($decoded['template']['components'] ?? []);

            $messageParts = [];

            // --- A. HEADER ---
            $headerComponent = $template->components->firstWhere('type', 'HEADER');
            if ($headerComponent) {
                $headerText = $headerComponent->text ?? '';
                $headerPayload = $payloadComponents->firstWhere('type', 'header');
                $headerParams = $headerPayload['parameters'] ?? [];

                if (!empty($headerParams)) {
                    foreach ($headerParams as $index => $param) {
                        $paramType = $param['type'] ?? 'text';

                        if ($paramType === 'text') {
                            $placeholder = '{{' . ($index + 1) . '}}';
                            $headerText = str_replace($placeholder, $param['text'] ?? '', $headerText);
                        } elseif (in_array($paramType, ['image', 'document', 'video'])) {
                            $mediaUrl = $param[$paramType]['link'] ?? $param[$paramType]['id'] ?? '';
                            $headerText = "[Media " . strtoupper($paramType) . "]: " . $mediaUrl;
                        }
                    }
                }

                if (!empty(trim($headerText))) {
                    $messageParts[] = $headerText;
                }
            }

            // --- B. BODY ---
            $bodyComponent = $template->components->firstWhere('type', 'BODY');
            if ($bodyComponent && $bodyComponent->text) {
                $bodyText = $bodyComponent->text;
                $bodyPayload = $payloadComponents->firstWhere('type', 'body');
                $bodyParams = $bodyPayload['parameters'] ?? [];

                foreach ($bodyParams as $index => $param) {
                    $placeholder = '{{' . ($index + 1) . '}}';
                    $value = $param['text'] ?? $param['value'] ?? '';
                    $bodyText = str_replace($placeholder, $value, $bodyText);
                }

                $messageParts[] = $bodyText;
            }

            // --- C. BUTTONS ---
            $buttonsComponent = $template->components->firstWhere('type', 'BUTTONS');
            if ($buttonsComponent && $buttonsComponent->buttons->isNotEmpty()) {
                $buttonTexts = [];

                // Kumpulkan semua komponen bertipe button dari payload
                $buttonPayloads = $payloadComponents->where('type', 'button')->values();

                foreach ($buttonsComponent->buttons as $button) {
                    $label = $button->text ?? 'Button';
                    $url = $button->url ?? '';

                    // Cari parameter tombol yang sesuai dengan index/posisi tombol
                    $matchingPayload = $buttonPayloads->firstWhere('index', $button->position)
                        ?? $buttonPayloads->get($button->position);

                    if ($matchingPayload) {
                        $subType = $matchingPayload['sub_type'] ?? '';
                        $params = $matchingPayload['parameters'] ?? [];

                        // 1. Variabel URL Dinamis
                        if ($subType === 'url' && !empty($params)) {
                            $dynamicParam = $params[0]['text'] ?? $params[0]['payload'] ?? '';
                            if ($url !== '') {
                                $url = str_replace('{{1}}', $dynamicParam, $url);
                            } else {
                                $url = $dynamicParam;
                            }
                        }

                        // 2. Quick Reply / Payload Button
                        if ($subType === 'quick_reply' && !empty($params)) {
                            $payloadValue = $params[0]['payload'] ?? $params[0]['text'] ?? '';
                            if ($payloadValue) {
                                $label .= " (Payload: {$payloadValue})";
                            }
                        }
                    }

                    if ($url !== '') {
                        $buttonTexts[] = "[\u{1F518} {$label} - {$url}]";
                    } else {
                        $buttonTexts[] = "[\u{1F518} {$label}]";
                    }
                }

                if (!empty($buttonTexts)) {
                    $messageParts[] = implode(' ', $buttonTexts);
                }
            }

            // Gabungkan teks lalu parsing formatting (*bold* & _italic_)
            $fullMessage = implode("\n\n", $messageParts);
            return static::parseWhatsAppFormatting($fullMessage);
        }

        $defaultText = $decoded['text']['body'] ?? '-';
        return static::parseWhatsAppFormatting($defaultText);
    }
}
