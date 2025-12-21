<?php

namespace App\Services\Customers;

use App\Helpers\LogHelper;
use App\Models\Customers\Customer;
use App\Models\Network\Ont;

class CustomerOntService
{
    public function getOntForCustomer(Customer $customer): ?object
    {
        LogHelper::logInfo(null, [
            'customer_id'   => $customer->id,
            'customer_name' => $customer->name,
            'mac_address'   => $customer->mac_address,
        ], 200, '[CustomerOntService] Start getOntForCustomer', null);

        if (! $customer->mac_address) {
            LogHelper::logWarning(null, [
                'customer_id' => $customer->id,
            ], 404, '[CustomerOntService] Customer has no MAC address', null);

            return null;
        }

        $normalizedMac = strtolower(str_replace(':', '', $customer->mac_address));

        LogHelper::logInfo(null, [
            'original'   => $customer->mac_address,
            'normalized' => $normalizedMac,
        ], 200, '[CustomerOntService] Normalized MAC', null);

        // =========================
        // MATCH 1 — EXACT MAC MATCH
        // =========================
        LogHelper::LogInfo(null, [], 200, '[CustomerOntService] Try exact MAC match');

        $ont = Ont::query()
            ->from('onts as o')
            ->join('olts as ol', 'ol.id', '=', 'o.olt_id')
            ->whereRaw(
                "LOWER(REPLACE(o.mac_address, ':', '')) = ?",
                [$normalizedMac]
            )
            ->select([
                'o.*',
                'ol.name as olt_name',
            ])
            ->first();

        if ($ont) {
            LogHelper::logInfo(null, [
                'ont_id'     => $ont->id,
                'ont_name'   => $ont->name,
                'ont_mac'    => $ont->mac_address,
                'olt_name'   => $ont->olt_name,
                'match_type' => 'exact_mac',
            ], 200, '[CustomerOntService] ONT found by exact MAC match', null);

            return $ont;
        }

        LogHelper::logInfo(null, [], 200, '[CustomerOntService] Exact MAC match not found');

        // ====================================
        // MATCH 2 — PREFIX + FIRST NAME MATCH
        // ====================================
        $prefix    = substr($normalizedMac, 0, 10);
        $firstName = strtolower(explode(' ', $customer->name)[0] ?? '');

        LogHelper::logInfo(null, [
            'mac_prefix' => $prefix,
            'first_name' => $firstName,
        ], 200, '[CustomerOntService] Try prefix + first name match', null);

        $ont = Ont::query()
            ->from('onts as o')
            ->join('olts as ol', 'ol.id', '=', 'o.olt_id')
            ->whereRaw(
                "LOWER(REPLACE(o.mac_address, ':','')) LIKE ?",
                ["{$prefix}%"]
            )
            ->whereRaw(
                "LOWER(SUBSTRING_INDEX(o.name, ' ', 1)) = ?",
                [$firstName]
            )
            ->select([
                'o.*',
                'ol.name as olt_name',
            ])
            ->first();

        if ($ont) {
            LogHelper::logInfo(null, [
                'ont_id'     => $ont->id,
                'ont_name'   => $ont->name,
                'ont_mac'    => $ont->mac_address,
                'olt_name'   => $ont->olt_name,
                'match_type' => 'prefix_name',
            ], 200, '[CustomerOntService] ONT found by prefix + first name', null);

            return $ont;
        }

        LogHelper::logWarning(null, [
            'customer_id' => $customer->id,
            'normalized_mac' => $normalizedMac,
            'mac_prefix' => $prefix,
            'first_name' => $firstName,
        ], 404, '[CustomerOntService] ONT not found after all matching strategies', null);

        return null;
    }
}
