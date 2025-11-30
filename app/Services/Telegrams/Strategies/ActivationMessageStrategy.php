<?php
// app/Services/Telegrams/Strategies/ActivationMessageStrategy.php
namespace App\Services\Telegrams\Strategies;

use App\Services\Telegrams\Strategies\Concerns\LoadsModels;

class ActivationMessageStrategy implements TelegramMessageStrategy
{
    use LoadsModels;

    public function slug(): string
    {
        return 'activation_customer';
    }

    public function build(int $customerId, ?int $billingId, ?int $receiptId): array
    {
        $customer = $this->getCustomerOrFail($customerId);

        return [
            '%name%'          => $customer->name,
            '%address%'       => $customer->address,
            '%email%'         => $customer->email ?? '-',
            '%customer_id%'   => $customer->customer_id,
            '%paket%'         => $customer->package->name . ' ' . $customer->package->description,
            '%ppp_username%'  => $customer->username,
            '%ppp_password%'  => $customer->password_ppp,
            '%admin_name%'    => $customer->user->name,
        ];
    }
}
