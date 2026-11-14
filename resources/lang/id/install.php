<?php

return [
    'title' => [
        'primary' => 'Install Customer Management',
        'admin' => 'Create an Admin Account!',
        'greeting' => 'Selamat datang kawan,',
        'welcome_message' => 'Kelola customer mikrotik pakai Customer Management jadi lebih mudah.',
        'license_notice' => 'Source code ini gratis ya dan dilarang diperjualbelikan.',
        'important' => 'Penting!!!',
    ],

    'db' => [
        'exists_warning' => 'Ups... Database dengan nama :db sudah ada di servermu. Jika kamu ingin melanjutkan menginstall, database :db dapat terhapus secara ludes semua lo.',
        'success' => 'Database <b class="text-success">:db</b> sudah berhasil dibuat.',
        'error' => 'Ups... maaf, saya tidak dapat menemukan database dengan nama <b class="text-danger">:db</b>. Mungkin saat instalasi awal tadi kami mengalami masalah saat membuat database <b class="text-danger">:db</b>. Silahkan ulangi dari awal lagi ya...',
    ],

    'form' => [
        'name' => 'Nama Lengkap',
        'email' => 'Alamat Email',
        'password' => 'Password',
        'repeat_password' => 'Ulangi Password',
        'agreement' => 'Saya mengerti dan setuju untuk melanjutkan proses instalasi.',
        'strength_weak' => 'Lemah (Low)',
        'strength_medium' => 'Sedang (Medium)',
        'strength_strong' => 'Kuat (Strong)',
        'match_success' => '✔ Password sama',
        'match_error' => '✖ Password tidak sama',
        'sequential_warning' => ' (Angka berurutan dilarang)',
    ],

    'validation' => [
        'name_required' => 'Kolom nama lengkap wajib diisi.',
        'email_required' => 'Kolom alamat email wajib diisi.',
        'email_invalid' => 'Format alamat email tidak valid.',
        'email_unique' => 'Email ini sudah terdaftar.',
        'password_required' => 'Kolom password wajib diisi.',
        'password_confirmed' => 'Konfirmasi password tidak cocok dengan password.',
        'password_min' => 'Password minimal harus 8 karakter.',
        'password_sequential' => 'Password tidak boleh mengandung angka berurutan (contoh: 123, 4321).',
        'password_date_format' => 'Password tidak boleh menggunakan format tanggal (8 digit angka beruntun seperti ddmmyyyy).',
    ],

    'button' => [
        'install' => 'Install',
        'overwrite' => 'Gak papa bro lanjut install dan hapus database lamaku!',
        'continue' => 'Lanjutkan',
        'restart' => 'Mulai dari awal',
        'login' => 'Login Admin',
        'create_admin' => 'Buat Akun Admin',
    ],

    'finish' => [
        'desc_1' => 'Installasi Customer Management telah selesai.',
        'desc_2' => 'Kamu harus menghapus route url installer.',
        'desc_3' => 'Buka file',
        'desc_4' => 'atau',
        'desc_5' => 'kemudian hapus kode:',
    ],
];
