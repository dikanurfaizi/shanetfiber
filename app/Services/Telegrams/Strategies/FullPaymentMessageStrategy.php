<?php
// app/Services/Telegrams/Strategies/FullPaymentMessageStrategy.php
namespace App\Services\Telegrams\Strategies;

use App\Services\Telegrams\Strategies\Concerns\LoadsModels;
use App\Helpers\FormatReceiver;
use Illuminate\Support\Facades\Config;

class FullPaymentMessageStrategy implements TelegramMessageStrategy
{
    use LoadsModels;

    public function __construct(private string $actionSlug) {}

    public function slug(): string
    {
        // gunakan slug sesuai action yang datang: 'cash' atau 'transfer'
        return $this->actionSlug;
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
            '%periode%'          => $billing?->billingSubscription?->billingPeriode->name,
            '%bill%'             => $billing?->base_price,
            '%discount%'         => $billing?->discount_amount,
            '%member%'           => $customer?->customerMembership?->membership?->name ?? 'Non Member',
            '%total%'            => $billing?->total_amount,
            '%teller%'           => $billing?->billingPayment->paidByUser->name,
            '%payment_time%'     => $billing?->billingPayment->payment_time,
            '%payment_methode%'  => $receipt?->billingPayment->payment_method,
            '%customer_contact%' => $baseWhatsappLink . $formatContact
        ];
    }
}
