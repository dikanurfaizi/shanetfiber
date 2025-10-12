<?php

namespace App\Services\Networks\Ont;

use App\Models\Network\Ont;
use App\Helpers\SnmpHelper;
use App\Helpers\LogHelper;
use App\Services\Networks\HouseKeepingLog;
use App\Services\Networks\Olt\OltLogService;

class OntUpdateService
{
    public static function updateOnts($olt, $snmpData, $duration, $username)
    {
        if (empty($snmpData) || !is_array($snmpData)) {
            $data = [
                'olt_id'        => $olt->id,
                'updated_at'    => now(),
                'success_count' => 0,
                'failed_count'  => 0,
                'details'       => "SNMP walk failed / not any response from OLT {$olt->ip}",
                'ont_count'     => 0,
                'status'        => 'Down',
                'username'      => $username,
                'duration'      => $duration ?? 0
            ];
            OltLogService::insertLog($data);

            LogHelper::logError(null, [
                'olt_id' => $olt->id,
                'olt_ip' => $olt->ip,
                'message' => 'SNMP walk failed or empty data'
            ], 500, "Failed Update ONT");

            return false;
        }

        // Hapus semua ONT milik OLT tertentu
        $deletedCount = Ont::where('olt_id', $olt->id)->delete();

        // Logging untuk verifikasi
        LogHelper::logInfo(null, [
            'olt_id'       => $olt->id,
            'olt_ip'       => $olt->ip,
            'deleted_rows' => $deletedCount,
        ], 200, "Delete Ont Successfully");

        $successList = [];
        $failedList  = [];

        foreach ($snmpData as $index => $data) {
            try {
                $ontData = [
                    'olt_id'       => $olt->id,
                    'ont_index'    => $index,
                    'pon_group'    => $data['pon_group'] ?? NULL,
                    'last_updated' => now(),
                    'name'         => $data['oid_name'] ?? '',
                    'tx_power'     => trim($data['txpower'] ?? 'N/A', '"'),
                    'rx_power'     => trim($data['rxpower'] ?? 'N/A', '"'),
                    'status'       => ($data['status'] ?? '') == '1' ? 'Up' : 'Down',
                    'mac_address'  => SnmpHelper::formatMacFromSnmp($data['macaddress'] ?? ''),
                    'temperature' => isset($data['temperature'])
                        ? (int) round(floatval(trim($data['temperature'], '"'))) . " °C"
                        : 'N/A',
                ];

                Ont::create($ontData);
                $successList[] = $ontData;
            } catch (\Throwable $e) {
                $failedList[] = [
                    'index'   => $index,
                    'raw'     => $data,
                    'error'   => $e->getMessage()
                ];
            }
        }

        $data = [
            'olt_id'        => $olt->id,
            'updated_at'    => now(),
            'success_count' => count($successList),
            'failed_count'  => count($failedList),
            'details'       => "Update ONT selesai: {$olt->name} ({$olt->ip}:{$olt->port})",
            'ont_count'     => count($successList),
            'status'        => 'Up',
            'username'      => $username,
            'duration'      => $duration ?? 0
        ];
        OltLogService::insertLog($data);

        // Log summary lengkap sekali di akhir
        LogHelper::logInfo(null, [
            'olt_id'   => $olt->id,
            'olt_ip'   => $olt->ip,
            'total_candidates' => count($snmpData),
            'inserted' => count($successList),
            'failed'   => count($failedList),
            'failed_list'  => $failedList,
            'duration' => $duration,
        ], 200, "Summary insert ONT");

        OltLogService::houseKeep();
        return true;
    }

    public static function updateOntMetric($olt, $snmpData, $metric, $duration, $username)
    {
        if (empty($snmpData) || !is_array($snmpData)) {
            $data = [
                'olt_id'        => $olt->id,
                'updated_at'    => now(),
                'success_count' => 0,
                'failed_count'  => 0,
                'details'       => "Partial update failed / no SNMP response from OLT {$olt->ip}",
                'ont_count'     => 0,
                'status'        => 'Down',
                'username'      => $username,
                'duration'      => $duration ?? 0
            ];
            OltLogService::insertLog($data);

            LogHelper::logError(null, [
                'olt_id' => $olt->id,
                'olt_ip' => $olt->ip,
                'metric' => $metric,
                'message' => 'SNMP walk failed or empty data'
            ], 500, "Failed partial update ONT");

            return false;
        }

        $successCount = 0;
        $failedList = [];

        foreach ($snmpData as $index => $data) {
            try {
                $value = $data[$metric] ?? null;

                if ($value === null || $value === '' || $value === 'N/A') {
                    continue;
                }

                // Normalisasi nilai
                $cleanValue = trim($value, '" ');

                // Update sesuai metric
                $updateData = [
                    $metric => $cleanValue,
                    'last_updated' => now(),
                ];

                // Update existing ONT
                $updated = Ont::where('olt_id', $olt->id)
                    ->where('ont_index', $index)
                    ->update($updateData);

                // Jika belum ada record ONT-nya, create baru minimal dengan data dasar
                if (!$updated) {
                    Ont::create([
                        'olt_id' => $olt->id,
                        'ont_index' => $index,
                        'pon_group' => $data['pon_group'] ?? null,
                        $metric => $cleanValue,
                        'last_updated' => now(),
                        'status' => 'Unknown',
                    ]);
                }

                $successCount++;
            } catch (\Throwable $e) {
                $failedList[] = [
                    'index' => $index,
                    'metric' => $metric,
                    'error' => $e->getMessage(),
                ];
            }
        }

        // Catat log ringkas
        $data = [
            'olt_id'        => $olt->id,
            'updated_at'    => now(),
            'success_count' => $successCount,
            'failed_count'  => count($failedList),
            'details'       => "Partial update ({$metric}) selesai: {$olt->name} ({$olt->ip}:{$olt->port})",
            'ont_count'     => $successCount,
            'status'        => 'Down',
            'username'      => $username,
            'duration'      => $duration ?? 0
        ];
        OltLogService::insertLog($data);

        // Logging detail untuk debugging
        LogHelper::logInfo(null, [
            'olt_id' => $olt->id,
            'olt_ip' => $olt->ip,
            'metric' => $metric,
            'updated_count' => $successCount,
            'failed_count' => count($failedList),
            'duration' => $duration,
            'failed_list' => $failedList,
        ], 200, "Partial ONT update summary ({$metric})");

        return true;
    }
}
