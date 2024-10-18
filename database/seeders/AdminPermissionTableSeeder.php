<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminPermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $admin = Permission::create([
            'name' => 'Admin'
        ]);
        //=================###=============================
        Permission::create([
            'name' => 'is-admin',
            'title' => 'Is Admin',
            'parent_id' =>  $admin->id
        ]);
        Permission::create([
            'name' => 'can-update',
            'title' => 'Can Update',
            'parent_id' =>  $admin->id
        ]);
    }
}
