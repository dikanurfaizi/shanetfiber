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
        $newTemplate = "Selamat, %name%! 🎉\n"
            . "*Customer ID Anda:* %customer_id%\n\n"
            . "Kami menginfokan bahwa akun dan layanan internet Anda saat ini sudah aktif dan siap digunakan.\n\n"
            . "Berikut rincian layananAnda:\n"
            . "• Nama Paket: %package_name%\n"
            . "• Biaya Bulanan: %package_price%\n"
            . "• Status Anggota: %member_type%\n\n"
            . "Tagihan awal yang perlu dibayar saat ini:\n"
            . "• Metode Hitung: %calculation_method%\n"
            . "• *Nominal:* *%grand_total%*\n\n"
            . "Pembayaran tagihan dapat dilakukan secara praktis melalui rekening berikut:\n"
            . "%account_bank%\n\n"
            . "_Mohon kirimkan bukti transfer ke nomor ini setelah melakukan pembayaran_\n\n"
            . "Agar kami dapat mengirimkan informasi berkala seperti info tagihan bulanan atau pemeliharaan jaringan langsung ke WhatsApp ini, apakah Anda bersedia menerima notifikasi dari kami?\n\n"
            . "[ *Cukup balas \"Ya\" jika Anda menyetujui* ] 🙏\n\n"
            . "Jika ada pertanyaan atau membutuhkan bantuan seputar layanan internet Anda, tim CS kami akan dengan senang hati membantu di %cs_contact%.\n\n"
            . "Salam hangat,\n"
            . "_*Shanum Network Fiber*_\n"
            . "_Pwrd by PT. Global Sarana Elektronik_";

        DB::table('whatsapp_messages')
            ->where('slug', 'activation_customer')
            ->update([
                'reply_message'    => $newTemplate,
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $oldTemplate = "Selamat, %name%! 🎉\n"
            . "*Customer ID Anda:* %customer_id%\n\n"
            . "Kami menginfokan bahwa akun dan layanan internet Anda saat ini sudah aktif dan siap digunakan. Semoga layanan kami dapat membantu kelancaran aktivitas Anda sehari-hari. 🚀\n\n"
            . "Berikut rincian layanan Anda:\n"
            . "• Nama Paket: %package_name%\n"
            . "• Biaya Bulanan: %package_price%\n"
            . "• Status Anggota: %member_type%\n\n"
            . "Pembayaran tagihan ke depannya dapat dilakukan secara praktis melalui rekening berikut:\n"
            . "%account_bank%\n\n"
            . "Agar kami dapat mengirimkan informasi berkala seperti info tagihan bulanan atau pemeliharaan jaringan langsung ke WhatsApp ini, apakah Anda bersedia menerima notifikasi dari kami?\n\n"
            . "[ *Cukup balas \"Ya\" jika Anda menyetujui* ] 🙏\n\n"
            . "Jika ada pertanyaan atau membutuhkan bantuan seputar layanan internet Anda, tim CS kami akan dengan senang hati membantu di %cs_contact%.\n\n"
            . "Salam hangat,\n"
            . "_*Shanum Network Fiber*_\n"
            . "_Pwrd by PT. Global Sarana Elektronik_";

        DB::table('whatsapp_messages')
            ->where('slug', 'activation_customer')
            ->update([
                'reply_message'    => $oldTemplate,
                'updated_at' => now(),
            ]);
    }
};
