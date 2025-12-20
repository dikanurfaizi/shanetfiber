<?php

namespace App\Services\Customers;

use App\Models\Customers\Customer;
use App\Models\Membership\CustomerMembership;

class CustomerMembershipService
{
    public function getCustomerMembershipInfo(Customer $customer): array
    {
        // Load relationship hanya jika memang ada
        $customerMembership = CustomerMembership::where('customer_id', $customer->id)
            ->where('is_active', true)->first();

        $membershipRecord = $customerMembership;

        // Fallback default as Non Member
        $result = [
            'is_member'        => false,
            'membership_name'  => 'Non Member',
            'start_date'       => null,
            'end_date'         => null,
            'discount_type'    => null,
            'discount_value'   => 0,
            'calculated_discount' => 0,
        ];

        // Jika tidak ada record membership → selesai
        if (!$membershipRecord) {
            return $result;
        }

        // Jika membership tidak active → treat as Non Member
        if (!$membershipRecord->is_active) {
            return $result;
        }

        $membership = $membershipRecord->membership;
        $package    = $customer->activePackage;

        // Jika membership tidak punya definisi di tabel membership
        if (!$membership) {
            return $result;
        }

        $result['is_member']       = true;
        $result['membership_name'] = $membership->name ?? 'Member';
        $result['start_date']      = $membershipRecord->start_date;
        $result['end_date']        = $membershipRecord->end_date;
        $result['discount_type']   = $membership->discount_type;
        $result['discount_value']  = $membership->discount_value;

        // -----------------------------------------------------
        // DISCOUNT CALCULATION
        // -----------------------------------------------------

        $discount = 0;
        $packagePrice = $package->price ?? 0;

        if ($membership->discount_type === 'amount') {

            $discount = $membership->discount_value;
        } elseif ($membership->discount_type === 'percent') {

            $discount = $packagePrice * ($membership->discount_value / 100);
        }

        // Pastikan tidak minus dan tidak melebihi harga paket
        if ($discount < 0) $discount = 0;
        if ($discount > $packagePrice) $discount = $packagePrice;

        $result['calculated_discount'] = $discount;

        return $result;
    }
}
