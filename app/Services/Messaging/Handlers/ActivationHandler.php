<?php

namespace App\Services\Messaging\Handlers;

use App\Models\Customers\CustomerSubscription;
use App\Models\User;
use App\Models\Membership\CustomerMembership;
use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;

class ActivationHandler extends BaseBroadcastAwareHandler implements MessageActionHandler
{
    private array $extraProrateData;

    public function __construct(
        private ?int $customerMembershipId,
        private CustomerSubscription $customerSubscription,
        private MessageBuilder $builder,
        private \Closure $getBankInfo,
        string $category,
        array $extraProrateData = [] // 💡 Terima data siap pakai dari service awal
    ) {
        $this->setCategory($category);
        $this->setRawContact($customerSubscription->customer->contact);
        $this->extraProrateData = $extraProrateData;
    }

    public function action(): string
    {
        return 'activation_customer';
    }

    public function validate(string $traceId): void
    {
        $this->validateBase($traceId);
    }

    public function build(string $traceId): array
    {
        $customer = $this->customerSubscription->customer;
        $admin = User::find($customer->user_id);

        if ($this->customerMembershipId === null) {
            $member = 'Non Member';
        } else {
            $customerMembership = CustomerMembership::with('membership')
                ->find($this->customerMembershipId);

            $member = $customerMembership?->membership->name ?? 'Non Member';
        }

        // Gunakan bank terformat dari Service pendaftaran jika ada, jika kosong panggil fallback collector
        $accountBank = $this->extraProrateData['formatted_banks'] ?? ($this->getBankInfo)($admin->id ?? 1);

        // 1. Tentukan harga dasar default (dari harga paket asli)
        $fallbackPrice = $this->customerSubscription->package->price ?? 0;

        // 2. Ambil data periode langganan pertama dari collection
        $firstPeriod = $this->customerSubscription->billingSubscriptionPeriodes->first();

        if ($firstPeriod && $firstPeriod->billings) {
            // 💡 PERBAIKAN: Karena $firstPeriod->billings adalah Collection, 
            // ambil record billing pertama menggunakan ->first() sebelum membaca total_amount
            $actualBilling = $firstPeriod->billings instanceof \Illuminate\Support\Collection
                ? $firstPeriod->billings->first()
                : $firstPeriod->billings;

            if ($actualBilling) {
                $fallbackPrice = $actualBilling->total_amount ?? $fallbackPrice;
            }
        }

        return [
            'template_slug' => 'activation_customer',
            'to'            => $customer->contact,
            'customer_id'   => $customer->id,
            'replacements'  => [
                '%name%'               => $customer->name,
                '%address%'            => $customer->address,
                '%email%'              => $customer->email ?? '-',
                '%cs_contact%'         => $admin?->contact ?? '-',
                '%customer_id%'        => $customer->customer_id,
                '%admin%'              => $admin?->name ?? '-',
                '%package_name%'       => $this->customerSubscription->package->name ?? '-',
                '%package_price%'      => $this->builder->currency($this->customerSubscription->package->price ?? 0),
                '%member_type%'        => $member,
                '%account_bank%'       => $accountBank,

                // Peta Replacements
                '%calculation_method%' => $this->extraProrateData['calculation_method'] ?? 'Biaya Penuh',
                '%prorate_note%'       => $this->extraProrateData['prorate_note'] ?? '',
                '%grand_total%'        => $this->extraProrateData['grand_total'] ?? $this->builder->currency($fallbackPrice),
            ],
        ];
    }
}
