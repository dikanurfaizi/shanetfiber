<?php

namespace App\Services\Messaging\Handlers;

use App\Helpers\LogHelper;
use App\Models\Websystem;
use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;

class BroadcastMonthlyHandler implements MessageActionHandler
{
    public function __construct(
        private object $billingRow,      // row hasil join di getEligibleBillings()
        private MessageBuilder $builder,
        private \Closure $getBankInfo,   // fn(int $userId): string
        private \Closure $getBillingPackages, // fn(int $customerId): \Illuminate\Support\Collection
        private \Closure $formatPackageInfo  // fn($package): string
    ) {}

    public function action(): string
    {
        return 'create_new_bill';
    }

    public function validate(string $traceId): void
    {
        if (empty($this->billingRow->customer_contact)) {
            LogHelper::logError(null, ['billing_id' => $this->billingRow->billing_id], 400, 'No customer contact', $traceId);
            throw new \RuntimeException('Contact kosong');
        }
    }

    public function build(string $traceId): array
    {
        $web = Websystem::first();
        $adminBank = ($this->getBankInfo)($this->billingRow->admin_id ?: 1);

        $packages = ($this->getBillingPackages)($this->billingRow->customer_id);
        if ($packages->isEmpty()) {
            LogHelper::logError(null, ['customer_id' => $this->billingRow->customer_id], 404, 'Packages not found', $traceId);
            throw new \RuntimeException('Package kosong');
        }

        $packagesText = $packages->map(fn($p) => ($this->formatPackageInfo)($p))->implode("\n---\n");

        $to = $this->billingRow->customer_contact;
        $adminContact = $this->billingRow->admin_contact;

        return [
            'template_slug' => 'create_new_bill',
            'to' => $to,
            'customer_id' => $this->billingRow->customer_id,
            'replacements' => [
                '%name%'            => $this->billingRow->customer_name,
                '%address%'         => $this->billingRow->customer_address,
                '%customer_id%'     => $this->billingRow->customer_unique_id,
                '%billing_number%'  => $this->billingRow->billing_number ?? 'Nomor tidak tersedia',
                '%account_bank%'    => $adminBank,
                '%cs_contact%'      => $adminContact,
                '%total-bill%'      => 'Rp. ' . number_format($this->billingRow->total_amount, 2),
                '%packages%'        => $packagesText,
                '%company_address%' => trim(($web->address ?? '') . ', ' . ($web->city ?? '') . ' ' . ($web->postal_code ?? '')),
                '%deadline%'        => $this->guessDueDate($packages),
                '%admin%'           => $this->billingRow->admin_name
            ]
        ];
    }

    private function guessDueDate($packages): string
    {
        $due = $packages->pluck('due_date')->filter()->sort()->first();
        return $due ? now()->parse($due)->locale('id')->isoFormat('D MMMM Y') : 'Tidak ada jatuh tempo';
        // (pakai Carbon::parse jika ingin ketat)
    }
}
