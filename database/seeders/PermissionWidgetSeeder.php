<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PermissionWidgetSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        // Insert parent permissions
        DB::table('permissions')->insert([
            [
                'name' => 'Dashboard Access',
                'guard_name' => 'web',
                'title' => null,
                'parent_id' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Billings Widget',
                'guard_name' => 'web',
                'title' => null,
                'parent_id' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // Get parent IDs dynamically
        $dashboardAccessId = DB::table('permissions')->where('name', 'Dashboard Access')->value('id');
        $billingsWidgetId = DB::table('permissions')->where('name', 'Billings Widget')->value('id');

        // Insert child permissions
        DB::table('permissions')->insert([
            [
                'name' => 'view-bill-top-bar-total',
                'guard_name' => 'web',
                'title' => 'Billings on Top Bar: Total',
                'parent_id' => $dashboardAccessId,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'view-bill-top-bar-related',
                'guard_name' => 'web',
                'title' => 'Billings on Top Bar: Related',
                'parent_id' => $dashboardAccessId,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'view-servers-widget',
                'guard_name' => 'web',
                'title' => 'Servers Widget',
                'parent_id' => $dashboardAccessId,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'view-customers-widget-total',
                'guard_name' => 'web',
                'title' => 'Customers Widget: Total',
                'parent_id' => $dashboardAccessId,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'view-customers-widget-related',
                'guard_name' => 'web',
                'title' => 'Customers Widget: Related',
                'parent_id' => $dashboardAccessId,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'view-billings-widget-total',
                'guard_name' => 'web',
                'title' => 'Billings Widget: Total',
                'parent_id' => $billingsWidgetId,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'view-billings-widget-related',
                'guard_name' => 'web',
                'title' => 'Billings Widget: Related',
                'parent_id' => $billingsWidgetId,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'view-billings-widget-customer-count',
                'guard_name' => 'web',
                'title' => 'Billings Widget: Customer Count',
                'parent_id' => $billingsWidgetId,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'view-billings-chart-nominal-total',
                'guard_name' => 'web',
                'title' => 'Billings Chart: Total Nominal',
                'parent_id' => $billingsWidgetId,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'view-billings-chart-nominal-related',
                'guard_name' => 'web',
                'title' => 'Billings Chart: Related Nominal',
                'parent_id' => $billingsWidgetId,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'view-billings-chart-customer-count',
                'guard_name' => 'web',
                'title' => 'Billings Chart: Customer Count',
                'parent_id' => $billingsWidgetId,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
