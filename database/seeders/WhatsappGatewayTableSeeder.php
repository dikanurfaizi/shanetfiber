<?php

namespace Database\Seeders;

use App\Models\WhatsappGateway\WhatsappGateway;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WhatsappGatewayTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil ID dari nomor WhatsApp yang diinginkan
        $whatsappNumberId = DB::table('whatsapp_numbers')
            ->where('number', '6287818040024') // Sesuaikan dengan nomor yang Anda inginkan
            ->value('id');

        // Jika tidak ditemukan, beri fallback (opsional)
        if (!$whatsappNumberId) {
            $whatsappNumberId = DB::table('whatsapp_numbers')->insertGetId([
                'slug' => '3d4zs',
                'name' => 'Official Contact',
                'number' => '6287818040024',
                'disabled' => false,
                'webhook_client_header' => 'Shanet-Signature',
                'webhook_client_secret' => 'O5aEU9rSlNnHevb',
                'description' => 'Official Contact Shanum Network Fiber',
                'default' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Buat entri di tabel WhatsappGateway
        WhatsappGateway::create([
            'slug' => 'gateway',
            'name' => 'WA Gateway',
            'disabled' => false,
            'remaining_day' => 0,
            'country_code' => 62,
            'description' => 'Official Contact Shanum Network Fiber',
            'url_callback' => 'http://103.129.148.212:3000',
            'api_key' => 'yE1Q82tGjnB3xZveu50yqQ==',
            'whatsapp_number_id' => $whatsappNumberId,
        ]);
    }
}
