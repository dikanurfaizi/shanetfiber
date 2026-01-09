<?php
// app/Services/Messaging/Handlers/BillingStatusHandler.php
namespace App\Services\Messaging\Handlers;

use App\Models\Customers\Customer;
use App\Models\Customers\CustomerSubscription;
use App\Models\Packages\Package;
use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;

class PackageInfoHandler extends BaseBroadcastAwareHandler implements MessageActionHandler
{
    public function __construct(
        private Customer $customer,
        private MessageBuilder $builder,
        string $category
    ) {
        $this->setCategory($category);
        $this->setRawContact($customer?->contact ?? null);
    }

    public function action(): string
    {
        return 'info_paket';
    }

    public function validate(string $traceId): void
    {
        $this->validateBase($traceId);
    }

    public function build(string $traceId): array
    {
        $cust    = $this->customer;
        $custSubs = CustomerSubscription::where('customer_id', $cust->id)->where('status', 'active')->first();
        $package = Package::where('id', $custSubs->package_id)->first();
        $cs      = $cust?->user?->contact ?? '-';

        if (!$package || !$cust) {
            return [
                'template_slug' => 'no_paket',
                'to'            => $cust->contact,
                'customer_id'   => $cust->id,
                'replacements'  => [
                    '%name%' => $cust->name,
                    '%cs_contact%' => $cs
                ],
            ];
        }

        return [
            'template_slug' => 'info_paket',
            'to'            => $cust->contact,
            'customer_id'   => $cust->id,
            'replacements'  => [
                '%name%'        => $cust->name,
                '%customer_id%' => $cust->customer_id,
                '%package%'     => $package->name,
                '%price%'       => $this->builder->currency($package->price ?? 0),
                '%member%'      => optional($cust->member)->type ?? 'Non Member',
                '%discount%'    => $this->builder->currency($member->discount ?? 0),
                '%cs_contact%'  => $cs
            ],
        ];
    }
}
