<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TelegramMessageSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('telegram_messages')->insert([
            [
                'slug' => 'unknow_keyword',
                'name' => 'Unknow Keyword',
                'incoming_message' => 'unknow keyword',
                'reply_message' => "Mohon maaf, keywoard yang anda masukkan salah\n\nKeyword yang tersedia:\n%keywords%",
                'description' => 'Pesan yang dikirim saat pesan yang diterima tidak ada dalam keyword.',
                'default' => true,
                'disabled' => false,
            ],
            [
                'slug' => 'payment',
                'name' => 'Pembayaran Lunas',
                'incoming_message' => 'payment',
                'reply_message' => "*Kuitansi Pembayaran*\r\n%billing_number%\r\n\r\nYth. Bpk/Ibu %name%\r\nCustomer ID: %customer_id%\r\n\r\nRincian Pembayaran:\r\n• Paket: %package%\r\n• Periode: %periode%\r\n• Harga Paket: %bill%\r\n• Potongan Harga: %discount%\r\n• Jenis Potongan: %member%\r\n• Total Dibayarkan: %total%\r\n\r\nStatus Pembayaran: *LUNAS*\r\n• Teller: _%teller%_\r\n• Tanggal: _%payment_time%_\r\n• Metode: _%payment_methode%_\r\n\r\nPembayaran Anda sudah kami terima.\r\nJika ada pertanyaan, silakan hubungi layanan kami.\r\n\r\nTerima kasih,\r\n_*Shanum Network Fiber*_",
                'description' => 'Pesan yang dikirim saat pelanggan melakukan pembayaran.',
                'default' => true,
                'disabled' => false,
            ],
            [
                'slug' => 'unknow_customer',
                'name' => 'Non Customer',
                'incoming_message' => 'unknow customer',
                'reply_message' => "Mohon maaf, anda belum terdaftar sebagai customer kami.\r\nSilahkan menghubungi 0856-4233-6774 untuk proses registrasi.",
                'description' => 'Pesan yang dikirim bukan customer.',
                'default' => true,
                'disabled' => false,
            ],
            [
                'slug' => 'unpayment',
                'name' => 'Unpayment',
                'incoming_message' => 'unpayment',
                'reply_message' => "Yth. Bpk/Ibu %name%,\r\nCustomer ID: %customer_id%\r\n\r\nMaaf telah terjadi kesalahan system.\r\n\r\nPembayaran anda:\r\nNomor Tagihan: *%billing_number%*\r\nJumlah: *%bill%*\r\n\r\nTelah dibatalkan.\r\n\r\nJika ada pertanyaan, silakan hubungi layanan kami.\r\n\r\nTerima kasih,\r\n_*Shanum Network Fiber*_",
                'description' => 'Pesan yang dikirim saat admin melakukan pembatalan pembayaran.',
                'default' => true,
                'disabled' => false,
            ],
            [
                'slug' => 'no_paket',
                'name' => 'Customer Dont Have Paket',
                'incoming_message' => 'no paket',
                'reply_message' => "Yth. Bpk/Ibu %name%,\r\n%address%\r\nCustomer ID: %customer_id%\r\n\r\nSaat ini anda sudah terdaftar di sistem kami, namun belum mempunyai paket.\r\n\r\nJika anda ingin menggunakan layanan kami, silahkan hubungi 0856-4233-6774.\r\n\r\n*Shanum Network Fiber*\r\nJl. Sukamaju No. 49 RT 009/007\r\nCiseureuh, Purwakarta\r\n\r\n*Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini.*",
                'description' => 'Pesan yang dikirim saat pelanggan tidak memiliki paket internet.',
                'default' => true,
                'disabled' => false,
            ],
            [
                'slug' => 'no_bill',
                'name' => 'No Bill',
                'incoming_message' => 'no bill',
                'reply_message' => "Yth. Bpk/Ibu %name%,\n%address%\nCustomer ID: %customer_id%\n\nSaat ini anda belum memiliki tagihan.\n\nKami mengucapkan terima kasih karena anda tertib administrasi.\nIni sangat berguna untuk kemajuan kami dalam memperbaiki layanan.\n\n*Shanum Network Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini.*",
                'description' => 'Pesan yang dikirim saat pelanggan tidak memiliki tagihan.',
                'default' => true,
                'disabled' => false,
            ],
            [
                'slug' => 'reminder_bill',
                'name' => 'Reminder Bill',
                'incoming_message' => 'warning bill',
                'reply_message' => "Yth. Bpk/Ibu %name%,\r\n%address%\r\n\r\nDalam %day% hari, batas akhir pembayaran paket internet Anda akan tiba. Saat ini, Anda memiliki %count-bill% tagihan yang belum dibayar:\r\n\r\n%packages%\r\n*💰 Total tagihan:* %total-bill%\r\n\r\nLakukan pembayaran sebelum jatuh tempo untuk menghindari isolir otomatis oleh system kami.\r\n\r\n💳 Anda dapat melakukan pembayaran melalui website _www.shanetfiber.co.id_ atau Admin kami:\r\n%account_bank%\r\n\r\n📌 Silakan informasikan kepada kami jika Anda sudah melakukan pembayaran melalui _WhatsApp Admin_ di nomor %whatsapp_number%\r\n\r\nTerima kasih telah menggunakan layanan internet kami.\r\n\r\n*Shanum Network Fiber*\r\n📍 Jl. Sukamaju No. 49 RT 009/007, Ciseureuh, Purwakarta\r\n\r\n📩 *_Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini._*",
                'description' => 'Pesan reminder tagihan sebelum jatuh tempo.',
                'default' => true,
                'disabled' => false,
            ],
            [
                'slug' => 'paylater',
                'name' => 'Pay Later',
                'incoming_message' => 'paylater',
                'reply_message' => "*Receipt #:*\r\n%billing_number%  \r\n\r\nYth. Bpk/Ibu %name%,\r\n*Cust - ID:* _%customer_id%_  \r\n\r\nRIncian Pembayaran:\r\n• *Paket:* _%package%_\r\n• *Periode:* _%periode%_\r\n• *Harga Paket:* *_%bill%_*\r\n• *Potongan Harga:* _%discount%_  \r\n• *Jenis Potongan:* _%member%_ \r\n• *Total Dibayarkan:* *0*  \r\n\r\nStatus Pembayaran: PAY LETER  \r\nTeller: _%teller%_  \r\nMetode Pembayaran: _%payment_methode%_\r\nJatuh Tempo: *%paylater%*\r\n\r\nMohon dapat membayar tagihan tepat waktu guna menghindari terisolirnya akun Anda,\r\n\r\nTerima Kasih,\r\n_*Shanum Network Fiber*_",
                'description' => 'Pesan yang dikirim saat permintaan pay later.',
                'default' => true,
                'disabled' => false,
            ],
        ]);
    }
}
