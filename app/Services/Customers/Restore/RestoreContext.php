<?php

namespace App\Services\Customers\Restore;

class RestoreContext
{
    public string $mode;

    public ?int $current_package_id = null;
    public ?int $current_duration_id = null;
    public ?string $current_duration_name = null;
    public ?int $current_membership_id = null;

    public string $billing_option = 'monthly';

    public $packages;
    public $members;
    public $addons;

    public $availablePeriodes;
    public $activeCustomerAddons;

    public ?array $discountEstimate = null;

    /**
     * UX flags
     */
    public bool $requirePackageSelection = false;
    public bool $requireDurationSelection = false;
}
