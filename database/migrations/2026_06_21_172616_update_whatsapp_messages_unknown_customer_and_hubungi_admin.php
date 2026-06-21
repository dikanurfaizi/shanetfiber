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
            ['slug' => 'unknown_customer'],
            [
                'message_scope' => 'unregistered',
                'keywords'      => json_encode(["menu", "hai", "halo", "hello"]),
                'name'          => 'Pelanggan Tidak Terdaftar (Greeting)',
                'reply_message' => "Halo! Terima kasih sudah menghubungi kami. 👋\n\nNomor WhatsApp Anda belum terdaftar sebagai pelanggan resmi Shanum Network Fiber. Namun jangan khawatir, kami tetap siap melayani Anda! 😊\n\n• Jika Anda ingin melihat daftar paket internet terbaik dari kami, silakan ketik *Info Paket*.\n• Jika Anda ingin bertanya lebih lanjut atau melakukan pendaftaran pasang baru, silakan ketik *Hubungi Admin*.\n\nSalam hangat,\n_*Shanum Network Fiber*_\n_Pwrd by PT Global Sarana Elektronika_",
                'description'   => 'Pesan otomatis ketika nomor baru/belum terdaftar mengirimkan pesan ke bot',
                'category'      => 'INTERACTIVE',
                'updated_at'    => now(),
            ]
        );

        DB::table('whatsapp_messages')->updateOrInsert(
            ['slug' => 'hubungi_admin'],
            [
                'message_scope' => 'both',
                'keywords'      => json_encode(['hubungi', 'admin', 'hubungi admin']),
                'name'          => 'Hubungi Admin / Alihkan ke CS',
                'reply_message' => "Baik, mohon tunggu sebentar ya. Sesi otomatis telah dijeda dan Anda sedang kami hubungkan langsung dengan tim Admin/CS kami. 👨‍💻\n\nAgar kami dapat membantu dengan lebih cepat, silakan sampaikan pertanyaan atau kendala Anda terlebih dahulu di bawah ini. Terima kasih! 😊\n\nSalam hangat,\n_*Shanum Network Fiber*_\n_Pwrd by PT Global Sarana Elektronika_",
                'description'   => 'Pesan konfirmasi ketika pelanggan meminta bantuan manusia (pindah ke mode human)',
                'category'      => 'INTERACTIVE',
                'updated_at'    => now(),
            ]
        );

        DB::table('whatsapp_messages')->updateOrInsert(
            ['slug' => 'unknown_call_admin'],
            [
                'message_scope' => 'unregistered',
                'keywords'      => json_encode(['hubungi', 'admin', 'hubungi admin']),
                'name'          => 'Hubungi Admin (Pelanggan Baru)',
                // Isi reply_message disamakan persis agar seragam
                'reply_message' => "Baik, mohon tunggu sebentar ya. Sesi otomatis telah dijeda dan Anda sedang kami hubungkan langsung dengan tim Admin/CS kami. 👨‍💻\n\nAgar kami dapat membantu dengan lebih cepat, silakan sampaikan pertanyaan atau kendala Anda terlebih dahulu di bawah ini. Terima kasih! 😊\n\nSalam hangat,\n_*Shanum Network Fiber*_\n_Pwrd by PT Global Sarana Elektronika_",
                'description'   => 'Pesan konfirmasi ketika nomor tidak dikenal langsung mengetik hubungi admin',
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
            ['slug' => 'unknown_customer'],
            [
                'message_scope' => 'unregistered',
                'keywords'      => json_encode([]),
                'name'          => 'Pelanggan Tidak Terdaftar (Greeting)',
                'reply_message' => "Nomor WA kamu belum terdaftar sebagai pelanggan Shanum Network Fiber. 😊 \n\nKalau kamu ingin lihat paket internet kami, ketik *info paket* ya. \nKalau ingin bertanya atau daftar pemasangan, ketik *hubungi admin*. \n\n_*Shanum Network Fiber*_",
                'description'   => 'Pesan otomatis ketika nomor baru/belum terdaftar mengirimkan pesan ke bot',
                'category'      => 'INTERACTIVE',
                'updated_at'    => now(),
            ]
        );

        DB::table('whatsapp_messages')->updateOrInsert(
            ['slug' => 'hubungi_admin'],
            [
                'message_scope' => 'both',
                'keywords'      => json_encode(['hubungi', 'admin', 'hubungi admin']),
                'name'          => 'Hubungi Admin / Alihkan ke CS',
                'reply_message' => "Baik! Untuk bantuan lebih lanjut kamu bisa chat admin kami.  \nAdmin akan membantu atau menjawab pertanyaan kamu. \n\nSilakan tunggu sebentar, kami hubungkan dengan admin. 😊",
                'description'   => 'Pesan konfirmasi ketika pelanggan meminta bantuan manusia (pindah ke mode human)',
                'category'      => 'INTERACTIVE',
                'updated_at'    => now(),
            ]
        );

        DB::table('whatsapp_messages')->updateOrInsert(
            ['slug' => 'unknown_call_admin'],
            [
                'message_scope' => 'unregistered',
                'keywords'      => json_encode(['hubungi', 'admin', 'hubungi admin']),
                'name'          => 'Hubungi Admin (Pelanggan Baru)',
                // Isi reply_message disamakan persis agar seragam
                'reply_message' => "Baik! Untuk bantuan lebih lanjut kamu bisa chat admin kami.  \nAdmin akan membantu proses pemasangan atau menjawab pertanyaan kamu. \n\nSilakan tunggu sebentar, kami hubungkan dengan admin. 😊",
                'description'   => 'Pesan konfirmasi ketika nomor tidak dikenal langsung mengetik hubungi admin',
                'category'      => 'INTERACTIVE',
                'updated_at'    => now(),
            ]
        );
    }
};
