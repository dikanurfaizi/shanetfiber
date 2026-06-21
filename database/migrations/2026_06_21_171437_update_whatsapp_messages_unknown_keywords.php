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
            ['slug' => 'unknown_keyword'], // Menargetkan slug pesan tidak dikenal
            [
                'message_scope' => 'both',
                'keywords'      => json_encode([]),
                'name'          => 'Pesan Tidak Dikenal (Fallback)',
                'reply_message' => "Halo! Terima kasih sudah menghubungi kami. 👋\n\nMaaf, sistem kami belum mengenali pesan yang Anda kirimkan. Agar kami dapat membantu dengan cepat, silakan ketik *Menu* untuk melihat daftar layanan mandiri kami. 📑\n\nNamun, jika ada kendala spesifik atau Anda membutuhkan bantuan langsung dari tim Admin, cukup ketik *Hubungi Admin* ya. Kami siap membantu Anda! 😊\n\nSalam hangat,\n_*Shanum Network Fiber*_\n_Pwrd by PT Global Sarana Elektronika_",
                'description'   => 'Dikirim ketika ketikan pelanggan tidak cocok dengan menu bot manapun',
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
        // Opsional: Mengembalikan ke teks kaku semula jika di-rollback
        DB::table('whatsapp_messages')->where('slug', 'unknown_keyword')->update([
            'reply_message' => "Format pesan belum kami pahami.\nGunakan keyword \"Menu\"\n\nJika kamu butuh bantuan, tinggal hubungi CS kami ya 😊\n\nShanum Network Fiber"
        ]);
    }
};
