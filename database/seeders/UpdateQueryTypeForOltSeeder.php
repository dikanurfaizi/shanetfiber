<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateQueryTypeForOltSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('oid_mapping_details')
            ->whereIn('oid_key', ['sysName', 'sysDescr'])
            ->update(['query_type' => 1]);
    }
}
