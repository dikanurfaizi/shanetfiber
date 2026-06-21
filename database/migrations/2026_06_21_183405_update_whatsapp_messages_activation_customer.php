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
        // 1. activation_customer (UP - Kalimat Baru & Hangat)
        DB::table('whatsapp_messages')->updateOrInsert(
            ['slug' => 'activation_customer'],
            [
                'message_scope' => 'registered',
                'name'          => 'Aktivasi Pelanggan Baru (Welcome Message)',
                'reply_message' => "Selamat, %name%! 🎉\n*Customer ID Anda:* %customer_id%\n\nKami menginfokan bahwa akun dan layanan internet Anda saat ini sudah aktif dan siap digunakan. Semoga layanan kami dapat membantu kelancaran aktivitas Anda sehari-hari. 🚀\n\nBerikut rincian layanan Anda:\n• Nama Paket: %package_name%\n• Biaya Bulanan: %package_price%\n• Status Anggota: %member_type%\n\nPembayaran tagihan ke depannya dapat dilakukan secara praktis melalui rekening berikut:\n%account_bank%\n\nAgar kami dapat mengirimkan informasi berkala seperti info tagihan bulanan atau pemeliharaan jaringan langsung ke WhatsApp ini, apakah Anda bersedia menerima notifikasi dari kami?\n\n[ *Cukup balas \"Ya\" jika Anda menyetujui* ] 🙏\n\nJika ada pertanyaan atau membutuhkan bantuan seputar layanan internet Anda, tim CS kami akan dengan senang hati membantu di %cs_contact%.\n\nSalam hangat,\n_*Shanum Network Fiber*_\n_Pwrd by PT Global Sarana Elektronika_",
                'description'   => 'Pesan aktivasi pelanggan baru dengan bahasa yang sopan, hangat, dan menghormati segala usia.',
                'category'      => 'SYSTEM',
                'updated_at'    => now(),
            ]
        );

        // 2. changes_package (UP - Kalimat Baru & Hangat)
        DB::table('whatsapp_messages')->updateOrInsert(
            ['slug' => 'changes_package'],
            [
                'message_scope' => 'registered',
                'name'          => 'Notifikasi Perubahan / Upgrade Paket',
                'reply_message' => "Selamat, %name%! 🎉\n*Customer ID Anda:* %customer_id%\n\nKami menginfokan bahwa permintaan perubahan paket internet Anda telah berhasil diproses dan diperbarui oleh sistem kami.\n\nBerikut adalah rincian perubahan layanan Anda:\n• Paket Sebelumnya: %old_package% (%old_bill%)\n• Paket Baru: %new_package% (%new_bill%)\n\nLayanan baru ini sudah aktif dan siap digunakan untuk mendukung kelancaran aktivitas Anda. 🚀\n\nJika Anda membutuhkan bantuan lebih lanjut atau memiliki kendala seputar perubahan paket ini, silakan hubungi tim Admin kami (%admin%) melalui nomor kontak %cs_contact%.\n\nMohon simpan pesan ini sebagai referensi layanan Anda ke depannya. Terima kasih atas kepercayaan Anda bersama kami. 😊\n\nSalam hangat,\n_*Shanum Network Fiber*_\n_Pwrd by PT Global Sarana Elektronika_",
                'description'   => 'Dikirim otomatis setelah admin atau sistem berhasil memproses perpindahan/upgrade paket internet pelanggan.',
                'category'      => 'SYSTEM',
                'updated_at'    => now(),
            ]
        );

        // 3. info_paket (UP - Kalimat Baru & Hangat)
        DB::table('whatsapp_messages')->updateOrInsert(
            ['slug' => 'info_paket'],
            [
                'message_scope' => 'registered',
                'name'          => 'Informasi Paket Pelanggan',
                'reply_message' => "Halo, %name%! 👋\n*Customer ID Anda:* %customer_id%\n\nBerikut adalah rincian informasi paket internet Anda yang aktif saat ini:\n• Nama Paket: %package%\n• Biaya Bulanan: %price%\n• Status Anggota: %member%\n• Potongan Harga: %discount%\n\nLayanan ini terus berjalan untuk mendukung kelancaran aktivitas Anda. Apakah ada hal lain yang bisa kami bantu seputar paket langganan Anda? 😊\n\nJika butuh bantuan langsung, Anda bisa menghubungi kami via kontak %cs_contact%.\n\nSalam hangat,\n_*Shanum Network Fiber*_\n_Pwrd by PT Global Sarana Elektronika_",
                'description'   => 'Menampilkan detail paket internet yang sedang aktif digunakan oleh pelanggan',
                'category'      => 'INTERACTIVE',
                'updated_at'    => now(),
            ]
        );

        // 4. info_tagihan (UP - Kalimat Baru & Hangat)
        DB::table('whatsapp_messages')->updateOrInsert(
            ['slug' => 'info_tagihan'],
            [
                'message_scope' => 'registered',
                'name'          => 'Informasi Tagihan Pelanggan',
                'reply_message' => "Halo, %name%! 👋\nKami telah melakukan pengecekan pada akun Anda.\n\nBerikut adalah rincian informasi tagihan Anda saat ini:\n%bills%\n*Total Tagihan:* %total-bills%\n\nUntuk kemudahan Anda, pembayaran dapat dilakukan secara praktis melalui transfer ke nomor rekening resmi berikut:\n%account_bank%\n\nApakah ada hal lain yang bisa kami bantu terkait informasi tagihan ini? 😊\n\nJika membutuhkan konfirmasi atau bantuan lebih lanjut, tim kami siap melayani Anda melalui %cs_contact%.\n\nSalam hangat,\n_*Shanum Network Fiber*_\n_Pwrd by PT Global Sarana Elektronika_",
                'description'   => 'Menampilkan rincian tagihan bulanan beserta instruksi rekening pembayaran',
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
        // 1. activation_customer (DOWN - Mengembalikan ke Template Asli Anda)
        DB::table('whatsapp_messages')->updateOrInsert(
            ['slug' => 'activation_customer'],
            [
                'message_scope' => 'both',
                'name'          => 'Aktivasi Pelanggan Baru (Welcome Message)',
                'reply_message' => "*Selamat, Bpk/Ibu %name%!* 🎉\n*Customer ID:* %customer_id%\n\nAkun dan layanan internet Anda sudah aktif dan siap digunakan.\n\nBerikut rincian layanan Anda:\n- Nama Layanan: %package_name%\n- Biaya Layanan: %package_price%\n- Member: %member_type%\n\nPembayaran dapat dilakukan melalui:\n%account_bank%\n\nAgar kami dapat mengirimkan informasi layanan melalui WhatsApp,\napakah Bpk/Ibu bersedia menerima notifikasi tersebut?\n[Cukup balas \"Ya\" untuk menyetujui.]\n\nJika ada pertanyaan atau membutuhkan bantuan, tim CS kami siap membantu 😊\n%cs_contact%\n\nSalam hangat,\n_*Shanum Network Fiber*_\n_Pwrd by PT Global Sarana Elektronika_",
                'updated_at'    => now(),
            ]
        );

        // 2. changes_package (DOWN - Mengembalikan ke Template Asli Anda)
        DB::table('whatsapp_messages')->updateOrInsert(
            ['slug' => 'changes_package'],
            [
                'message_scope' => 'both',
                'name'          => 'Notifikasi Perubahan / Upgrade Paket',
                'reply_message' => "*Yth. Bpk/Ibu* %name%\n*Customer ID:* %customer_id%\n\nPaket internet Anda telah diperbarui.\n\nPerubahan:\n• Paket Sebelumnya: %old_package%\n• Harga Lama: %old_bill%\n• Paket Baru: %new_package%\n• Harga Baru: %new_bill%\n\nAdmin Area:\n• Nama: %admin%\n• Nomor Kontak: %cs_contact%\n\nSimpan informasi ini sebagai referensi anda.\nJika ada kendala, jangan sungkan hubungi kami 😊\n\nSalam hangat,\n_*Shanum Network Fiber*_",
                'updated_at'    => now(),
            ]
        );

        // 3. info_paket (DOWN - Mengembalikan ke Template Asli Anda)
        DB::table('whatsapp_messages')->updateOrInsert(
            ['slug' => 'info_paket'],
            [
                'message_scope' => 'both',
                'name'          => 'Informasi Paket Pelanggan',
                'reply_message' => "Halo %name%, \nAnda terdaftar dengan nomor pelanggan %customer_id%.\n\nBerikut info paket anda saat ini:\n- Nama Paket: %package%\n- Harga Paket: %price%\n- Member: %member%\n- Potongan Harga: %discount%\n\nApakah ada yang bisa kami bantu lagi? 😊\n  \n%cs_contact%  \n_*Shanum Network Fiber*_",
                'updated_at'    => now(),
            ]
        );

        // 4. info_tagihan (DOWN - Mengembalikan ke Template Asli Anda)
        DB::table('whatsapp_messages')->updateOrInsert(
            ['slug' => 'info_tagihan'],
            [
                'message_scope' => 'both',
                'name'          => 'Informasi Tagihan Pelanggan',
                'reply_message' => "Halo %name%, kami sudah cek akun Anda. \n\nBerikut informasi tagihannya.  \n%bills%  \nTotal: %total-bills%\n\nAnda dapat melakukan pembayaran melalui transfer ke nomor rekening berikut:\n%account_bank%\n\nApakah ada yang bisa kami bantu lagi? 😊  \n  \n%cs_contact%  \n_*Shanum Network Fiber*_",
                'updated_at'    => now(),
            ]
        );
    }
};
