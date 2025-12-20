<?php

namespace App\Services\Customers;

use App\Models\Customers\Customer;
use App\Models\Network\Ont;
use Illuminate\Support\Facades\DB;

class CustomerOntService
{
    public function getOntForCustomer(Customer $customer): ?object
    {
        if (! $customer->mac_address) {
            return null;
        }

        $normalizedMac = strtolower(str_replace(':', '', $customer->mac_address));

        // MATCH 1 — EXACT MAC MATCH
        $ont = Ont::query()
            ->from('onts as o')
            ->join('olts as ol', 'ol.id', '=', 'o.olt_id')
            ->whereRaw("LOWER(REPLACE(o.mac_address, ':', '')) = ?", [$normalizedMac])
            ->select([
                'o.*',
                'ol.name as olt_name',
            ])
            ->first();

        if ($ont) return $ont;

        // MATCH 2 — PREFIX MATCH (MAC + FIRST NAME)
        $prefix = substr($normalizedMac, 0, 10);
        $firstName = strtolower(explode(' ', $customer->name)[0] ?? '');

        return Ont::query()
            ->from('onts as o')
            ->join('olts as ol', 'ol.id', '=', 'o.olt_id')
            ->whereRaw("LOWER(REPLACE(o.mac_address, ':','')) LIKE '{$prefix}%'")
            ->whereRaw("LOWER(SUBSTRING_INDEX(o.name, ' ', 1)) = ?", [$firstName])
            ->select([
                'o.*',
                'ol.name as olt_name',
            ])
            ->first();
    }
}
