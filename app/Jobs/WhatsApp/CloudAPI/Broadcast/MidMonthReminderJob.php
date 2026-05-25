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

class MidMonthReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Menggunakan PHP 8 Constructor Property Promotion untuk efisiensi kode.
     */
    public function __construct(
        private WhatsappGatewayAccount $account,
        private string $customerId,
        private string $customerName,
        private string $packageName,
        private string $accountData,
        private string $totalAmount,
        private string $dueDate,
        private string $note,
        private string $templateName,
        private string $contact,
        private array $bankPayload,       // 💡 Menangkap payload bank dari service
        private string $contactAdmin,     // 💡 Menangkap contact admin dari service
        private string $contactWhatsapp   // 💡 Menangkap link whatsapp admin dari service
    ) {}

    public function handle(TemplateMessageService $templateMessageService): void
    {
        // Eksekusi pengiriman sesungguhnya ke WA Cloud API
        $templateMessageService->sendBroadcastMidMonth([
            'recipient'        => $this->contact,
            'customer_id'      => $this->customerId,
            'customer_name'    => $this->customerName,
            'package_name'     => $this->packageName,
            'account_data'     => $this->accountData,
            'total_amount'     => $this->totalAmount,
            'due_date'         => $this->dueDate,
            'note'             => $this->note,
            'templateName'     => $this->templateName,
            'account'          => $this->account,
            'bank_payload'     => $this->bankPayload,       // 💡 Diteruskan ke service message
            'contact_admin'    => $this->contactAdmin,      // 💡 Diteruskan ke service message
            'contact_whatsapp' => $this->contactWhatsapp,   // 💡 Diteruskan ke service message
        ]);

        LogHelper::logInfo(null, [
            'recipient'        => $this->contact,
            'customer_id'      => $this->customerId,
            'customer_name'    => $this->customerName,
            'package_name'     => $this->packageName,
            'account_data'     => $this->accountData,
            'total_amount'     => $this->totalAmount,
            'due_date'         => $this->dueDate,
            'note'             => $this->note,
            'templateName'     => $this->templateName,
            'contact_admin'    => $this->contactAdmin,
            'contact_whatsapp' => $this->contactWhatsapp,
        ], 200, "[Mid Month Reminder Job] Executed and Message Sent via API");
    }
}
