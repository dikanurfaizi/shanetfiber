<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Whatsapp\WhatsappMessage;

class WhatsappMessagesSeeder extends Seeder
{
    public function run(): void
    {
        $messages = [

            // =====================================================
            // PAYMENT
            // =====================================================
            [
                'slug'   => 'payment',
                'name'   => 'Payment',
                'incoming_message' => 'payment',
                'reply_message' => "*Kuitansi Pembayaran*
%billing_number%

Yth. Bpk/Ibu %name%
Customer ID: %customer_id%

Rincian Pembayaran:
• Paket: %package%
• Periode: %periode%
• Harga Paket: %bill%
• Potongan Harga: %discount%
• Kategori: %member%
*Total Dibayarkan: %total%*

Status Pembayaran: *LUNAS*
• Teller: _%teller%_
• Tanggal: _%payment_time%_
• Metode: _%payment_methode%_

Pembayaran Anda telah kami terima dengan baik.
Terima kasih atas kepercayaan dan kerja samanya.

Salam hangat,
_*Shanum Network Fiber*_",
                'description' => 'Dikirim otomatis saat pembayaran berhasil tercatat.',
                'default'     => 1,
                'disabled'    => 0,
                'category'    => 'SYSTEM',
                'is_interactive' => 0,
                'keywords' => [],
            ],

            // =====================================================
            // PAYLATER
            // =====================================================
            [
                'slug'   => 'paylater',
                'name'   => 'Paylater',
                'incoming_message' => 'paylater',
                'reply_message' => "*Kuitansi #:*
%billing_number%

*Yth. Bpk/Ibu %name%*
Customer ID: %customer_id%

Berikut rincian pembayaran layanan Anda:
• Paket: %package%
• Periode: %periode%
• Harga Paket: %bill%
• Potongan Harga: %discount%
• Jenis Potongan: %member%

*Status Pembayaran:* Pay Later
• Teller: %teller%
• Batas Waktu Pembayaran: %paylater%

Mohon melakukan pembayaran sebelum batas waktu tersebut agar layanan tetap aktif dan berjalan lancar.
Terima kasih atas kepercayaan Bpk/Ibu menggunakan layanan kami.

Salam hangat,
_*Shanum Network Fiber*_",
                'description' => 'Reminder PayLater (soft window)',
                'default'     => 1,
                'disabled'    => 0,
                'category'    => 'SYSTEM',
                'is_interactive' => 0,
                'keywords' => [],
            ],

            // =====================================================
            // UNPAYMENT
            // =====================================================
            [
                'slug'   => 'unpayment',
                'name'   => 'Unpayment',
                'incoming_message' => 'unpayment',
                'reply_message' => "Yth. Bpk/Ibu %name%
*Customer ID:* %customer_id% 

Kami informasikan bahwa transaksi _%billing_number%_ dibatalkan.
Tenang saja — tidak ada biaya yang dikenakan untuk transaksi ini.

Apabila pembatalan ini tidak Anda lakukan atau ada hal yang ingin dikonfirmasi, silakan hubungi tim CS kami ya 😊
%cs_contact%

Salam hangat,
_*Shanum Network Fiber*_",
                'description' => 'Pembatalan pembayaran.',
                'default'     => 1,
                'disabled'    => 0,
                'category'    => 'SYSTEM',
                'is_interactive' => 1,
                'keywords' => [],
            ],

            // =====================================================
            // UNKNOWN KEYWORD
            // =====================================================
            [
                'slug'   => 'unknown_keyword',
                'name'   => 'Unknown keyword',
                'incoming_message' => 'unknown_keyword',
                'reply_message' => "Format pesan belum kami pahami.  
Gunakan keyword \"Menu\"
 
Jika kamu butuh bantuan, tinggal hubungi CS kami ya 😊  
 
Shanum Network Fiber",
                'description' => 'Auto responder unknown keyword.',
                'default'     => 1,
                'disabled'    => 0,
                'category'    => 'INTERACTIVE',
                'is_interactive' => 1,
                'keywords' => [],
            ],

            // =====================================================
            // UNKNOWN CUSTOMER
            // =====================================================
            [
                'slug'   => 'unknown_customer',
                'name'   => 'Unknown customer',
                'incoming_message' => 'unknown_customer',
                'reply_message' => "Nomor WA kamu belum terdaftar.  
Kalau mau pasang internet, tinggal chat kami 😊
—  
Jika kamu butuh bantuan, tinggal hubungi CS kami ya 😊  
%cs_contact%  
Shanum Network Fiber",
                'description' => 'WA customer tidak dikenal.',
                'default'     => 1,
                'disabled'    => 0,
                'category'    => 'INTERACTIVE',
                'is_interactive' => 1,
                'keywords' => [],
            ],

            // =====================================================
            // NO PAKET
            // =====================================================
            [
                'slug'   => 'no_paket',
                'name'   => 'No paket',
                'incoming_message' => 'no_paket',
                'reply_message' => "Halo %name%, akun Anda belum memiliki paket terdaftar. 
Kalau mau langganan tinggal kabari 😊

Jika anda butuh bantuan, silakan hubungi CS kami ya 😊 
 
%cs_contact%  
_*Shanum Network Fiber*_",
                'description' => 'Customer belum punya paket.',
                'default'     => 1,
                'disabled'    => 0,
                'category'    => 'INTERACTIVE',
                'is_interactive' => 1,
                'keywords' => [],
            ],

            // =====================================================
            // NO BILL
            // =====================================================
            [
                'slug'   => 'no_bill',
                'name'   => 'No bill',
                'incoming_message' => 'no_bill',
                'reply_message' => "Halo %name%, saat ini tidak ada tagihan aktif pada akun Anda.

Jika anda butuh bantuan, silakan hubungi CS kami ya 😊
  
%cs_contact%  
_*Shanum Network Fiber*_",
                'description' => 'Tidak ada tagihan aktif.',
                'default'     => 1,
                'disabled'    => 0,
                'category'    => 'INTERACTIVE',
                'is_interactive' => 1,
                'keywords' => [],
            ],

            // =====================================================
            // INFO TAGIHAN
            // =====================================================
            [
                'slug'   => 'info_tagihan',
                'name'   => 'Info Tagihan',
                'incoming_message' => 'info_tagihan',
                'reply_message' => "Halo %name%, kami sudah cek akun Anda. 

Berikut informasi tagihannya.  
%bills%  
Total: %total-bills%

Anda dapat melakukan pembayaran melalui transfer ke nomor rekening berikut:
%account_bank%

Jika anda butuh bantuan, jangan sungkan hubungi CS kami ya 😊  

%cs_contact%  
_*Shanum Network Fiber*_",
                'description' => 'User minta info tagihan',
                'default'     => 1,
                'disabled'    => 0,
                'category'    => 'INTERACTIVE',
                'is_interactive' => 1,
                'keywords' => [
                    "tagihan",
                    "info tagihan",
                    "cek tagihan",
                    "tagihan saya",
                ],
            ],

            // =====================================================
            // INFO PAKET
            // =====================================================
            [
                'slug'   => 'info_paket',
                'name'   => 'Info Paket',
                'incoming_message' => 'info_paket',
                'reply_message' => "Halo %name%, 
Anda terdaftar dengan nomor pelanggan %customer_id%.

Berikut info paket anda saat ini:
- Nama Paket: %package%
- Harga Paket: %price%
- Member: %member%
- Potongan Harga: %discount%

Jika anda butuh bantuan, jangan sungkan hubungi CS kami ya 😊
  
%cs_contact%  
_*Shanum Network Fiber*_",
                'description' => 'User minta info paket.',
                'default'     => 1,
                'disabled'    => 0,
                'category'    => 'INTERACTIVE',
                'is_interactive' => 1,
                'keywords' => [
                    "paket",
                    "info paket",
                    "cek paket",
                    "paket saya",
                    "langganan",
                ],
            ],

            // =====================================================
            // DEACTIVATION CUSTOMER
            // =====================================================
            [
                'slug'   => 'deactivation_customer',
                'name'   => 'Deactivation customer',
                'incoming_message' => 'deactivation_customer',
                'reply_message' => "Akun kamu sudah dinonaktifkan.
—  
Jika kamu butuh bantuan, tinggal hubungi CS kami ya 😊  
%cs_contact%  
Shanum Network Fiber",
                'description' => 'Akun dinonaktifkan.',
                'default'     => 1,
                'disabled'    => 0,
                'category'    => 'SYSTEM',
                'is_interactive' => 0,
                'keywords' => [],
            ],

            // =====================================================
            // ACTIVATION CUSTOMER
            // =====================================================
            [
                'slug'   => 'activation_customer',
                'name'   => 'Activation customer',
                'incoming_message' => 'activation_customer',
                'reply_message' => "*Selamat, Bpk/Ibu %name%!* 🎉
*Customer ID:* %customer_id%

Akun dan layanan internet Anda sudah aktif dan siap digunakan.

Berikut rincian layanan Anda:
- Nama Layanan: %package_name%
- Biaya Layanan: %package_price%
- Member: %member_type%

Agar kami dapat mengirimkan informasi layanan melalui WhatsApp,
apakah Bpk/Ibu bersedia menerima notifikasi tersebut?
[Cukup balas \"Ya\" untuk menyetujui.]

Jika ada pertanyaan atau membutuhkan bantuan, tim CS kami siap membantu 😊
%cs_contact%

Salam hangat,
_*Shanum Network Fiber*_",
                'description' => 'Customer berhasil diaktivasi.',
                'default'     => 1,
                'disabled'    => 0,
                'category'    => 'SYSTEM',
                'is_interactive' => 0,
                'keywords' => [],
            ],

            // =====================================================
            // ADD PAKET
            // =====================================================
            [
                'slug'   => 'add_paket',
                'name'   => 'Add paket',
                'incoming_message' => 'add_paket',
                'reply_message' => "Paket baru kamu berhasil ditambahkan ✅  
—  
Jika kamu butuh bantuan, tinggal hubungi CS kami ya 😊  
%cs_contact%  
Shanum Network Fiber",
                'description' => 'Paket baru ditambahkan.',
                'default'     => 1,
                'disabled'    => 1,
                'category'    => 'INTERACTIVE',
                'is_interactive' => 1,
                'keywords' => [],
            ],

            // =====================================================
            // ACTIVATION PAKET
            // =====================================================
            [
                'slug'   => 'activation_paket',
                'name'   => 'Activation paket',
                'incoming_message' => 'activation_paket',
                'reply_message' => "Paket kamu sudah aktif 🎉
—  
Jika kamu butuh bantuan, tinggal hubungi CS kami ya 😊  
%cs_contact%  
Shanum Network Fiber",
                'description' => 'Paket berhasil diaktifkan.',
                'default'     => 1,
                'disabled'    => 0,
                'category'    => 'SYSTEM',
                'is_interactive' => 0,
                'keywords' => [],
            ],

            // =====================================================
            // ENABLE PAKET
            // =====================================================
            [
                'slug'   => 'enable_paket',
                'name'   => 'Enable paket',
                'incoming_message' => 'enable_paket',
                'reply_message' => "Paket kamu sudah aktif kembali 😄
—  
Jika kamu butuh bantuan, tinggal hubungi CS kami ya 😊  
%cs_contact%  
Shanum Network Fiber",
                'description' => 'Paket di-enable kembali.',
                'default'     => 1,
                'disabled'    => 1,
                'category'    => 'INTERACTIVE',
                'is_interactive' => 1,
                'keywords' => [],
            ],

            // =====================================================
            // DISABLE PAKET
            // =====================================================
            [
                'slug'   => 'disable_paket',
                'name'   => 'Disable paket',
                'incoming_message' => 'disable_paket',
                'reply_message' => "Paket kamu dinonaktifkan.
—  
Jika kamu butuh bantuan, tinggal hubungi CS kami ya 😊  
%cs_contact%  
Shanum Network Fiber",
                'description' => 'Paket dinonaktifkan.',
                'default'     => 1,
                'disabled'    => 1,
                'category'    => 'INTERACTIVE',
                'is_interactive' => 1,
                'keywords' => [],
            ],

            // =====================================================
            // REGISTER CUSTOMER
            // =====================================================
            [
                'slug'   => 'register_customer',
                'name'   => 'Register customer',
                'incoming_message' => 'register_customer',
                'reply_message' => "Registrasi berhasil!  
Akan segera kami proses ya 😊
—  
Jika kamu butuh bantuan, tinggal hubungi CS kami ya 😊  
%cs_contact%  
Shanum Network Fiber",
                'description' => 'Registrasi customer.',
                'default'     => 1,
                'disabled'    => 1,
                'category'    => 'INTERACTIVE',
                'is_interactive' => 1,
                'keywords' => [],
            ],

            // =====================================================
            // CREATE NEW BILL
            // =====================================================
            [
                'slug'   => 'create_new_bill',
                'name'   => 'Create new bill',
                'incoming_message' => 'create_new_bill',
                'reply_message' => "*Yth. Bpk/Ibu* %name%
*Customer ID:* %customer_id%

Kami informasikan bahwa periode pembayaran terbaru tersedia.
Berikut rinciannya:
%packages%
*Total:* %total-bill%

Silakan melakukan pembayaran sesuai metode yang tersedia.
Jika perlu bantuan, tim CS siap membantu 😊
%cs_contact%

Salam hangat,
_*Shanum Network Fiber*_",
                'description' => 'Billing period baru.',
                'default'     => 1,
                'disabled'    => 0,
                'category'    => 'BROADCAST',
                'is_interactive' => 0,
                'keywords' => [],
            ],

            // =====================================================
            // ORDER CREATED
            // =====================================================
            [
                'slug'   => 'order_created',
                'name'   => 'Order Created',
                'incoming_message' => 'order_created',
                'reply_message' => "*Yth. Bpk/Ibu* %name%
*Customer ID:* %customer_id%

Berikut rincian pembayaran layanan Anda:
*Nomor Pembayaran:* %billing_number%
%payment_details%

Silakan selesaikan pembayaran.
%cs_contact%

Salam hangat,
_*Shanum Network Fiber*_",
                'description' => 'Order created.',
                'default'     => 1,
                'disabled'    => 0,
                'category'    => 'SYSTEM',
                'is_interactive' => 0,
                'keywords' => [],
            ],

            // =====================================================
            // CHANGES PACKAGE
            // =====================================================
            [
                'slug'   => 'changes_package',
                'name'   => 'Changes package',
                'incoming_message' => 'changes_package',
                'reply_message' => "*Yth. Bpk/Ibu* %name%
*Customer ID:* %customer_id%

Paket internet Anda telah diperbarui.

Perubahan:
• Paket Sebelumnya: %old_package%
• Harga Lama: %old_bill%
• Paket Baru: %new_package%
• Harga Baru: %new_bill%

Admin Area:
• Nama: %admin%
• Nomor Kontak: %cs_contact%

Salam hangat,
_*Shanum Network Fiber*_",
                'description' => 'Customer change package.',
                'default'     => 1,
                'disabled'    => 0,
                'category'    => 'SYSTEM',
                'is_interactive' => 0,
                'keywords' => [],
            ],

            // =====================================================
            // TEST MESSAGE
            // =====================================================
            [
                'slug'   => 'test_message',
                'name'   => 'Test Message',
                'incoming_message' => 'test_message',
                'reply_message' => "Halo 👋 %testing_note%.",
                'description' => 'Untuk testing gateway.',
                'default'     => 1,
                'disabled'    => 0,
                'category'    => 'SYSTEM',
                'is_interactive' => 0,
                'keywords' => [],
            ],

            // =====================================================
            // MENU
            // =====================================================
            [
                'slug'   => 'menu',
                'name'   => 'Menu',
                'incoming_message' => 'menu',
                'reply_message' => "Halo %name%, selamat datang di *Shanum Network Fiber!* 🌐
Untuk memilih menu, silakan ketik kata kunci berikut:

* TAGIHAN → Info tagihan
* PAKET → Info paket langganan
* GANGGUAN → Lapor kendala
* ADMIN → Hubungi admin

Tuliskan salah satu kata di atas. Saya siap bantu! 😊",
                'description' => 'User mengetik Menu.',
                'default'     => 1,
                'disabled'    => 0,
                'category'    => 'INTERACTIVE',
                'is_interactive' => 0,
                'keywords' => [
                    "menu",
                    "help",
                    "bantuan",
                    "mulai",
                    "info",
                ],
            ],

            // =====================================================
            // STOP
            // =====================================================
            [
                'slug'   => 'stop',
                'name'   => 'Stop',
                'incoming_message' => 'stop',
                'reply_message' => "Halo %name%,

Terima kasih atas konfirmasi Anda.
Sesuai permintaan, kami telah menghentikan pengiriman pesan promosi kepada Anda.

Untuk informasi layanan lainnya, ketik \"Menu\".

Salam,
_*Shanum Network Fiber*_",
                'description' => 'Keyword STOP',
                'default'     => 1,
                'disabled'    => 0,
                'category'    => 'INTERACTIVE',
                'is_interactive' => 1,
                'keywords' => [
                    "stop",
                    "berhenti",
                    "unsubscribe",
                ],
            ],
        ];

        foreach ($messages as $msg) {
            WhatsappMessage::updateOrInsert(
                ['slug' => $msg['slug']],
                [
                    ...$msg,
                    'keywords' => json_encode($msg['keywords'] ?? []),
                ]
            );
        }
    }
}
