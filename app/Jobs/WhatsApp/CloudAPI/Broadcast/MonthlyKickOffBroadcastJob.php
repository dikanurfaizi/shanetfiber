<?php

namespace App\Jobs\Whatsapp\CloudAPI\Broadcast;

use App\Helpers\LogHelper;
use App\Models\Whatsapp\WhatsappGatewayAccount;
use App\Services\Whatsapp\CloudAPI\Messages\TemplateMessageService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class MonthlyKickOffBroadcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // ===============================================================
    // PROPERTIES (PROPERLY ORDERED)
    // ===============================================================
    private WhatsappGatewayAccount $account;
    private string $templateName;
    private string $customerId;
    private string $customerName;
    private string $contact;
    private string $initialDate;
    private string $channel;
    private array $bankPayload;
    private string $contactAdmin;
    private string $contactWhatsapp;

    // ===============================================================
    // CONSTRUCTOR (MATCHING PROPERTY ORDER)
    // ===============================================================
    public function __construct(
        WhatsappGatewayAccount $account,
        string $templateName,
        string $customerId,
        string $customerName,
        string $contact,
        string $initialDate,
        string $channel,
        array $bankPayload,
        string $contactAdmin,
        string $contactWhatsapp
    ) {
        $this->account           = $account;
        $this->templateName      = $templateName;
        $this->customerId        = $customerId;
        $this->customerName      = $customerName;
        $this->contact           = $contact;
        $this->initialDate       = $initialDate;
        $this->channel           = $channel;
        $this->bankPayload       = $bankPayload;
        $this->contactAdmin      = $contactAdmin;
        $this->contactWhatsapp   = $contactWhatsapp;
    }

    // ===============================================================
    // EXECUTION HANDLE
    // ===============================================================
    public function handle(TemplateMessageService $templateMessageService): void
    {
        // Parameter di-map secara konsisten ke service (Menggunakan snake_case)
        $templateMessageService->sendMonthlyKickOffBroadcast([
            'account'             => $this->account,
            'templateName'        => $this->templateName,
            'customer_id'         => $this->customerId,
            'customer_name'       => $this->customerName,
            'recipient'           => $this->contact,
            'initial_date'        => $this->initialDate,
            'channel'             => $this->channel,
            'bank_payload'        => $this->bankPayload,
            'contact_admin'       => $this->contactAdmin,
            'contact_whatsapp'    => $this->contactWhatsapp,
        ]);

        // Struktur log dibuat identik dengan parameter pengiriman service
        LogHelper::logInfo(null, [
            'account_id'          => $this->account->id,
            'templateName'        => $this->templateName,
            'customer_id'         => $this->customerId,
            'customer_name'       => $this->customerName,
            'recipient'           => $this->contact,
            'initial_date'        => $this->initialDate,
            'channel'             => $this->channel,
            'bank_payload'        => $this->bankPayload,
            'contact_admin'       => $this->contactAdmin,
            'contact_whatsapp'    => $this->contactWhatsapp,
        ], 200, "[PaymentChannelUpdateJob] Executed");
    }
}
