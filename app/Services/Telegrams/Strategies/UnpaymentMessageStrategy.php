<?php
// app/Services/Telegrams/Strategies/FullPaymentMessageStrategy.php
namespace App\Services\Telegrams\Strategies;

use App\Services\Telegrams\Strategies\Concerns\LoadsModels;
use App\Helpers\FormatReceiver;
use Illuminate\Support\Facades\Config;

class UnpaymentMessageStrategy implements TelegramMessageStrategy
{
    use LoadsModels;

    public function slug(): string
    {
        return 'unpayment';
    }

    public function build(int $customerId, ?int $billingId, ?int $receiptId): array
    {
        $customer = $this->getCustomerOrFail($customerId);
        $billing  = $this->getBilling($billingId);

        $helper = new FormatReceiver();
        $formatContact = $helper->formatReceiver($customer->contact, null, false);
        $baseWhatsappLink = Config::get('services.whatsapp.base_url', 'https://wa.me/');

        return [
            '%billing_number%' => $billing?->billing_number,
            '%name%'           => $customer->name,
            '%customer_id%'    => $customer->customer_id,
            '%periode%'        => $billing?->billingSubscription?->billingPeriode?->name,
            '%total%'          => $billing?->total_amount,
            '%customer_contact%' => $baseWhatsappLink . $formatContact

            // ✅ receiptData akan menyatu ketika di Service
            // misal: '%receipt_number%' => $extra['receipt']['number']
        ];
    }
}
