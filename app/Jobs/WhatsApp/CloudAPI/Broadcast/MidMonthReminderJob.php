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
    private string $totalAmount;
    private string $dueDate;
    private WhatsappGatewayAccount $account;

    public function __construct(
        WhatsappGatewayAccount $account,
        string $customerId,
        string $customerName,
        string $packageName,
        string $totalAmount,
        string $dueDate,
        string $contact
    ) {
        $this->account = $account;
        $this->customerId = $customerId;
        $this->customerName = $customerName;
        $this->packageName = $packageName;
        $this->totalAmount = $totalAmount;
        $this->dueDate = $dueDate;
        $this->contact = $contact;
    }

    public function handle(TemplateMessageService $templateMessageService): void
    {
        $templateMessageService->sendBroadcastMidMonth([
            'recipient' => $this->contact,
            'customer_id' => $this->customerId,
            'customer_name' => $this->customerName,
            'package_name' => $this->packageName,
            'total_amount' => $this->totalAmount,
            'due_date' => $this->dueDate,
            'account' => $this->account
        ]);

        LogHelper::logInfo(null, [
            'recipient' => $this->contact,
            'recipient_Name' => $this->customerName,
            'package_name' => $this->packageName,
            'total_amount' => $this->totalAmount,
            'due_date' => $this->dueDate,
            'account' => $this->account->id
        ], 200, "[Mid Month Reminder Job] Executed");
    }
}
