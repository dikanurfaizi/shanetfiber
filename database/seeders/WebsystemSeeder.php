<?php

namespace Database\Seeders;

use App\Models\Websystem;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class WebsystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Websystem::create([
            'title' => 'Customer Management',
            'slug' => 'websystem',
            'version' => config('app.version'),

        ]);
    }
}
