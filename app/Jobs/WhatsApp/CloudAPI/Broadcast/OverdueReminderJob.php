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

class OverdueReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $contact,
        private string $customerId,
        private string $packageName,
        private string $totalAmount,
        private int $lateDays,
        private string $note,
        private string $templateName,
        private WhatsappGatewayAccount $account,
        private array $bankPayload,       // 💡 Tambahan field baru
        private string $contactAdmin,     // 💡 Tambahan field baru
        private string $contactWhatsapp   // 💡 Tambahan field baru
    ) {}

    public function handle(TemplateMessageService $templateMessageService): void
    {
        // Panggil service WhatsApp untuk kirim payload gabungan ke template baru Anda
        $templateMessageService->sendOverdueBroadcast([
            'recipient'        => $this->contact,
            'customer_id'      => $this->customerId,
            'package_name'     => $this->packageName,
            'total_amount'     => $this->totalAmount,
            'late_days'        => $this->lateDays,
            'note'             => $this->note,
            'templateName'     => $this->templateName,
            'account'          => $this->account,
            'bank_payload'     => $this->bankPayload,
            'contact_admin'    => $this->contactAdmin,
            'contact_whatsapp' => $this->contactWhatsapp,
        ]);

        LogHelper::logInfo(null, [
            'recipient'        => $this->contact,
            'customer_id'      => $this->customerId,
            'package_name'     => $this->packageName,
            'total_amount'     => $this->totalAmount,
            'late_days'        => $this->lateDays,
            'note'             => $this->note,
            'templateName'     => $this->templateName,
            'contact_admin'    => $this->contactAdmin,
            'contact_whatsapp' => $this->contactWhatsapp,
        ], 200, "[Overdue Reminder Job] Executed and Message Sent via API");
    }
}
