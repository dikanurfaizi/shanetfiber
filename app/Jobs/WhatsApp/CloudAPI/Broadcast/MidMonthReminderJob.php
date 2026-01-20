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

    private string $contact;
    private string $customerId;
    private string $customerName;
    private string $packageName;
    private string $accountData;
    private string $totalAmount;
    private string $dueDate;
    private string $note;
    private string $templateName;
    private WhatsappGatewayAccount $account;

    public function __construct(
        WhatsappGatewayAccount $account,
        string $customerId,
        string $customerName,
        string $packageName,
        string $accountData,
        string $totalAmount,
        string $dueDate,
        string $note,
        string $templateName,
        string $contact
    ) {
        $this->account = $account;
        $this->customerId = $customerId;
        $this->customerName = $customerName;
        $this->packageName = $packageName;
        $this->accountData = $accountData;
        $this->totalAmount = $totalAmount;
        $this->dueDate = $dueDate;
        $this->contact = $contact;
        $this->note = $note;
        $this->templateName = $templateName;
    }

    public function handle(TemplateMessageService $templateMessageService): void
    {
        $templateMessageService->sendBroadcastMidMonth([
            'recipient' => $this->contact,
            'customer_id' => $this->customerId,
            'customer_name' => $this->customerName,
            'package_name' => $this->packageName,
            'account_data' => $this->accountData,
            'total_amount' => $this->totalAmount,
            'due_date' => $this->dueDate,
            'note' => $this->note,
            'templateName' => $this->templateName,
            'account' => $this->account
        ]);

        LogHelper::logInfo(null, [
            'recipient' => $this->contact,
            'customer_id' => $this->customerId,
            'customer_name' => $this->customerName,
            'package_name' => $this->packageName,
            'account_data' => $this->accountData,
            'total_amount' => $this->totalAmount,
            'due_date' => $this->dueDate,
            'note' => $this->note,
            'templateName' => $this->templateName,
            'account' => $this->account
        ], 200, "[Mid Month Reminder Job] Executed");
    }
}
