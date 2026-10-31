<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('telegram_messages')->insert([
            [
                'slug'             => 'changes_package',
                'name'             => 'Notifikasi Perubahan Upgrade Paket',
                'incoming_message' => 'notifikasi perubahan upgrade paket',
                'reply_message'    => "🔔 *NOTIFIKASI PERUBAHAN PAKET PELANGGAN*\n\nHalo Admin, terdapat pembaruan/upgrade paket internet pelanggan yang telah berhasil diproses oleh sistem.\n\nBerikut rincian data pelanggan:\n- *Nama Pelanggan:* %name%\n- *Customer ID:* %customer_id%\n- *Paket Lama:* %old_package% (%old_bill%)\n- *Paket Baru:* %new_package% (%new_bill%)\n- *Diproses Oleh:* %admin%\n- *Kontak CS:* %cs_contact%\n\nStatus layanan baru sudah aktif di sistem.\n\n_*Shanum Network Fiber*_\n_Pwrd by PT Global Sarana Elektronika_",
                'description'      => 'Dikirim otomatis ke Admin setelah sistem atau admin berhasil memproses perpindahan/upgrade paket internet pelanggan.',
                'default'          => 0,
                'disabled'         => 0,
                'created_at'       => '2025-12-04 23:15:45',
                'updated_at'       => '2026-06-21 19:27:46',
                'deleted_at'       => null,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('telegram_messages')
            ->whereIn('slug', ['changes_package', 'test_message'])
            ->delete();
    }
};
