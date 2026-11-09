<?php

namespace Database\Seeders;

use App\Models\Whatsapp\WhatsappMessage;
use Illuminate\Database\Seeder;

class WhatsappMessageTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pesan_pembayaran_lunas = '===============================
         SHANUM NETWORK FIBER
          Receipt # : %billing_number%
===============================

Pelanggan yang terhormat, Berikut kami sampaikan detail pembayaran anda:

*👥 Nama:* _Bpk/Ibu %name%_
*🆔 Cust - ID:* _%customer_id%_
*📍 Alamat:* _%address%_
*📦 Paket:* _%paket%_
*📆 Periode:* _%periode%_
*💶 Nominal:* *_%bill%_*

*Teller:* _%teller%_
*Tanggal:* _%payment_time%_
*Metode pembayaran:* _%payment_methode%_

===============================
Terima kasih telah mempercayakan kebutuhan internetnya kepada kami.
Kami berkomitmen untuk selalu memberikan yang terbaik.
Mohon maaf jika ada salah penulisan nama, Anda dapat menghubungi kami untuk pembetulan.
===============================
            *Shanum Network Fiber*
_Pwrd by PT. Global Sarana Elektronika_
===============================';

        $pay_later = '===============================
         SHANUM NETWORK FIBER
          Receipt # : %billing_number%
===============================

Pelanggan yang terhormat, Berikut kami sampaikan detail pembayaran mundur anda:

*👥 Nama:* _Bpk/Ibu %name%_
*🆔 Cust - ID:* _%customer_id%_
*📍 Alamat:* _%address%_
*📦 Paket:* _%paket%_
*📆 Periode:* _%periode%_
*💶 Nominal:* *_%bill%_*

*Teller:* _%teller%_
*Metode pembayaran:* _%payment_methode%_
*Jatuh Tempo:* *%paylater%*

===============================
Mohon agar membayar tagihan tepat waktu guna menghindari
terisolirnya akun Anda, Terima Kasih.
===============================
            *Shanum Network Fiber*
_Pwrd by PT. Global Sarana Elektronika_
===============================';

        $pesan_unpayment = 'Yth. Bpk/Ibu %name%,
%address%
Customer ID: %customer_id%

Maaf telah terjadi kesalahan system.

Pembayaran anda:
Nomor Tagihan: *%billing_number%*
Jumlah: *%bill%*

Telah dibatalkan.

Terima kasih telah menggunakan layanan internet kami.

*Shanum Network Fiber*
Jl. Sukamaju No. 49 RT 009/007
Ciseureuh, Purwakarta

*_Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini._*';

        $pesan_no_keyword = 'Mohon maaf, keywoard yang anda masukkan salah

Keyword yang tersedia:
%keywords%';

        $pesan_non_customer = 'Mohon maaf, anda belum terdaftar sebagai customer kami.
Silahkan menghubungi 087818040024 untuk proses registrasi.';



        $pesan_dont_have_paket = 'Yth. Bpk/Ibu %name%,
%address%
Customer ID: %customer_id%

Saat ini anda sudah terdaftar di sistem kami, namun belum mempunyai paket.

Jika anda ingin menggunakan layanan kami, silahkan hubungi 0878-1804-0024.

*Shanum Network Fiber*
Jl. Sukamaju No. 49 RT 009/007
Ciseureuh, Purwakarta

*Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini.*';

        $pesan_no_bill = 'Yth. Bpk/Ibu %name%,
%address%
Customer ID: %customer_id%

Saat ini anda belum memiliki tagihan.

Kami mengucapkan terima kasih karena anda tertib administrasi.
Ini sangat berguna untuk kemajuan kami dalam memperbaiki layanan.

*Shanum Network Fiber*
Jl. Sukamaju No. 49 RT 009/007
Ciseureuh, Purwakarta

*Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini.*';

        $pesan_peringatan_pembayaran = 'Yth. Bpk/Ibu %name%,
%address%
Customer ID: %customer_id%

%day% hari lagi adalah batas akhir pembayaran paket internet anda. Saat ini anda memiliki %count-bill% tagihan yang belum dibayar:

%pakets%
*Jumlah tagihan: %total-bill%*

Lakukan pembayaran sebelum jatuh tempo untuk menghindari isolir otomatis oleh system kami.

Anda dapat melakukan pembayaran melalui:
%account-bank%

Terima kasih telah menggunakan layanan internet kami.

*Shanum Network Fiber*
Jl. Sukamaju No. 49 RT 009/007
Ciseureuh, Purwakarta

*_Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini._*';

        $pesan_peringatan_deadline = 'Yth. Bpk/Ibu %name%,
%address%
Customer ID: %customer_id%

