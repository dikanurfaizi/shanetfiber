<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Paket\PackageCategory;

class PackageCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Internet Only', 'slug' => 'internetOnly', 'description' => 'For internet only'],
            ['name' => 'Internet Stream', 'slug' => 'internetStream', 'description' => 'For streamer'],
            ['name' => 'Internet + TV', 'slug' => 'internetPlusTV', 'description' => 'For internet and TV'],
        ];

        foreach ($categories as $category) {
            PackageCategory::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
