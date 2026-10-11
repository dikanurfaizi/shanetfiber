<?php
// app/Services/Messaging/TemplateEngine.php
namespace App\Services\Messaging;

use App\Helpers\LogHelper;
use App\Models\WhatsApp\WhatsAppMessage;

class TemplateEngine
{
    public function renderBySlug(string $slug, array $replacements, ?string $traceId = null): string
    {
        $raw = WhatsAppMessage::query()
            ->where('slug', $slug)
            ->where('disabled', false)
            ->where('default', true)
            ->value('reply_message');

        if (!$raw) {
            LogHelper::logWarning(null, ['slug' => $slug], 404, 'Template slug not found', $traceId);
            return 'Template tidak tersedia.';
        }

        // Pastikan kunci replacement sudah berupa "%key%" => "value"
        $search  = array_keys($replacements);
        $replace = array_values($replacements);

        // Hindari “Array to string conversion”
        array_walk($replace, function (&$v) {
            if (is_array($v)) $v = implode(", ", $v);
        });

        return str_replace($search, $replace, $raw);
    }
}
