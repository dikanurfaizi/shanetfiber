<?php

namespace App\Domain\Decision;

class DecisionResult
{
    public const STRATEGY_NONE = 'none';
    public const STRATEGY_UPDATE_CURRENT = 'update_current';
    public const STRATEGY_SUPPLEMENTAL = 'supplemental';
    public const STRATEGY_DEFER = 'defer';

    public const PERIOD_CURRENT = 'current';
    public const PERIOD_NEXT = 'next';

    public const PRORATE_ACCUMULATIVE = 'accumulative';
    public const PRORATE_ADJUSTMENT   = 'adjustment';

    public function __construct(
        public bool $allowed = true,
        public ?string $blockReason = null,

        public ?string $effectivePeriod = null,
        public ?string $billingStrategy = null,

        public ?string $prorateMode = null,
        public bool $requireSubscriptionChangeRecord = false,
        public bool $shouldApplyPackageEntitlement = false,

        public array $notes = []
    ) {}
}
