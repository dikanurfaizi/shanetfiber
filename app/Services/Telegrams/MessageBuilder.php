<?php

namespace App\Services\Telegrams;

use App\Models\Telegram\TelegramMessage;
use App\Helpers\Markdown;

class MessageBuilder
{
    public function build(string $slug, array $replaceValues, string $traceId): ?string
    {
        $template = TelegramMessage::where('slug', $slug)
            ->where('disabled', false)
            ->where('default', true)
            ->value('reply_message');

        if (!$template) {
            return null;
        }

        $message = str_replace(
            array_keys($replaceValues),
            array_values($replaceValues),
            $template
        );

        return Markdown::convertMarkdownToHtml($message);
    }
}
