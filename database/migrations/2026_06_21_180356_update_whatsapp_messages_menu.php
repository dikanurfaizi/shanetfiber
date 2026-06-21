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
        DB::table('whatsapp_messages')->updateOrInsert(
            ['slug' => 'menu'],
            [
                'message_scope' => 'registered',
                'name'          => 'Menu Utama Bot',
                'reply_message' => "Halo %name%, selamat datang di *Shanum Network Fiber!* 🌐\n\nAda yang bisa bantu hari ini? Biar cepat, Anda bisa ketik salah satu kata kunci di bawah ini ya:\n\n* TAGIHAN → Buat cek info tagihan\n* PAKET → Buat lihat paket langganan\n* GANGGUAN → Buat lapor kendala internet\n* ADMIN → Buat ngobrol langsung sama tim CS\n\nSilakan ketik kata kunci yang Anda butuhkan, nanti langsung saya proses. Selesai! 😊\n\nSalam hangat,\n_*Shanum Network Fiber*_\n_Pwrd by PT Global Sarana Elektronika_",
                'description'   => 'Daftar menu utama bot dengan gaya bahasa yang lebih santai dan bersahabat',
                'category'      => 'INTERACTIVE',
                'updated_at'    => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('whatsapp_messages')->updateOrInsert(
            ['slug' => 'menu'],
            [
                'message_scope' => 'registered',
                'name'          => 'Menu Utama Bot',
                'reply_message' => "Halo %name%, selamat datang di *Shanum Network Fiber!* 🌐 \nUntuk memilih menu, silakan ketik kata kunci berikut: \n\n* TAGIHAN → Info tagihan\n* PAKET → Info paket langganan\n* GANGGUAN → Lapor kendala\n* ADMIN → Hubungi admin\n\nTuliskan salah satu kata di atas. Saya siap bantu! 😊",
                'description'   => 'Daftar menu utama bot dengan gaya bahasa yang lebih santai dan bersahabat',
                'category'      => 'INTERACTIVE',
                'updated_at'    => now(),
            ]
        );
    }
};
