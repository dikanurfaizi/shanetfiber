<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FillTotalAmountInBillingsSeeder extends Seeder
{
    public function run()
    {
        DB::table('billings')->update([
            'total_amount' => DB::raw('base_price')
        ]);
    }
}
