<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tripay;
use App\Models\PaymentGateway;

class TripaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tripay::create([
            'name' => 'Tripays',
            'slug' => 'tripays',
            'tripays_merchant_id' => 'T34508',
            'tripays_sandbox_api_key' => 'DEV-hiBSKyEeNo8bKGAyhIlIWjVvtfWyKylSEmZTIvUz',
            'tripays_sandbox_private_key' => 'biUh4-RTBYg-CQD3d-50Vu7-XOuV4',
        ]);

        // Menambahkan data ke tabel payment_gateways
        PaymentGateway::create([
            'name' => 'Tripays',
            'slug' => 'tripays',
            'description' => 'Tripays Payment Gateway',
        ]);
    }
}
