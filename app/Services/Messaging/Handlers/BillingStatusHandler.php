<?php
// app/Services/Messaging/Handlers/BillingStatusHandler.php
namespace App\Services\Messaging\Handlers;

use App\Models\Customers\Customer;
use App\Models\Billings\Billing;
use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;
use App\Helpers\LogHelper;
use App\Models\Customers\CustomerAddon;
use App\Models\Customers\CustomerSubscription;
use App\Models\User;
use Illuminate\Support\Carbon;

class BillingStatusHandler extends BaseBroadcastAwareHandler implements MessageActionHandler
{
    public function __construct(
        private CustomerSubscription $customerSubscription,
        private Billing $billing,
        private string $type, // 'paylater' | 'unpayment'
        private MessageBuilder $builder,
        private \Closure $getBankInfo,
        string $category
    ) {
        $this->setCategory($category);
        $this->setRawContact($customerSubscription->customer?->contact ?? null);
    }

    public function action(): string
    {
        return $this->type;
    }

    public function validate(string $traceId): void
    {
        $this->validateBase($traceId);
    }

    public function build(string $traceId): array
    {
        $this->customerSubscription->loadMissing(
            'customer.activeMembership.membership',
            'package'
        );
        $this->billing->loadMissing(['customer.user', 'billingSubscription']);

        $cust      = $this->customerSubscription->customer;
        $user      = $cust?->user;
        $periode   = $this->billing->billingSubscription?->billingPeriode->name ?? '-';
        $bank      = ($this->getBankInfo)($user?->id ?? 1);

        $addons = CustomerAddon::where('customer_id', $this->billing->customer_id)->where('is_active', true)->get();

        $userUpdate = User::where('id', $this->billing->updated_by)->first();

        return [
            'template_slug' => $this->type,
            'to'            => $cust->contact,
            'customer_id'   => $cust->id,
            'replacements'  => [
                '%name%'           => $cust->name,
                '%customer_id%'    => $cust->customer_id,
                '%billing_number%' => $this->billing->billing_number,
                '%package%'        => $this->customerSubscription->package->name ?? '-',
                '%periode%'        => $periode ?? '-',
                '%bill%'           => $this->builder->currency($this->billing->base_price),
                '%addons_section%' => $this->builder->addonsSection($addons),
                '%member_discount_section%' => $this->builder->memberDiscountSection(
                    optional($cust->activeMembership?->membership)->name,
                    $this->billing->discount_amount
                ),
                '%total%'          => $this->builder->currency($this->billing->total_amount),
                '%account_bank%'   => $bank,
                '%admin%'          => $user?->name ?? '-',
                '%cs_contact%'     => $user?->contact ?? '-',
                '%teller%'         => $userUpdate->name,
                '%paylater%'       => $this->billing->paylater_until ? Carbon::parse($this->billing->paylater_until)->locale('id')->translatedFormat('d F Y') : '-',
            ],
        ];
    }
}
