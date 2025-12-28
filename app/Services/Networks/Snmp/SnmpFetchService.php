<?php

namespace App\Services\Networks\Snmp;

use App\Models\Network\OidMapping;
use App\Helpers\SnmpHelper;
use App\Helpers\LogHelper;

class SnmpFetchService
{
    public static function getOnts($olt)
    {
        $startTime = microtime(true);

        $mapping = OidMapping::with(['details' => fn($q) => $q->where('query_type', 2)])
            ->find($olt->oid_mapping_id);

        if (!$mapping) {
            LogHelper::logError(null, ['mapping' => $mapping, 'olt' => $olt], 404, "Mapping not found");
            return ['snmpData' => false, 'duration' => 0, 'totalOnt' => 0];
        }

        $oids = [];
        foreach ($mapping->details as $detail) {
            $oids[$detail->oid_key] = trim($detail->oid_value, ' .');
        }
        // LogHelper::logInfo(null, ['oids_keys' => array_keys($oids)], 200, "OID list prepared");

        $rawSnmp   = [];
        $ontsList  = [];
        foreach ($oids as $key => $baseOid) {
            $walkRes = SnmpCoreService::snmpWalk($olt->ip, $olt->port ?? 161, $olt->community, $baseOid, $olt->version);
            LogHelper::logInfo(null, ['walkRes' => $walkRes], 200, "walkRes Info");
            $rawSnmp[$key] = $walkRes;
            $count = count((array)$walkRes);

            // // log jumlah hasil per OID (tidak semua isi)
            LogHelper::logInfo(null, ['key' => $key, 'count' => $count], 200, "SNMP walk count");

            foreach ((array)$walkRes as $fullOid => $val) {
                if (is_array($val)) $val = reset($val);
                $index = SnmpParser::extractIndex($fullOid, $baseOid);

                $clean = SnmpParser::normalizeValue($val);
                if (!isset($ontsList[$index])) $ontsList[$index] = [];

                $ontsList[$index][$key] = $clean;

                // === Tambahan: ambil nomor PON dari index ===
                $ponNumber = SnmpParser::extractPonNumber($index);
                $ontsList[$index]['pon_group'] = $ponNumber;
            }
        }

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $totalOnt = count($ontsList);

        // Log sample saja biar ringkas
        LogHelper::logInfo(null, [
            'sample_onts' => array_slice($ontsList, 0, 5, true),
            'total_ont'   => $totalOnt
        ], 200, "ONT data sample");

        LogHelper::logInfo(null, ['rawSnmp' => $rawSnmp], 200, "SNMP Raw Data (debug only)");
        LogHelper::logInfo(null, ['ontsList' => $ontsList], 200, "OntsList Raw Data (debug only)");

        return [
            'snmpData'  => $rawSnmp,
            'onts'      => $ontsList,
            'duration'  => $duration,
            'total_ont' => $totalOnt
        ];
    }

    public static function fetchMetric($olt, string $metric)
    {
        $startTime = microtime(true);

        $mapping = OidMapping::with([
            'details' => fn($q) =>
            $q->where('query_type', 2)->where('oid_key', $metric)
        ])->find($olt->oid_mapping_id);

        if (!$mapping || $mapping->details->isEmpty()) {
            LogHelper::logError(null, [
                'metric' => $metric,
                'olt'    => $olt->name,
            ], 404, "OID mapping not found for metric");
            return ['snmpData' => false, 'duration' => 0, 'totalOnt' => 0];
        }

        $oidValue = trim($mapping->details->first()->oid_value, ' .');

        $walkRes = SnmpCoreService::snmpWalk($olt->ip, $olt->port, $olt->community, $oidValue, $olt->version);
        $ontsList = [];

        foreach ((array)$walkRes as $fullOid => $val) {
            if (is_array($val)) $val = reset($val);
            $index = SnmpParser::extractIndex($fullOid, $oidValue);
            $clean = SnmpParser::normalizeValue($val);
            $ontsList[$index][$metric] = $clean;
            $ontsList[$index]['pon_group'] = SnmpParser::extractPonNumber($index);
        }

        $duration = round(microtime(true) - $startTime, 2);

        LogHelper::logInfo(null, [
            'olt' => $olt->name,
            'metric' => $metric,
            'total_ont' => count($ontsList)
        ], 200, "Fetched single metric via SNMP");

        return [
            'snmpData'  => $walkRes,
            'onts'      => $ontsList,
            'duration'  => $duration,
            'total_ont' => count($ontsList)
        ];
    }
}
