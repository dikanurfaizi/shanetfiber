<?php

namespace App\Services\Whatsapp\CloudAPI\Single;

use App\Helpers\FormatReceiver;
use App\Models\Whatsapp\WhatsappGatewayAccount;
use App\Services\Whatsapp\CloudAPI\Messages\TemplateMessageService;

class TestMessage
{
    public function __construct(private TemplateMessageService $templateMessageService) {}

    public function test(WhatsappGatewayAccount $account, string $contact)
    {
        $formatter = new FormatReceiver;

        $recipient = $formatter->formatReceiver(
            $contact,
            null,
            false
        );

        $this->templateMessageService->sendTestMessage([
            'recipient' => $recipient,
            'account' => $account
        ]);

        return back()->with('success', 'Pesan uji berhasil dikirim.');
    }
}
