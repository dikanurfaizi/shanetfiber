<?php

namespace App\Services\Whatsapp\CloudAPI\Single;

use App\Models\Whatsapp\WhatsappGatewayAccount;
use App\Services\Whatsapp\CloudAPI\Messages\TemplateMessageService;

class ReplyMessage
{
    public function __construct(
        private TemplateMessageService $templateMessageService
    ) {}

    /**
     * Mengirim balasan teks manual melalui infrastruktur Dispatcher.
     */
    public function send(
        WhatsappGatewayAccount $account,
        string $contact,
        string $text,
        ?string $replyToMessageId = null
    ) {
        // Memanggil service existing agar melewati Dispatcher -> Builder -> DTO -> Client
        return $this->templateMessageService->replyMessage([
            'recipient'  => $contact,
            'text'       => $text,
            'account'    => $account,
            'message_id' => $replyToMessageId, // Context ID dari database
        ]);
    }
}
