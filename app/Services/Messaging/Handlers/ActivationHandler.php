<?php
// app/Services/Messaging/Handlers/ActivationHandler.php
namespace App\Services\Messaging\Handlers;

use App\Models\Customer;
use App\Models\User;
use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;
use App\Helpers\LogHelper;

class ActivationHandler implements MessageActionHandler
{
    public function __construct(
        private Customer $customer,
        private MessageBuilder $builder,
        private \Closure $getBankInfo
    ) {}

    public function action(): string { return 'activation_customer'; }

    public function validate(string $traceId): void
    {
        if (empty($this->customer->contact)) {
            LogHelper::logError(null,['customer' => $this->customer],400,"Contact kosong saat validasi ActivationHandler",$traceId);
            throw new \RuntimeException('Contact pelanggan kosong');
        }
    }

    public function build(string $traceId): array
    {
        $admin = User::find($this->customer->user_id);
        $accountBank = ($this->getBankInfo)($admin->id ?? 1);

        return [
            'template_slug' => 'activation_customer',
            'to'            => $this->customer->contact,
            'customer_id'   => $this->customer->id,
            'replacements'  => [
                '%name%'             => $this->customer->name,
                '%address%'          => $this->customer->address,
                '%email%'            => $this->customer->email ?? '-',
                '%cs_contact%'       => $admin?->contact ?? '-',
                '%customer_id%'      => $this->customer->customer_id,
                '%admin%'            => $admin?->name ?? '-',
                '%package_name%'     => $this->customer->package->name ?? '-',
                '%package_price%'    => $this->builder->currency($this->customer->package->price ?? 0),
                '%member_type%'      => $this->customer?->member->type ?? 'Non Member',
                '%account_bank%'     => $accountBank,
            ],
        ];
    }
}
