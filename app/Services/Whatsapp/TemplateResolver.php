<?php

namespace App\Services\Whatsapp;

use App\Models\Whatsapp\WhatsappMessage;

class TemplateResolver
{
    public function fill(WhatsappMessage $template, array $data): string
    {
        $text = $template->reply_message;

        foreach ($data as $key => $value) {
            $text = str_replace("%{$key}%", $value, $text);
        }

        return $text;
    }

    public function extractRequiredPlaceholders(string $templateText): array
    {
        preg_match_all('/%([a-zA-Z0-9_]+)%/', $templateText, $matches);
        return $matches[1] ?? [];
    }
}
