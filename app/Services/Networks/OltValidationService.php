<?php

namespace App\Services\Networks;

use App\Models\Network\OidMapping;
use App\Services\Networks\SnmpService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OltValidationService
{
    public function validateOltData(array $data): \Illuminate\Validation\Validator
    {
        $vendors = OidMapping::pluck('vendor')->toArray();

        return Validator::make($data, [
            'olt_name' => 'required|string|max:255',
            'ip_address' => 'required|string',
            'port' => 'nullable|numeric',
            'community' => 'nullable|string',
            'olt_type' => 'required|numeric',
        ]);
    }

    public function testSnmpConnection(array $data, string $username): array
    {
        $validator = $this->validateOltData($data);

        if ($validator->fails()) {
            throw new \InvalidArgumentException(implode(', ', $validator->errors()->all()), 422);
        }

        $oltVendor = OidMapping::where('id', $data['olt_type'])->first();

        if (!$oltVendor) {
            throw new \InvalidArgumentException('OID mapping not found.');
        }

        $olt = (object) [
            'name' => $data['olt_name'],
            'ip' => $data['ip_address'],
            'port' => $data['port'],
            'community' => $data['community'],
            'oid_mapping_id' => $oltVendor->id,
            'vendor' => $oltVendor->vendor,
            'version' => 'v2c'
        ];

        $healthCheck = SnmpService::checkOltStatus($olt, $username);

        if ($healthCheck['status'] === 'Up') {
            $snmpData = SnmpService::getOnts($olt);
            $totalOnt = $snmpData['total_ont'];

            return [
                'success' => true,
                'device_info' => $olt->vendor,
                'ont_count' => $totalOnt,
            ];
        }

        return [
            'success' => false,
            'device_info' => $olt->vendor,
            'ont_count' => 0,
            'error' => 'Could not establish SNMP connection to the device',
        ];
    }
}