Hari ini adalah batas akhir pembayaran paket internet anda. Saat ini anda memiliki %count-bill% tagihan yang belum dibayar:

%pakets%
*✅ Jumlah tagihan: %total-bill%*

Segera lakukan pembayaran sebelum jatuh tempo untuk menghindari isolir otomatis oleh system kami.

Anda dapat melakukan pembayaran melalui:
%account-bank%

Informasikan kepada kami jika anda sudah melakukan pembayaran di nomor *WA 0878-1804-0024*
Terima kasih telah menggunakan layanan internet kami.

*Shanum Network Fiber*
Jl. Sukamaju No. 49 RT 009/007
Ciseureuh, Purwakarta

*_Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini._*';

        $pesan_lewat_jatuh_tempo = 'Yth. Bpk/Ibu %name%,
%address%
Customer ID: %customer_id%

Kami ingin mengingatkan bahwa Anda memiliki %count-bill% tagihan paket internet yang belum dibayar hingga melewati jatuh tempo.
Berikut adalah rincian tagihan Anda:

%pakets%
*✅ Jumlah tagihan keseluruhan : %total-bill%*

Pembayaran dapat dilakukan melalui:
%account-bank%

Informasikan kepada kami jika anda sudah melakukan pembayaran di nomor *WA 0878-1804-0024*
Kami berkomitmen memberikan layanan terbaik untuk Anda.

*Shanum Network Fiber*
Jl. Sukamaju No. 49 RT 009/007
Ciseureuh, Purwakarta

*_Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini._*';

        $pesan_info_tagihan = 'Yth. Bpk/Ibu %name%,
%address%
Customer ID: %customer_id%

Berikut info tagihan anda.
%bills%
Jumlah tagihan: %total-bills%

Anda dapat melakukan pembayaran melalui:
%account-banks%

Terima kasih telah mempercayakan kebutuhan internet anda kepada kami.

*Shanum Network Fiber*
Jl. Sukamaju No. 49 RT 009/007
Ciseureuh, Purwakarta

*_Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini._*';

        $pesan_info_paket = 'Yth. Bpk/Ibu %name%,
%address%
Customer ID: %customer_id%

Berikut kami sampaikan rincian paket anda:
%pakets%

Terima kasih telah mempercayakan layanan internet anda kepada kami. Kami berkomitmen untuk selalu memberikan pelayanan yang terbaik.

*Shanum Network Fiber*
Jl. Sukamaju No. 49 RT 009/007
Ciseureuh, Purwakarta

*_Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini._*';

        $register_new_customer = 'Yth. Bpk/Ibu %name%,
%address%

Terima kasih telah melakukan pendaftaran di sistem kami.
Berikut layanan yang anda pilih:

%pakets%

Jika anda ingin menggunakan layanan kami, silahkan hubungi *WA 0878-1804-0024*.

*Shanum Network Fiber*
Jl. Sukamaju No. 49 RT 009/007
Ciseureuh, Purwakarta

*_Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini._*';

        $activation_new_customer = 'Yth. Bpk/Ibu %name%,
%address%

Aktivasi akun berhasil. Berikut detail informasi akun anda:

*👥 Nama:* _Bpk/Ibu %name%_
*🆔 Cust - ID:* _%customer_id%_
*📍 Alamat:* _%address%_
*📧 Email:* _%email%_
*👨🏼‍💼 Customer Service:* %customer_service%

Simpan informasi ini untuk pengajuan layanan atau saat terjadi gangguan layanan.

*Shanum Network Fiber*
Jl. Sukamaju No. 49 RT 009/007
Ciseureuh, Purwakarta

*_Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini._*';

        $deactivation_customer = 'Yth. Bpk/Ibu %name%,
%address%
Customer ID: %customer_id%

Dengan berat hati kami menonaktifkan akun anda. Jika tidak keberatan, silahkan kirim masukkan anda untuk perbaikan pada layanan kami.

Terima kasih telah menjadi pelanggan kami selama ini.

*Shanum Network Fiber*
Jl. Sukamaju No. 49 RT 009/007
Ciseureuh, Purwakarta

*_Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini._**';

        $add_paket = 'Yth. Bpk/Ibu %name%,
%address%
Customer ID: %customer_id%

Pendaftaran paket berhasil, berikut info layanan paket anda:

Paket: %paket_name%
Harga: %paket_price%

Biaya Langganan: %bill% per bulan

silahkan tunggu beberapa saat karna paket anda masih dalam proses aktivasi.

*Shanum Network Fiber*
Jl. Sukamaju No. 49 RT 009/007
Ciseureuh, Purwakarta

*Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini.*';

        $activation_paket = 'Yth. Bpk/Ibu %name%,
%address%
Customer ID: %customer_id%

Paket anda sudah berhasil diaktivasi, selamat menikmati layanan internet dari kami.

Masa berlaku: %periode%
Batas bayar: Setiap tanggal %deadline%
Nama CS: %cs_name%
Contact: %cs_contact%

Jika terjadi kendala pada jaringan, silahkan hubungi CS kami.

*Shanum Network Fiber*
Jl. Sukamaju No. 49 RT 009/007
Ciseureuh, Purwakarta

*_Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini._*';

        $enable_paket = 'Yth. Bpk/Ibu %name%,
%address%
Customer ID: %customer_id%

Paket anda sudah diaktifkan, selamat menikmati layanan internet kami kembali.

Info paket:
Paket: *%paket_name%*
Tagihan: *%bill%* per bulan
Status: *%status%*
Tanggal Aktivasi : %activation_date%

*Shanum Network Fiber*
Jl. Sukamaju No. 49 RT 009/007
Ciseureuh, Purwakarta

*_Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini._*';

        $disable_paket = 'Yth. Bpk/Ibu %name%,
%address%
Customer ID: %customer_id%

Paket anda sudah dinon-aktifkan, kami tidak mengenakan biaya layanan selama paket anda dinon-aktifkan.

Info paket:
Paket: *%paket_name%*
Status: *%status%*

*Shanum Network Fiber*
Jl. Sukamaju No. 49 RT 009/007
Ciseureuh, Purwakarta

*_Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini._*';

        $create_new_bill = 'Yth. Bpk/Ibu %name%,
%address%
Customer ID: %customer_id%

Saat ini anda sudah dapat membayar tagihan internet.
Berikut rincian tagihan anda:

*📦 Paket:* %paket-name%
*💶 Tagihan:* %bill%
*📆 Periode:* %periode%
*✅ Jatuh tempo:* %deadline%

Anda dapat melakukan pembayaran melalui:
%account-banks%

Informasikan kepada kami jika anda sudah melakukan pembayaran di nomor *WA 0878-1804-0024*
Terima kasih telah mempercayakan kebutuhan internet anda kepada kami.

*Shanum Network Fiber*
Jl. Sukamaju No. 49 RT 009/007
Ciseureuh, Purwakarta

