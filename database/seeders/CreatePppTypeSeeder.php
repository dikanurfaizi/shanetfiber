<?php

namespace Database\Seeders;

use App\Models\PppType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreatePppTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PppType::create([
            'name' => 'any',
        ]);
        PppType::create([
            'name' => 'async',
        ]);
        PppType::create([
            'name' => 'l2tp',
        ]);
        PppType::create([
            'name' => 'ovpn',
        ]);
        PppType::create([
            'name' => 'pppoe',
        ]);
        PppType::create([
            'name' => 'pptp',
        ]);
        PppType::create([
            'name' => 'sstp',
        ]);
    }
}
