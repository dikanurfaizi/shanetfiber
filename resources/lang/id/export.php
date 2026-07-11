<?php

return [
    'title' => [
        'export' => 'Ekspor Pelanggan ke Router',
        'comment' => 'Sinkronisasi Komentar ke Router'
    ],
    'message' => [
        'warning' => 'Peringatan! Proses ekspor akan menulis ulang atau menggandakan akun pelanggan pada router tujuan.',
        'comment' => 'Perbarui status komentar pelanggan (Payment/Unpayment) pada Mikrotik tertentu berdasarkan data tagihan periode aktif saat ini.',
        'select_source_server' => 'Silakan pilih server asal.',
        'select_destination_server' => 'Silakan pilih server tujuan.',
        'select_target_server' => 'Silakan pilih server target untuk sinkronisasi.',
        'processing_export' => 'Sedang mengekspor data ke router...',
        'processing_sync' => 'Sedang menyinkronkan komentar ke router...',
    ],
    'placeholder' => [
        'select_source_server' => 'Pilih Server Asal',
        'select_destination_server' => 'Pilih Server Tujuan',
        'select_target_server' => 'Pilih Server Target Sinkronisasi',
    ],
    'button' => [
        'export_customer_data' => 'Ekspor Data Pelanggan',
        'export_comment_data' => 'Sinkronkan Data Komentar',
    ],
    'label' => [
        'source_server' => 'Server Asal',
        'destination_server' => 'Server Tujuan',
        'target_server' => 'Server Target',
    ],
];
