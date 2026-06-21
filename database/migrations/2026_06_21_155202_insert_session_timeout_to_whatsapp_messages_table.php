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
        DB::table('whatsapp_messages')->insert([
            'slug'          => 'session_timeout',
            'message_scope' => 'both',
            'keywords'      => json_encode([]),
            'name'          => 'Sesi CS Berakhir (Timeout)',
            'incoming_message' => null,
            'reply_message' => "Sesi percakapan Anda dengan Admin telah berakhir karena tidak ada aktivitas selama 15 menit. ⏱️\n\nSistem otomatis kami telah aktif kembali. Ada hal lain yang bisa kami bantu? \nSilakan ketik *Menu* untuk melihat daftar layanan otomatis kami. Terima kasih! 🙏\n\nSalam hangat,\n_*Shanum Network Fiber*_\n_Pwrd by PT Global Sarana Elektronika_",
            'description'   => 'Dikirim otomatis oleh Cron Job ketika sesi human chat idle selama 15 menit',
            'default'       => 1,
            'disabled'      => 0,
            'category'      => 'SYSTEM',
            'is_interactive' => 0,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('whatsapp_messages')->where('slug', 'session_timeout')->delete();
    }
};
