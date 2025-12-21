<?php

namespace App\Services\Telegrams\Strategies;

use App\Services\Telegrams\Strategies\Concerns\LoadsModels;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class PaylaterMessageStrategy implements TelegramMessageStrategy
{
    use LoadsModels;

    public function slug(): string
    {
        return 'paylater';
    }

    public function build(int $customerId, ?int $billingId, ?int $receiptId): array
    {
        $customer = $this->getCustomerOrFail($customerId);
        $billing  = $this->getBilling($billingId);

        $helper = new FormatReceiver();
        $formatContact = $helper->formatReceiver($customer->contact, null, false);
        $baseWhatsappLink = Config::get('services.whatsapp.base_url', 'https://wa.me/');

        $tellerName =
            $billing?->updatedByUser?->name
            ?? optional($customer->user)->name
            ?? 'System';

        $placeholders = [
            '%billing_number%'   => $billing?->billing_number,
            '%name%'             => $customer->name,
            '%customer_id%'      => $customer->customer_id,
            '%package%'          => $customer->active_package->name ?? null,
            '%periode%'          => $billing?->billingSubscription?->billingPeriode?->name,
            '%bill%'             => $billing?->base_price,
            '%discount%'         => $billing?->discount_amount,
            '%member%'           => $customer?->customerMembership?->membership?->name ?? 'Non Member',
            '%total%'            => 0,
            '%teller%'           => $tellerName,
            '%paylater%'         => $billing?->paylater_until,
            '%customer_contact%' => $baseWhatsappLink . $formatContact,
        ];

        // ⛳️ LOG NULL placeholders
        $nullKeys = [];
        foreach ($placeholders as $key => $value) {
            if ($value === null) {
                $nullKeys[] = $key;
            }
        }

        if (!empty($nullKeys)) {
            LogHelper::logWarning(
                null,
                [
                    'customer_id'  => $customerId,
                    'billing_id'   => $billingId,
                    'receipt_id'   => $receiptId,
                    'null_fields'  => $nullKeys,
                ],
                100,
                'Telegram PayLater — Some placeholders are NULL',
                Str::uuid()->toString()
            );
        }

        return $placeholders;
    }
}