*_Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini._*';

        //=================Create Template message============================
        WhatsappMessage::create([
            'slug' => 'unknow_keyword',
            'name' => 'Unknow Keyword',
            'incoming_message' => 'unknow keyword',
            'reply_message' => $pesan_no_keyword,
            'description' =>  'Pesan yang dikirim saat pesan yang diterima tidak ada dalam keyword.',
            'default' => true,
            'disabled' => false
        ]);
        WhatsappMessage::create([
            'slug' => 'payment',
            'name' => 'Pembayaran Lunas',
            'incoming_message' => 'payment',
            'reply_message' => $pesan_pembayaran_lunas,
            'description' =>  'Pesan yang dikirim saat pelanggan melakukan pembayaran.',
            'default' => true,
            'disabled' => false
        ]);
        WhatsappMessage::create([
            'slug' => 'unknow_customer',
            'name' => 'Non Customer',
            'incoming_message' => 'unknow customer',
            'reply_message' => $pesan_non_customer,
            'description' =>  'Pesan yang dikirim bukan customer.',
            'default' => true,
            'disabled' => false
        ]);
        WhatsappMessage::create([
            'slug' => 'unpayment',
            'name' => 'Unpayment',
            'incoming_message' => 'unpayment',
            'reply_message' => $pesan_unpayment,
            'description' =>  'Pesan yang dikirim saat admin melakukan pembatalan pembayaran.',
            'default' => true,
            'disabled' => false
        ]);
        WhatsappMessage::create([
            'slug' => 'no_paket',
            'name' => 'Customer Dont Have Paket',
            'incoming_message' => 'no paket',
            'reply_message' => $pesan_dont_have_paket,
            'description' =>  'Pesan yang dikirim saat pelanggan tidak memiliki paket internet.',
            'default' => true,
            'disabled' => false
        ]);
        WhatsappMessage::create([
            'slug' => 'no_bill',
            'name' => 'No Bill',
            'incoming_message' => 'no bill',
            'reply_message' => $pesan_no_bill,
            'description' =>  'Pesan yang dikirim saat pelanggan tidak memiliki tagihan.',
            'default' => true,
            'disabled' => false
        ]);
        WhatsappMessage::create([
            'slug' => 'reminder_bill',
            'name' => 'Reminder Bill',
            'incoming_message' => 'warning bill',
            'reply_message' => $pesan_peringatan_pembayaran,
            'description' =>  'Pesan yang dikirim untuk memberitahukan bahwa tagihan mereka akan jatuh tempo.',
            'default' => true,
            'disabled' => false
        ]);

        WhatsappMessage::create([
            'slug' => 'deadline_bill',
            'name' => 'Deadline Bill',
            'incoming_message' => 'warning bill',
            'reply_message' => $pesan_peringatan_deadline,
            'description' =>  'Pesan yang dikirim untuk memberitahukan bahwa tagihan mereka sudah jatuh tempo.',
            'default' => true,
            'disabled' => false
        ]);
        WhatsappMessage::create([
            'slug' => 'past_due_warning',
            'name' => 'Past Due Warning',
            'incoming_message' => 'warning bill',
            'reply_message' => $pesan_lewat_jatuh_tempo,
            'description' =>  'Pesan yang dikirim untuk mengingatkan bahwa tagihan sudah melewati jatuh tempo.',
            'default' => true,
            'disabled' => false
        ]);
        WhatsappMessage::create([
            'slug' => 'info_tagihan',
            'name' => 'Info Tagihan',
            'incoming_message' => 'info tagihan',
            'reply_message' => $pesan_info_tagihan,
            'description' =>  'Pesan yang dikirim saat pelanggan melakukan pengecekan tagihan.',
            'default' => false,
            'disabled' => false
        ]);
        WhatsappMessage::create([
            'slug' => 'info_paket',
            'name' => 'Info Paket',
            'incoming_message' => 'info paket',
            'reply_message' => $pesan_info_paket,
            'description' =>  'Pesan yang dikirim saat pelanggan melakukan pengecekan paket.',
            'default' => false,
            'disabled' => false
        ]);
        WhatsappMessage::create([
            'slug' => 'disable_paket',
            'name' => 'Disable Paket',
            'incoming_message' => 'disable paket',
            'reply_message' => $disable_paket,
            'description' =>  'Pesan yang dikirim saat paket di non-aktifkan.',
            'default' => true,
            'disabled' => false
        ]);
        WhatsappMessage::create([
            'slug' => 'enable_paket',
            'name' => 'Enable Paket',
            'incoming_message' => 'disable paket',
            'reply_message' => $enable_paket,
            'description' =>  'Pesan yang dikirim saat paket di aktifkan.',
            'default' => true,
            'disabled' => false
        ]);
        WhatsappMessage::create([
            'slug' => 'activation_paket',
            'name' => 'Activation Paket',
            'incoming_message' => 'activation paket',
            'reply_message' => $activation_paket,
            'description' =>  'Pesan yang dikirim saat aktivasi paket.',
            'default' => true,
            'disabled' => false
        ]);
        WhatsappMessage::create([
            'slug' => 'add_paket',
            'name' => 'Add Paket',
            'incoming_message' => 'add paket',
            'reply_message' => $add_paket,
            'description' =>  'Pesan yang dikirim saat penambahan paket customer.',
            'default' => true,
            'disabled' => false
        ]);
        WhatsappMessage::create([
            'slug' => 'deactivation_customer',
            'name' => 'Deactivation Customer',
            'incoming_message' => 'deactivation customer',
            'reply_message' => $deactivation_customer,
            'description' =>  'Pesan yang dikirim saat customer di non-aktifkan.',
            'default' => true,
            'disabled' => false
        ]);
        WhatsappMessage::create([
            'slug' => 'activation_customer',
            'name' => 'Activation Customer',
            'incoming_message' => 'activation customer',
            'reply_message' => $activation_new_customer,
            'description' =>  'Pesan yang dikirim saat aktivasi customer.',
            'default' => true,
            'disabled' => false
        ]);
        WhatsappMessage::create([
            'slug' => 'register_customer',
            'name' => 'Register Customer',
            'incoming_message' => 'register customer',
            'reply_message' => $register_new_customer,
            'description' =>  'Pesan yang dikirim saat registrasi customer.',
            'default' => true,
            'disabled' => false
        ]);


        WhatsappMessage::create([
            'slug' => 'create_new_bill',
            'name' => 'Create New bill',
            'incoming_message' => 'create new bill',
            'reply_message' => $create_new_bill,
            'description' =>  'Pesan yang dikirim saat tagihan dibuat.',
            'default' => true,
            'disabled' => false
        ]);
        WhatsappMessage::create([
            'slug' => 'paylater',
            'name' => 'Pay Later',
            'incoming_message' => 'paylater',
            'reply_message' => $pay_later,
            'description' =>  'Pesan yang dikirim saat permintaan pay later.',
            'default' => true,
            'disabled' => false
        ]);
    }
}
