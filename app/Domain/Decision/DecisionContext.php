<?php

namespace App\Domain\Decision;

use App\Models\Billings\Billing;
use App\Models\Billings\BillingPeriode;

class DecisionContext
{
    public function __construct(
        public BillingPeriode $currentPeriod,
        public ?Billing $currentBilling,

        public bool $hasOutstandingPast,
        public bool $hasActiveSubscription,

        public int|string|null $oldPackageId,
        public int|string|null $newPackageId,

        public float $oldPrice,
        public float $newPrice,

        public bool $hasAddonChange = false,
        public ?string $addonChangeType = null,

        public bool $hasMembershipChange = false,
    ) {}


    public function isUpgrade(): bool
    {
        return $this->isPackageChanged()
            && $this->newPrice > $this->oldPrice;
    }

    public function isDowngrade(): bool
    {
        return $this->isPackageChanged()
            && $this->newPrice < $this->oldPrice;
    }

    public function isSamePackage(): bool
    {
        return $this->oldPackageId === $this->newPackageId
            && $this->newPrice === $this->oldPrice;
    }

    public function isPackageChanged(): bool
    {
        return $this->oldPackageId !== $this->newPackageId;
    }

    public function isSidegrade(): bool
    {
        return $this->isPackageChanged()
            && $this->newPrice === $this->oldPrice;
    }

    public function isSettled(): bool
    {
        return $this->currentBilling?->status === 'LS';
    }

    public bool $hasAddonAddOrChange = false;
    public bool $hasAddonRemove = false;
}
