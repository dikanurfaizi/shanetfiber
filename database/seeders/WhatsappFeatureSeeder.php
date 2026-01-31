<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WhatsappFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $defaultTimeWindow = [
            'start' => '08:00',
            'end'   => '21:00',
        ];

        $timezone = 'Asia/Jakarta';

        $features = [
            [
                'name' => 'Aktivasi',
                'type' => 'onprem',
                'is_active' => true,
                'is_scheduled' => false,
                'schedule' => null,
                'description' => 'Mengirim pesan aktivasi layanan kepada pelanggan setelah proses pendaftaran atau instalasi berhasil.',
            ],
            [
                'name' => 'Pembayaran',
                'type' => 'onprem',
                'is_active' => true,
                'is_scheduled' => false,
                'schedule' => null,
                'description' => 'Mengirim notifikasi pembayaran kepada pelanggan, termasuk konfirmasi dan status transaksi.',
            ],
            [
                'name' => 'PayLater',
                'type' => 'onprem',
                'is_active' => true,
                'is_scheduled' => false,
                'schedule' => null,
                'description' => 'Mengirim notifikasi pembayaran menunda pembayaran kepada pelanggan.',
            ],
            [
                'name' => 'Broadcast Awal Bulan',
                'type' => 'cloud',
                'is_active' => false,
                'is_scheduled' => true,
                'schedule' => [
                    'timezone' => $timezone,
                    'days' => [1, 2, 3],
                    'time_window' => $defaultTimeWindow,
                ],
                'description' => 'Broadcast onboarding bulan pertama pada hari ke-1 sampai ke-3 dalam jam operasional.',
            ],
            [
                'name' => 'Broadcast Pertengahan Bulan',
                'type' => 'cloud',
                'is_active' => true,
                'is_scheduled' => true,
                'schedule' => [
                    'timezone' => $timezone,
                    'days' => [10, 15],
                    'time_window' => $defaultTimeWindow,
                ],
                'description' => 'Broadcast pertengahan bulan pada tanggal 10 dan 15 dalam jam operasional.',
            ],
            [
                'name' => 'Broadcast Akhir Bulan',
                'type' => 'cloud',
                'is_active' => true,
                'is_scheduled' => true,
                'schedule' => [
                    'timezone' => $timezone,
                    'days' => [25],
                    'time_window' => $defaultTimeWindow,
                ],
                'description' => 'Broadcast akhir bulan pada tanggal 25 sebagai pengingat sebelum penutupan billing.',
            ],
            [
                'name' => 'Pembayaran Online',
                'type' => 'onprem',
                'is_active' => true,
                'is_scheduled' => false,
                'schedule' => null,
                'description' => 'Mengirim pesan terkait pembayaran online dan instruksi pembayaran digital.',
            ],
            [
                'name' => 'Pembatalan Pembayaran',
                'type' => 'onprem',
                'is_active' => false,
                'is_scheduled' => false,
                'schedule' => null,
                'description' => 'Mengirim notifikasi pembatalan pembayaran pelanggan.',
            ],
            [
                'name' => 'Perubahan Paket',
                'type' => 'onprem',
                'is_active' => false,
                'is_scheduled' => false,
                'schedule' => null,
                'description' => 'Mengirim notifikasi perubahan paket layanan pelanggan.',
            ],
        ];

        foreach ($features as $feature) {
            DB::table('whatsapp_features')->insert([
                'slug'         => (string) Str::uuid(),
                'name'         => $feature['name'],
                'alias'        => Str::lower(Str::slug($feature['name'], '_')),
                'type'         => $feature['type'],
                'is_active'    => $feature['is_active'],
                'is_scheduled' => $feature['is_scheduled'],
                'schedule'     => $feature['schedule']
                    ? json_encode($feature['schedule'])
                    : null,
                'description'  => $feature['description'],
                'created_by'   => 'System',
                'updated_by'   => 'System',
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }
    }
}
