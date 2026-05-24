<?php

namespace App\Services\Whatsapp\CloudAPI\Messages;

use App\Helpers\LogHelper;
use App\Services\Whatsapp\CloudAPI\Messaging\MessageDispatcher;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands\BroadcastMidMonthCommand;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands\CustomerActivationCommand;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands\InfoPayLaterCommand;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands\InfoPembayaranCommand;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands\OverdueBroadcastCommand;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands\PaymentChannelUpdateCommand;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands\TestMessageCommand;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands\ReplyMessageCommand;

class TemplateMessageService
{
    public function __construct(
        private MessageDispatcher $dispatcher
    ) {}

    public function sendCustomerActivation(array $data)
    {
        $command = new CustomerActivationCommand(
            recipient: $data['recipient'],
            customerName: $data['customer_name'],
            customerId: $data['customer_id'],
            packageName: $data['package_name'],
            packagePrice: $data['package_price'],
            memberName: $data['member_name'],
            adminName: $data['admin_name'],
            adminContact: $data['admin_contact'],
            bank: $data['bank'],
            account: $data['account']
        );

        return $this->dispatcher->dispatch($command, $data['event_id']);
    }

    public function sendInfoPembayaran(array $data)
    {
        $command = new InfoPembayaranCommand(
            recipient: $data['recipient'],
            customerId: $data['customer_id'],
            customerName: $data['customer_name'],
            period: $data['period'],
            amount: $data['amount'],
            paymentTime: $data['payment_time'],
            tellerName: $data['teller_name'],
            receiptCode: $data['receipt_code'],
            account: $data['account']
        );

        $this->dispatcher->dispatch($command, $data['event_id']);
    }

    public function sendInfoPayLater(array $data)
    {
        $command = new InfoPayLaterCommand(
            recipient: $data['recipient'],
            customerId: $data['customer_id'],
            customerName: $data['customer_name'],
            period: $data['period'],
            tellerName: $data['teller_name'],
            account: $data['account'],
            paylaterCode: $data['paylater_code']
        );

        return $this->dispatcher->dispatch($command, $data['event_id']);
    }

    public function sendBroadcastMidMonth(array $data)
    {
        $command = new BroadcastMidMonthCommand(
            recipient: $data['recipient'],
            customerId: $data['customer_id'],
            // customerName: $data['customer_name'],
            // packageName: $data['package_name'],
            accountData: $data['account_data'],
            total: $data['total_amount'],
            dueDate: $data['due_date'],
            note: $data['note'],
            templateName: $data['templateName'],
            account: $data['account']
        );

        return $this->dispatcher->dispatch($command, null);
    }

    public function sendTestMessage(array $data)
    {
        $command = new TestMessageCommand(
            recipient: $data['recipient'],
            account: $data['account']
        );

        return $this->dispatcher->dispatch($command, null);
    }

    public function sendOverdueBroadcast(array $data)
    {
        $command = new OverdueBroadcastCommand(
            recipient: $data['recipient'],
            customerId: $data['customer_id'],
            packageName: $data['package_name'],
            total: $data['total_amount'],
            lateDays: $data['late_days'],
            note: $data['note'],
            templateName: $data['templateName'],
            account: $data['account']
        );

        return $this->dispatcher->dispatch($command, null);
    }

    public function replyMessage(array $data)
    {
        $command = new ReplyMessageCommand(
            recipient: $data['recipient'],
            text: $data['text'],
            account: $data['account'],
            messageId: $data['message_id'] ?? null, // dari wa_message_id di database
            customerId: $data['customer_id'] ?? null
        );

        return $this->dispatcher->dispatch($command, null);
    }

    public function sendPaymentChannelUpdate(array $data)
    {
        $command = new PaymentChannelUpdateCommand(
            account: $data['account'],
            templateName: $data['templateName'],
            customerId: $data['customer_id'],
            customerName: $data['customer_name'],
            recipient: $data['recipient'],
            channelUpdateDate: $data['channel_update_date'],
            channel: $data['channel'],
            bankPayload: $data['bank_payload'],
            contactAdmin: $data['contact_admin'],
            contactWhatsapp: $data['contact_whatsapp']
        );

        return $this->dispatcher->dispatch($command, null);
    }
}
