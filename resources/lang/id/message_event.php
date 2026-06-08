<?php

return [
    'header' => [],
    'title' => 'Log Riwayat Pesan',
    'app_title' => 'Riwayat Pesan',

    'table' => [
        'hash' => '#',
        'channel' => 'Saluran',
        'customer' => 'Pelanggan',
        'phone' => 'No. Telepon',
        'message_type' => 'Tipe Pesan',
        'status' => 'Status',
        'sent_at' => 'Dikirim Pada',
        'scheduled_at' => 'Dijadwalkan Pada',
        'action' => 'Aksi',
    ],

    'label' => [
        'all_channel' => 'Semua Saluran',
        'all_message_type' => 'Semua Tipe Pesan',
        'quick_filter' => 'Filter Cepat:',
        'label_response' => 'Respon:',
        'realtime' => 'REALTIME',
        'unknown_customer' => 'Pelanggan Tidak Dikenal',
        'message_id' => 'ID Pesan',
        'trace_id' => 'Trace ID'
    ],

    'placeholder' => [
        'placeholder_search' => 'Nama / Nomor',
    ],

    'button' => [
        'advanced_filter' => 'Filter Lanjutan',
        'filter' => 'Filter',
        'reset' => 'Reset',
        'btn_detail' => 'Detail',
        'btn_view_detail' => 'Lihat Detail',
        'btn_retry' => 'Coba Lagi',
    ],

    'state' => [
        'status_failed' => 'Gagal',
        'status_pending' => 'Tertunda',
        'status_success' => 'Berhasil',
        'status_all' => 'Semua',
        'no_events' => 'Riwayat pesan tidak ditemukan',
    ],

    'db_status' => [
        'success' => 'BERHASIL',
        'failed'  => 'GAGAL',
        'pending' => 'TERTUNDA',
    ],
];
