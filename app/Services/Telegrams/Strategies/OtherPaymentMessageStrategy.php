<?php
// app/Services/Telegrams/Strategies/OtherPaymentMessageStrategy.php
namespace App\Services\Telegrams\Strategies;

use App\Services\Telegrams\Strategies\Concerns\LoadsModels;
use App\Helpers\FormatReceiver;
use Illuminate\Support\Facades\Config;

class OtherPaymentMessageStrategy implements TelegramMessageStrategy
{
    use LoadsModels;

    public function slug(): string
    {
        return 'other_payment';
    }

    public function build(int $customerId, ?int $billingId, ?int $receiptId): array
    {
        $customer = $this->getCustomerOrFail($customerId);
        $billing  = $this->getBilling($billingId);
        $receipt  = $this->getReceipt($receiptId);

        $helper = new FormatReceiver();
        $formatContact = $helper->formatReceiver($customer->contact, null, false);
        $baseWhatsappLink = Config::get('services.whatsapp.base_url', 'https://wa.me/');

        return [
            '%billing_number%'   => $billing?->billing_number,
            '%name%'             => $customer->name,
            '%customer_id%'      => $customer->customer_id,
            '%package%'          => $customer->active_package->name,
            '%periode%'          => $billing?->billingSubscription?->periode_periode,
            '%bill%'             => $billing?->base_price,
            '%discount%'         => $billing?->discount_amount,
            '%member%'           => $customer?->member?->type ?? 'Non Member',
            '%total%'            => $billing?->total_amount,
            '%teller%'           => $billing?->teller_name,
            '%payment_time%'     => $billing?->payment_time,
            '%payment_methode%'  => $receipt?->payment_methode,
            '%customer_contact%' => $baseWhatsappLink . $formatContact
        ];
    }
}
