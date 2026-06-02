<?php

return [
    'header' => [
        'name' => 'Sistem Manajemen',
    ],

    'home' => [
        'nav-item' => 'Beranda',
    ],

    'customers' => [
        'nav-item' => 'Pelanggan',
        'index' => 'Semua Pelanggan',
        'show-offline' => 'Pelanggan Offline',
        'show-online' => 'Pelanggan Online',
        'show-isolir' => 'Pelanggan Terisolir',
        'create' => 'Tambah Pelanggan',
        'member' => 'Daftar Anggota',
        'trashed' => 'Pelanggan Terhapus',
    ],

    'billings' => [
        'nav-item' => 'Tagihan',
        'active_billing' => 'Tagihan Aktif',
        'history' => 'Riwayat Tagihan',
        'create' => 'Buat Tagihan Baru',
        'add' => 'Tambah Tagihan',
        'show' => 'Semua Tagihan',
        'last' => 'Tagihan Terakhir',
        'show-paid' => 'Tagihan Lunas',
        'show-unpaid' => 'Tagihan Belum Lunas',
        'show-paylater' => 'Pay Later',
        'billing-cycles' => 'Siklus Penagihan'
    ],
    'receipts' => [
        'nav-item' => 'Kuitansi',
        'index' => 'Semua Kuitansi'
    ],
    'packages' => [
        'nav-item' => 'Paket Layanan',
        'index' => 'Semua Paket',
        'create' => 'Buat Paket Baru',
        'package-categories' => 'Kategori Paket'
    ],
    'profiles' => [
        'nav-item' => 'Profil Mikrotik', // Menyesuaikan konteks ppp profile b-ras
        'create' => 'Buat Profil Baru',
        'index' => 'Semua Profil',
    ],
    'subscriptions' => [
        'nav-item' => 'Langganan',
        'customer_subscriptions' => [
            'index' => 'Langganan Pelanggan'
        ],
        'subscription_durations' => [
            'index' => 'Durasi Langganan'
        ]
    ],
    'individual' => [
        'home' => 'Beranda',
        'details' => 'Detail Tagihan & Bayar',
        'bill' => 'Tagihan',
        'payment' => 'Pembayaran',
        'device-info' => 'Informasi Perangkat'
    ],
    'network' => [
        'nav-item' => 'Jaringan',
        'olt-managements' => [
            'index' => 'Manajemen OLT',
        ],
        'onu-managements' => [
            'index' => 'Manajemen ONT'
        ]
    ],
    'membership' => [
        'nav-item' => 'Keanggotaan',
        'index' => 'Semua Anggota',
        'create' => 'Tambah Anggota Baru',
    ],
    'addons' => [
        'nav-item' => 'Fitur Tambahan',
        'index' => 'Semua Addon',
        'create' => 'Buat Addon Baru',
    ],
    'reports' => [
        'nav-item' => 'Laporan',
        'kpi' => [
            'index' => 'Laporan KPI',
        ],
        'internet-monthly' => [
            'index' => 'Laporan Bulanan Internet',
        ],
    ],
    'conversations' => [
        'nav-item' => 'Pesan',
        'index' => 'WhatsApp'
    ],
    'settings' => [
        'nav-item' => 'Pengaturan',
        'general' => 'Umum',
        'servers' => [
            'nav-item' => 'Server Radius / NAS',
            'create' => 'Tambah Server',
            'index' => 'Semua Server',
        ],
        'routers' => [
            'nav-item' => 'Router Klien',
            'create' => 'Tambah Router',
            'index' => 'Semua Router',
        ],
        'customers' => [
            'nav-item' => 'Pelanggan',
        ],
        'users' => [
            'nav-item' => 'Karyawan / User',
            'create' => 'Tambah User',
            'index' => 'Semua User',
        ],
        'roles' => [
            'nav-item' => 'Peran & Izin',
            'create' => 'Buat Peran Baru',
            'index' => 'Semua Peran',
        ],
        'telegram' => [
            'nav-item' => 'Telegram Bot',
            'gateways' => 'Pengaturan Gateway',
            'chat-ids' => 'Chat ID',
            'messages' => 'Template Pesan',
        ],
        'whatsapp' => [
            'nav-item' => 'WhatsApp Gateway',
            'gateways' => 'Pengaturan Gateway',
            'templates' => 'Meta Templates',
            'messages' => 'Template Pesan',
            'features' => 'Fitur',
        ],
        'message-events' => [
            'nav-item' => 'Pesan',
            'index' => 'Semua Log Pesan',
        ],
        'payment-gateways' => [
            'nav-item' => 'Payment Gateway',
        ],
        'banks' => [
            'nav-item' => 'Rekening',
        ],
        'autoisolir' => [
            'nav-item' => 'Auto Isolir',
            'create' => 'Buat Aturan Isolir',
            'index' => 'Semua Aturan Isolir',
        ],
        'exports' => [
            'nav-item' => 'Ekspor Data',
        ],
        'updates' => [
            'nav-item' => 'Pembaruan Sistem',
        ],
        'back' => 'Kembali',
    ],
];
