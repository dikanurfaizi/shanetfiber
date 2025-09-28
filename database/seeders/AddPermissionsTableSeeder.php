<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddPermissionsTableSeeder extends Seeder
{
    public function run(): void
    {
        // Fungsi helper: insert atau ambil id kalau sudah ada
        $getOrCreatePermissionId = function ($name, $guard_name, $title, $parent_id = 0, $created_at = null) {
            $permission = DB::table('permissions')
                ->where('name', $name)
                ->where('guard_name', $guard_name)
                ->first();

            if ($permission) {
                return $permission->id;
            }

            return DB::table('permissions')->insertGetId([
                'name' => $name,
                'guard_name' => $guard_name,
                'title' => $title,
                'parent_id' => $parent_id,
                'created_at' => $created_at ?? now(),
                'updated_at' => $created_at ?? now(),
            ]);
        };

        // Insert parent
        $parentId = $getOrCreatePermissionId(
            'Network Management',
            'web',
            'Network Management',
            0,
            '2025-09-20 08:31:00'
        );

        // Insert children
        $oltPermissionId = $getOrCreatePermissionId(
            'olt-management',
            'web',
            'OLT Management',
            $parentId,
            '2025-08-09 08:31:00'
        );

        $onuPermissionId = $getOrCreatePermissionId(
            'onu-management',
            'web',
            'ONU Management',
            $parentId,
            '2025-08-09 08:31:00'
        );

        // Insert ke role_has_permissions jika tabel ada
        if (DB::getSchemaBuilder()->hasTable('role_has_permissions')) {
            $roleId = 1;

            foreach ([$oltPermissionId, $onuPermissionId] as $permissionId) {
                // Cek dulu agar tidak duplicate
                $exists = DB::table('role_has_permissions')
                    ->where('role_id', $roleId)
                    ->where('permission_id', $permissionId)
                    ->exists();

                if (!$exists) {
                    DB::table('role_has_permissions')->insert([
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                    ]);
                }
            }
        }
    }
}
