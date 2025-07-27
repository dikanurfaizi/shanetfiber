<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BillingCycleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('billing_cycles')->insert([
            [
                'code' => 'monthly',
                'name' => 'Per Bulan',
                'months' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'bimonthly',
                'name' => 'Per 2 Bulan',
                'months' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'quarterly',
                'name' => 'Per 3 Bulan',
                'months' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '4monthly',
                'name' => 'Per 4 Bulan',
                'months' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'semiannual',
                'name' => 'Per 6 Bulan',
                'months' => 6,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'annual',
                'name' => 'Per Tahun',
                'months' => 12,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
