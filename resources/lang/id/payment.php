<?php

return [
    'mikrotik' => [
        'not_found'         => 'Data Router MikroTik tidak ditemukan untuk billing ini.',
        'connection_failed' => 'Koneksi ke Server Router (MikroTik) gagal. Silakan cek status jaringan atau konfigurasi API router Anda.',
        'user_offline'      => "User PPP ':username' tidak terdeteksi online di router. Pastikan modem pelanggan dalam kondisi menyala.",
        'query_failed'      => "Gagal memverifikasi status aktif pelanggan ':username' pada router.",
    ],
    'success' => [
        'no_usage'  => "Status penagihan untuk :name berhasil diubah menjadi Tidak Ada Penggunaan untuk :periode",
        'processed' => 'Pembayaran untuk :name berhasil diproses.',
    ],
    'errors' => [
        'failed'    => 'Proses pembayaran gagal dilakukan.',
    ]
];
