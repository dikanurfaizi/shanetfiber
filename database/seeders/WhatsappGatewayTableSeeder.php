<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WhatsappGatewayTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil ID dari WhatsappGateway yang sudah terisi (misal slug 'gateway' atau record pertama)
        $gatewayId = DB::table('whatsapp_gateways')
            ->where('slug', 'gowa')
            ->value('id') ?? DB::table('whatsapp_gateways')->value('id');

        if (!$gatewayId) {
            // Fallback jika whatsapp_gateways ternyata belum ada record sama sekali
            $gatewayId = DB::table('whatsapp_gateways')->insertGetId([
                'slug' => 'gowa',
                'name' => 'Gowa On-Prem',
                'type' => 'onprem',
                'description' => 'Official Gateway',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('whatsapp_gateways')
                ->where('id', $gatewayId)
                ->update([
                    'is_active' => 1,
                    'updated_at' => now(),
                ]);
        }

        // 2. Buat/Ambil WhatsappGatewayAccount
        $accountId = DB::table('whatsapp_gateway_accounts')
            ->where('whatsapp_gateway_id', $gatewayId)
            ->value('id');

        if (!$accountId) {
            $accountId = DB::table('whatsapp_gateway_accounts')->insertGetId([
                'whatsapp_gateway_id' => $gatewayId,
                'name' => 'Official Account',
                'purpose' => 'Notification & Billing',
                'description' => 'Official Contact Account',
                'base_url' => 'http://103.129.148.212:3000',
                'auth_enabled' => 0,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Buat/Update WhatsappNumber dengan nama kolom yang sudah diperbaiki (is_enabled & is_default)
        $whatsappNumberId = DB::table('whatsapp_numbers')
            ->where('whatsapp_gateway_account_id', $accountId)
            ->value('id');

        if (!$whatsappNumberId) {
            DB::table('whatsapp_numbers')->insert([
                'whatsapp_gateway_account_id' => $accountId,
                'slug' => '3d4zs',
                'name' => 'Official Contact',
                'country_code' => '62',
                'number' => '6287818040024',
                'webhook_client_header' => 'WebApp-Signature',
                'webhook_client_secret' => 'O5aEU9rSlNnHevb',
                'description' => 'Official Contact',
                'is_enabled' => 1, // Menggantikan kolom disabled
                'is_default' => 1, // Menggantikan kolom default
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            // Pastikan relasi ke account terhubung jika nomor sudah ada sebelumnya
            DB::table('whatsapp_numbers')
                ->where('id', $whatsappNumberId)
                ->update([
                    'is_enabled' => 1,
                    'is_default' => 1,
                    'updated_at' => now(),
                ]);
        }
    }
}
