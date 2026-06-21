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
        // 1. Template: info_tagihan (Bagi yang memiliki tunggakan BL / PL)
        DB::table('whatsapp_messages')->updateOrInsert(
            ['slug' => 'info_tagihan'],
            [
                'message_scope' => 'registered',
                'name'          => 'Informasi Tagihan Aktif',
                'reply_message' => "Halo, %name%! 👋\nKami telah melakukan pengecekan pada akun Anda.\n\nBerikut adalah rincian informasi tagihan yang belum terselesaikan saat ini:\n\n%bills%\n\n*Total Tagihan Anda:* %total-bills%\n\nUntuk kemudahan Anda, pembayaran dapat dilakukan melalui transfer ke nomor rekening resmi berikut:\n%account_bank%\n\nApakah ada hal lain yang bisa kami bantu terkait informasi tagihan ini? 😊\n\nJika membutuhkan konfirmasi atau bantuan lebih lanjut, silakan hubungi kami melalui %cs_contact%.\n\nSalam hangat,\n_*Shanum Network Fiber*_\n_Pwrd by PT Global Sarana Elektronika_",
                'description'   => 'Menampilkan daftar tagihan bulanan (BL/PL) yang belum dibayar beserta status jatuh temponya.',
                'category'      => 'INTERACTIVE',
                'updated_at'    => now(),
            ]
        );

        // 2. Template: info_lunas (Bagi yang sudah membayar tagihan terakhir / tidak ada tunggakan)
        DB::table('whatsapp_messages')->updateOrInsert(
            ['slug' => 'info_lunas'],
            [
                'message_scope' => 'registered',
                'name'          => 'Informasi Tagihan Lunas',
                'reply_message' => "Halo, %name%! 👋\nTerima kasih banyak ya, saat ini akun Anda aman dan tidak ada tagihan yang tertunggak. 😊\n\nBerikut adalah rincian riwayat pembayaran terakhir Anda:\n• Nama Paket: %package%\n• Periode Layanan: %periode%\n• Total Pembayaran: %total%\n• Waktu Pembayaran: %paid_at%\n• Metode Pembayaran: %method%\n\nLayanan internet Anda berjalan normal untuk mendukung kelancaran aktivitas Anda sehari-hari. Apakah ada hal lain yang bisa kami bantu? 🙏\n\nJika ada pertanyaan, tim CS kami siap melayani Anda melalui %cs_contact%.\n\nSalam hangat,\n_*Shanum Network Fiber*_\n_Pwrd by PT Global Sarana Elektronika_",
                'description'   => 'Dikirim ketika pelanggan mengecek tagihan namun semua tagihannya sudah berstatus lunas (LS).',
                'category'      => 'INTERACTIVE',
                'updated_at'    => now(),
            ]
        );

        // 3. Template: no_bill (Bagi pelanggan baru yang belum memiliki cetak tagihan bulanan)
        DB::table('whatsapp_messages')->updateOrInsert(
            ['slug' => 'no_bill'],
            [
                'message_scope' => 'registered',
                'name'          => 'Informasi Belum Ada Tagihan',
                'reply_message' => "Halo, %name%! 👋\nKami menginfokan bahwa saat ini belum ada data tagihan bulanan yang diterbitkan untuk akun Anda.\n\nJika Anda merupakan pelanggan baru yang baru saja melakukan aktivasi layanan pada bulan ini, tagihan pertama Anda akan diterbitkan otomatis pada siklus periode berikutnya. 👍\n\nApakah ada hal lain yang bisa kami bantu seputar layanan internet Anda? Jika membutuhkan informasi lebih lanjut, silakan hubungi kami di %cs_contact%.\n\nSalam hangat,\n_*Shanum Network Fiber*_\n_Pwrd by PT Global Sarana Elektronika_",
                'description'   => 'Dikirim ketika pelanggan baru (baru aktivasi) mengecek tagihan tetapi belum ada bill bulanan yang rilis.',
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
        // 1. Rollback Template: info_tagihan
        DB::table('whatsapp_messages')->updateOrInsert(
            ['slug' => 'info_tagihan'],
            [
                'message_scope' => 'registered',
                'name'          => 'Informasi Tagihan Aktif',
                'reply_message' => "Halo, %name%! 👋\nKami telah melakukan pengecekan pada akun Anda.\n\nBerikut adalah rincian informasi tagihan Anda saat ini:\n%bills%\n*Total Tagihan:* %total-bills%\n\nUntuk kemudahan Anda, pembayaran dapat dilakukan secara praktis melalui transfer ke nomor rekening resmi berikut:\n%account_bank%\n\nApakah ada hal lain yang bisa kami bantu terkait informasi tagihan ini? 😊\n\nJika membutuhkan konfirmasi atau bantuan lebih lanjut, tim kami siap melayani Anda melalui %cs_contact%.\n\nSalam hangat,\n_*Shanum Network Fiber*_\n_Pwrd by PT Global Sarana Elektronika_",
                'category'      => 'INTERACTIVE',
                'updated_at'    => now(),
            ]
        );

        // 2. Rollback Template: info_lunas
        DB::table('whatsapp_messages')->updateOrInsert(
            ['slug' => 'info_lunas'],
            [
                'message_scope' => 'registered',
                'name'          => 'Informasi Tagihan Lunas',
                'reply_message' => "Halo, %name%! 👋\n*Customer ID Anda:* %customer_id%\n\nBerikut adalah rincian informasi paket internet Anda yang aktif saat ini:\n• Nama Paket: %package%\n• Biaya Bulanan: %price%\n• Status Anggota: %member%\n• Potongan Harga: %discount%\n\nLayanan ini terus berjalan untuk mendukung kelancaran aktivitas Anda. Apakah ada hal lain yang bisa kami bantu seputar paket langganan Anda? 😊\n\nJika butuh bantuan langsung, Anda bisa menghubungi kami via kontak %cs_contact%.\n\nSalam hangat,\n_*Shanum Network Fiber*_\n_Pwrd by PT Global Sarana Elektronika_",
                'category'      => 'INTERACTIVE',
                'updated_at'    => now(),
            ]
        );

        // 3. Rollback Template: no_bill
        DB::table('whatsapp_messages')->updateOrInsert(
            ['slug' => 'no_bill'],
            [
                'message_scope' => 'registered',
                'name'          => 'Informasi Belum Ada Tagihan',
                'reply_message' => "Halo %name%, saat ini tidak ada tagihan aktif pada akun Anda.\n\nApakah ada yang bisa kami bantu lagi? 😊\n  \n%cs_contact%  \n_*Shanum Network Fiber*_",
                'category'      => 'INTERACTIVE',
                'updated_at'    => now(),
            ]
        );
    }
};
