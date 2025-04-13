<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionWidgetRollbackSeeder extends Seeder
{
    public function run()
    {
        DB::table('permissions')->whereIn('name', [
            'Dashboard Access',
            'view-servers-widget',
            'view-customers-widget-total',
            'view-customers-widget-partial',
            'Billings Widget',
            'view-billings-widget-total',
            'view-billings-widget-partial',
            'view-billings-widget-customer-count',
            'view-billings-chart-nominal-total',
            'view-billings-chart-nominal-partial',
            'view-billings-chart-customer-count',
        ])->delete();
    }
}
