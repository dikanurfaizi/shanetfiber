<?php
// app/Services/Messaging/Handlers/ActivationHandler.php
namespace App\Services\Messaging\Handlers;

use App\Models\Customers\CustomerSubscription;
use App\Models\User;
use App\Models\Membership\CustomerMembership;
use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;
use App\Helpers\LogHelper;

class ActivationHandler extends BaseBroadcastAwareHandler implements MessageActionHandler
{
    public function __construct(
        private ?int $customerMembershipId,
        private CustomerSubscription $customerSubscription,
        private MessageBuilder $builder,
        private \Closure $getBankInfo,
        string $category
    ) {
        $this->setCategory($category);
        $this->setRawContact($customerSubscription->customer->contact);
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
        $accountBank = ($this->getBankInfo)($admin->id ?? 1);

        return [
            'template_slug' => 'activation_customer',
            'to'            => $customer->contact,
            'customer_id'   => $customer->customer_id,
            'replacements'  => [
                '%name%'             => $customer->name,
                '%address%'          => $customer->address,
                '%email%'            => $customer->email ?? '-',
                '%cs_contact%'       => $admin?->contact ?? '-',
                '%customer_id%'      => $customer->customer_id,
                '%admin%'            => $admin?->name ?? '-',
                '%package_name%'     => $this->customerSubscription->package->name ?? '-',
                '%package_price%'    => $this->builder->currency($this->customerSubscription->package->price ?? 0),
                '%member_type%'      => $member,
                '%account_bank%'     => $accountBank,
            ],
        ];
    }
}
