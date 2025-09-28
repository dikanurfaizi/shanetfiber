<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NewOidMappingsSeeder extends Seeder
{
    public function run(): void
    {
        // --- Vendor Hioso ---
        $hiosoId = DB::table('oid_mappings')->insertGetId([
            'vendor'      => 'HiosoEpon',
            'model'       => null,
            'description' => 'Hioso OLT',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        DB::table('oid_mapping_details')->insert([
            [
                'mapping_id'  => $hiosoId,
                'oid_key'     => 'oid_name',
                'oid_value'   => '.1.3.6.1.4.1.25355.3.2.6.3.2.1.37',
                'description' => 'OID name',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'mapping_id'  => $hiosoId,
                'oid_key'     => 'txpower',
                'oid_value'   => '.1.3.6.1.4.1.25355.3.2.6.14.2.1.4',
                'description' => 'Tx Power',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'mapping_id'  => $hiosoId,
                'oid_key'     => 'rxpower',
                'oid_value'   => '.1.3.6.1.4.1.25355.3.2.6.14.2.1.8',
                'description' => 'Rx Power',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'mapping_id'  => $hiosoId,
                'oid_key'     => 'status',
                'oid_value'   => '.1.3.6.1.4.1.25355.3.2.6.3.2.1.39',
                'description' => 'ONU Status',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'mapping_id'  => $hiosoId,
                'oid_key'     => 'macaddress',
                'oid_value'   => '.1.3.6.1.4.1.25355.3.2.6.3.2.1.11',
                'description' => 'ONU MAC Address',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'mapping_id'  => $hiosoId,
                'oid_key'     => 'temperature',
                'oid_value'   => '.1.3.6.1.4.1.25355.3.2.6.14.2.1.7',
                'description' => 'Device Temperature',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'mapping_id' => $hiosoId, // misalnya hioso
                'oid_key'    => 'sysName',
                'oid_value'  => '1.3.6.1.2.1.1.5.0',
                'description' => 'System Name',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'mapping_id' => $hiosoId,
                'oid_key'    => 'sysDescr',
                'oid_value'  => '1.3.6.1.2.1.1.1.0',
                'description' => 'System Description',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // --- Vendor HSGQ ---
        $hsgqId = DB::table('oid_mappings')->insertGetId([
            'vendor'      => 'hsgqEpon',
            'model'       => null,
            'description' => 'HSGQ OLT',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        DB::table('oid_mapping_details')->insert([
            [
                'mapping_id'  => $hsgqId,
                'oid_key'     => 'oid_name',
                'oid_value'   => '.1.3.6.1.4.1.50224.3.12.2.1.2',
                'description' => 'OID name',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'mapping_id'  => $hsgqId,
                'oid_key'     => 'txpower',
                'oid_value'   => '.1.3.6.1.4.1.50224.3.12.3.1.5',
                'description' => 'Tx Power',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'mapping_id'  => $hsgqId,
                'oid_key'     => 'rxpower',
                'oid_value'   => '.1.3.6.1.4.1.50224.3.12.3.1.4',
                'description' => 'Rx Power',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'mapping_id'  => $hsgqId,
                'oid_key'     => 'status',
                'oid_value'   => '.1.3.6.1.4.1.50224.3.12.2.1.5',
                'description' => 'ONU Status',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            // Mac & Temp memang NULL di data awal -> tidak dimasukkan
        ]);
    }
}
