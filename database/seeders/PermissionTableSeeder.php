<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $server = Permission::create([
            'name' => 'Server'
        ]);
        $role = Permission::create([
            'name' => 'Role'
        ]);
        $packet = Permission::create([
            'name' => 'Packet'
        ]);
        $ppp = Permission::create([
            'name' => 'PPP'
        ]);
        $user = Permission::create([
            'name' => 'User'
        ]);
        $customer = Permission::create([
            'name' => 'Customer'
        ]);
        $billing = Permission::create([
            'name' => 'Billing'
        ]);
        // $admin = Permission::create([
        //      'name' => 'Admin'
        // ]);
        //=================Server=============================
        Permission::create([
            'name' => 'server-list',
            'title' => 'Server List',
            'parent_id' =>  $server->id
        ]);
        Permission::create([
            'name' => 'server-create',
            'title' => 'Add Server',
            'parent_id' =>  $server->id
        ]);
        Permission::create([
            'name' => 'server-edit',
            'title' => 'Edit Server',
            'parent_id' =>  $server->id
        ]);
        Permission::create([
            'name' => 'server-delete',
            'title' => 'Delete Server',
            'parent_id' =>  $server->id
        ]);
        //=================Role=============================
        Permission::create([
            'name' => 'role-list',
            'title' => 'Role List',
            'parent_id' =>  $role->id
        ]);
        Permission::create([
            'name' => 'role-create',
            'title' => 'Create Role',
            'parent_id' =>  $role->id
        ]);
        Permission::create([
            'name' => 'role-edit',
            'title' => 'Edit Role',
            'parent_id' =>  $role->id
        ]);
        Permission::create([
            'name' => 'role-delete',
            'title' => 'Delete Role',
            'parent_id' =>  $role->id
        ]);
        //=================Packet=============================
        Permission::create([
            'name' => 'packet-list',
            'title' => 'Packet List',
            'parent_id' =>  $packet->id
        ]);
        Permission::create([
            'name' => 'packet-create',
            'title' => 'Add Packet',
            'parent_id' =>  $packet->id
        ]);
        Permission::create([
            'name' => 'packet-edit',
            'title' => 'Edit Packet',
            'parent_id' =>  $packet->id
        ]);
        Permission::create([
            'name' => 'packet-delete',
            'title' => 'Delete Packet',
            'parent_id' =>  $packet->id
        ]);
        //=================PPP Secret=============================
        Permission::create([
            'name' => 'secret-list',
            'title' => 'PPP Secret List',
            'parent_id' =>  $ppp->id
        ]);
        Permission::create([
            'name' => 'secret-create',
            'title' => 'Add PPP Secret',
            'parent_id' =>  $ppp->id
        ]);
        Permission::create([
            'name' => 'secret-edit',
            'title' => 'Edit PPP Secret',
            'parent_id' =>  $ppp->id
        ]);
        Permission::create([
            'name' => 'secret-delete',
            'title' => 'Delete PPP Secret',
            'parent_id' =>  $ppp->id
        ]);
        Permission::create([
            'name' => 'secret-disable',
            'title' => 'Disable/Enable PPP Secret',
            'parent_id' =>  $ppp->id
        ]);
        //=================USER=============================
        Permission::create([
            'name' => 'user-list',
            'title' => 'User List',
            'parent_id' =>  $user->id
        ]);
        Permission::create([
            'name' => 'user-create',
            'title' => 'Add User',
            'parent_id' =>  $user->id
        ]);
        Permission::create([
            'name' => 'user-edit',
            'title' => 'Edit User',
            'parent_id' =>  $user->id
        ]);
        Permission::create([
            'name' => 'user-delete',
            'title' => 'Delete User',
            'parent_id' =>  $user->id
        ]);

        //=================CUSTOMER=============================
        Permission::create([
            'name' => 'customer-list',
            'title' => 'Customer List',
            'parent_id' =>  $customer->id
        ]);
        Permission::create([
            'name' => 'customer-create',
            'title' => 'Add Customer',
            'parent_id' =>  $customer->id
        ]);
        Permission::create([
            'name' => 'customer-edit',
            'title' => 'Edit Customer',
            'parent_id' =>  $customer->id
        ]);
        Permission::create([
            'name' => 'customer-delete',
            'title' => 'Delete Customer',
            'parent_id' =>  $customer->id
        ]);

        //=================Billing=============================
        Permission::create([
            'name' => 'billing-list',
            'title' => 'Billing List',
            'parent_id' =>  $billing->id
        ]);
        Permission::create([
            'name' => 'billing-create',
            'title' => 'Create Billing',
            'parent_id' =>  $billing->id
        ]);
        Permission::create([
            'name' => 'billing-edit',
            'title' => 'Edit Billing',
            'parent_id' =>  $billing->id
        ]);
        Permission::create([
            'name' => 'billing-delete',
            'title' => 'Delete Billing',
            'parent_id' =>  $billing->id
        ]);
        /*
        //=================Admin=============================
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
        */
    }
}
