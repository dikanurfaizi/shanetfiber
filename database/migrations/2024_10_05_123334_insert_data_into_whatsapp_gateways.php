<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class InsertDataIntoWhatsappGateways extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Mengubah panjang kolom message menjadi 5000
        Schema::table('whatsapp_gateways', function (Blueprint $table) {
            // Mengubah panjang kolom message menjadi 5000
            $table->string('message', 5000)->change();
            // Mengubah tipe kolom schedule_time menjadi time
            $table->time('schedule_time')->change();
        });

        // Menyisipkan data baru ke dalam tabel whatsapp_gateways
        DB::table('whatsapp_gateways')->insert([
            [
                'id' => 1,
                'name' => 'Payment Bills Notification',
                'status' => 'active',
                'description' => 'Send Payment Invoices Via WhatsApp On Schedule',
                'schedule_date' => '1',
                'schedule_time' => '06:00:00', // Format waktu HH:mm:ss
                'message' => "*Subject: Pengingat Pembayaran*\n\nYth. _Pelanggan Setia SHANET FIBER_,\n\nKami berharap Anda dalam keadaan baik. Kami ingin mengingatkan bahwa tagihan Anda telah *jatuh tempo pada hari ini*.\n\nKami sangat menghargai jika pembayaran dapat segera diselesaikan. Apabila Anda sudah melakukan pembayaran, mohon abaikan pesan ini, dan kami ucapkan terima kasih atas kerjasamanya.\n\nJika ada pertanyaan atau kendala terkait pembayaran, jangan ragu untuk menghubungi kami. Kami siap membantu.\n\nTerima kasih atas perhatian dan pengertiannya.\n\nHormat kami,\n*SHANET FIBER*",
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Promotional Broadcasts',
                'status' => 'active',
                'description' => 'Send Promotional Broadcasts Via WhatsApp',
                'schedule_date' => '15',
                'schedule_time' => '12:12:12', // Format waktu HH:mm:ss
                'message' => "Promo Menarik Paket Internet RT/RW:\r\n\r\nHalo, Warga RT/RW yang Terhormat!\r\n\r\nKami punya kabar gembira untuk Anda! Saat ini, kami sedang menawarkan Promo Spesial Paket Internet RT/RW yang akan membuat seluruh keluarga dan tetangga di lingkungan Anda tetap terhubung dengan kecepatan tinggi dan harga yang sangat terjangkau!\r\n\r\nDengan berlangganan Paket Internet RT/RW, Anda akan mendapatkan:\r\n\r\n- Kecepatan hingga 100 Mbps untuk pengalaman browsing, streaming, dan gaming tanpa gangguan!\r\n- Kuota Unlimited tanpa batasan, cocok untuk seluruh anggota keluarga!\r\n- Gratis pemasangan dan modem Wi-Fi untuk setiap rumah tangga yang berlangganan.\r\n- Harga mulai dari Rp 200.000/bulan, semakin murah jika semakin banyak tetangga yang bergabung!\r\n- Jangan lewatkan kesempatan ini untuk meningkatkan kenyamanan internet di lingkungan Anda! Ayo, ajak tetangga dan teman-teman satu RT/RW untuk bergabung dan rasakan pengalaman internet cepat bersama.\r\n\r\nUntuk informasi lebih lanjut dan pendaftaran, hubungi kami di [Nomor Kontak/WhatsApp] atau kunjungi [Website Anda].\r\n\r\nMari bersama-sama membangun lingkungan yang lebih terhubung dan modern! 🌐\r\n\r\nSalam hangat,\r\nSHANET FIBER\r\n\r\n_PS: Semakin banyak tetangga yang bergabung, semakin besar diskon yang bisa Anda dapatkan. Jadi, ajak semua orang di lingkungan Anda sekarang juga!_",
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Hapus data jika perlu, tergantung pada kebutuhan Anda
        DB::table('whatsapp_gateways')->where('id', 1)->delete();
        DB::table('whatsapp_gateways')->where('id', 2)->delete();
    }
}
