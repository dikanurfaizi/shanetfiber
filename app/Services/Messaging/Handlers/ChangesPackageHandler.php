<?php

namespace App\Services\Messaging\Handlers;

use App\Models\Customers\Customer;
use App\Models\User;
use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;
use App\Helpers\LogHelper;

class ChangesPackageHandler extends BaseBroadcastAwareHandler implements MessageActionHandler
{
    public function __construct(
        private int $customerId,
        private ?string $oldPackageName,
        private ?int $oldPackagePrice,
        private MessageBuilder $builder,
        private \Closure $getBankInfo,
        private \Closure $formatReceiver,
        string $category
    ) {
        $this->setCategory($category);
    }

    public function action(): string
    {
        return 'changes_package';
    }

    public function validate(string $traceId): void
    {
        // 1. Load customer
        $customer = Customer::find($this->customerId);

        if (!$customer) {
            LogHelper::logError(
                null,
                ['customer_id' => $this->customerId],
                404,
                "Customer tidak ditemukan saat validate ChangesPackage",
                $traceId
            );

            throw new \RuntimeException("Customer ID {$this->customerId} tidak ditemukan");
        }

        // 2. Jika contact kosong, langsung gagalkan
        if (empty($customer->contact)) {
            LogHelper::logError(
                null,
                ['customer_id' => $this->customerId],
                400,
                "Contact kosong saat validate ChangesPackage",
                $traceId
            );

            throw new \RuntimeException('Nomor pelanggan kosong');
        }

        // 3. Set raw contact (supaya base class bisa validasi opt-out BILA BROADCAST)
        $this->setRawContact($customer->contact);

        // 4. Validasi base: format nomor, cari customer via contact, cek opt-out
        $this->validateBase($traceId);
    }

    public function build(string $traceId): array
    {
        $customer = Customer::with(['user'])->find($this->customerId);
        $admin = $customer->user ?? User::find(1);

        $admin_number = !empty($admin->contact)
            ? ($this->formatReceiver)($admin->contact)
            : '-';

        LogHelper::logInfo(null, [
            'customer_id' => $this->customerId,
            'admin_id'    => $admin->id ?? null,
        ], 200, '[ChangesPackageHandler] Customer dan admin info loaded', $traceId);

        $bank = ($this->getBankInfo)($admin->id ?? 1);

        return [
            'template_slug' => 'changes_package',
            'to'            => $customer->contact,
            'customer_id'   => $customer->id,
            'replacements'  => [
                '%name%'         => $customer->name,
                '%address%'      => $customer->address,
                '%email%'        => $customer->email ?? '-',
                '%old_package%'  => $this->oldPackageName ?? '-',
                '%old_bill%'     => $this->builder->currency($this->oldPackagePrice ?? 0),
                '%new_package%'  => $customer->active_package->name ?? '-',
                '%new_bill%'     => $this->builder->currency($customer->active_package->price ?? 0),
                '%admin%'        => $admin->name ?? '-',
                '%cs_contact%'   => $admin_number,
                '%customer_id%'  => $customer->customer_id,
                '%account_bank%' => $bank,
                '%member_type%'  => optional($customer->member)->type ?? 'Non Member',
            ],
        ];
    }
}
