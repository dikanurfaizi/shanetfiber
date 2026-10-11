<?php

namespace App\Services\WhatsApp\CloudAPI\Single;

use App\Helpers\FormatReceiver;
use App\Models\WhatsApp\WhatsAppGatewayAccount;
use App\Services\WhatsApp\CloudAPI\Messages\TemplateMessageService;

class TestMessage
{
    public function __construct(private TemplateMessageService $templateMessageService) {}

    public function test(WhatsAppGatewayAccount $account, string $contact)
    {
        try {
            $formatter = new FormatReceiver;

            $recipient = $formatter->formatReceiver(
                $contact,
                null,
                false
            );

            // Eksekusi pengiriman dan tangkap MessageResult
            $result = $this->templateMessageService->sendTestMessage([
                'recipient' => $recipient,
                'account'   => $account,
            ]);

            $response = $result->getResponse(); // Ambil array response dari MessageResult

            if (!($response['success'] ?? false)) {
                $errorMessage = $response['message'] ?? 'Gagal mengirim pesan uji.';

                return back()->with('error', "Gagal mengirim pesan uji: {$errorMessage}");
            }

            return back()->with('success', 'Pesan uji berhasil dikirim.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengirim pesan uji: ' . $e->getMessage());
        }
    }
}
