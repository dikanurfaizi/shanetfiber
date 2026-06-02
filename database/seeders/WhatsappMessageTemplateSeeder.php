<?php

namespace Database\Seeders;

use App\Enums\WhatsAppAction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WhatsappMessageTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $slug = WhatsAppAction::PAYMENT_CHANNEL_UPDATE->value;

        // Isi template mentah dengan placeholder utuh
        $messageTemplate = "Halo %customer_name%,\n\n"
            . "Kami ingin menginformasikan bahwa per tanggal %effective_date%, pembayaran untuk layanan %services% dialihkan ke %account_total% rekening resmi terbaru kami berikut ini:\n\n"
            . "%account_list%\n\n"
            . "Mohon lakukan pembayaran ke salah satu rekening di atas untuk transaksi selanjutnya. Jika Anda memiliki pertanyaan atau kendala, silakan hubungi tim dukungan kami dengan membalas pesan ini atau melalui WhatsApp ke nomor %whatsapp_number%.\n\n"
            . "Terima kasih atas perhatian dan kerja samanya.";

        // Menggunakan updateOrInsert untuk mencegah duplicate entry jika seeder dijalankan ulang
        DB::table('whatsapp_messages')->updateOrInsert(
            ['slug' => $slug], // Kunci unik pencarian
            [
                'message_scope'    => 'registered',
                'keywords'         => json_encode([]), // Default json array kosong
                'name'             => 'Broadcast Payment Channel Update',
                'incoming_message' => null,
                'reply_message'    => $messageTemplate,
                'description'      => 'Template pesan blast dinamis ketika ada perubahan data channel bank/ewallet milik admin',
                'default'          => 1,
                'disabled'         => 0,
                'category'         => 'BROADCAST', // Menggunakan enum BROADCAST
                'is_interactive'   => 0,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]
        );
    }
}
