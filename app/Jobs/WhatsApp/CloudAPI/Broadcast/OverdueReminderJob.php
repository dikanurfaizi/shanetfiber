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

    private string $contact;
    private string $customerId;
    private string $packageName;
    private string $totalAmount;
    private string $lateDays;
    private string $note;
    private string $templateName;
    private WhatsappGatewayAccount $account;

    public function __construct(
        WhatsappGatewayAccount $account,
        string $customerId,
        string $packageName,
        string $totalAmount,
        string $lateDays,
        string $note,
        string $templateName,
        string $contact
    ) {
        $this->account = $account;
        $this->customerId = $customerId;
        $this->packageName = $packageName;
        $this->totalAmount = $totalAmount;
        $this->lateDays = $lateDays;
        $this->contact = $contact;
        $this->note = $note;
        $this->templateName = $templateName;
    }

    public function handle(TemplateMessageService $templateMessageService): void
    {
        $templateMessageService->sendOverdueBroadcast([
            'recipient' => $this->contact,
            'customer_id' => $this->customerId,
            'package_name' => $this->packageName,
            'total_amount' => $this->totalAmount,
            'late_days' => $this->lateDays,
            'note' => $this->note,
            'templateName' => $this->templateName,
            'account' => $this->account
        ]);

        LogHelper::logInfo(null, [
            'recipient' => $this->contact,
            'customer_id' => $this->customerId,
            'package_name' => $this->packageName,
            'total_amount' => $this->totalAmount,
            'late_days' => $this->lateDays,
            'note' => $this->note,
            'templateName' => $this->templateName,
            'account' => $this->account
        ], 200, "[Mid Month Reminder Job] Executed");
    }
}
