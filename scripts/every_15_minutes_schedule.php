<?php

// Atur timezone agar timestamp sesuai jam lokal
date_default_timezone_set('Asia/Jakarta');

// Ambil basePath otomatis: parent folder dari folder script
$basePath = dirname(__DIR__);

// Lokasi file log schedule
$logFile = $basePath . '/storage/logs/schedule.log';

// Waktu sekarang untuk log separator
$timestamp = date('Y-m-d H:i:s');

// Tambahkan separator log
echo "[$timestamp] Menjalankan scheduled tasks...\n";
file_put_contents($logFile, "  ============================ $timestamp ===========================\n", FILE_APPEND);

// Proses 1: cache:clear
echo "→ Menjalankan: php artisan cache:clear\n";
exec("php {$basePath}/artisan cache:clear >> {$logFile} 2>&1");

// Proses 2: queue:flush
echo "→ Menjalankan: php artisan queue:flush\n";
exec("php {$basePath}/artisan queue:flush >> {$logFile} 2>&1");

// Proses 3: initial-payment:messages-send
echo "→ Menjalankan: php artisan initial-payment:messages-send\n";
exec("php {$basePath}/artisan initial-payment:messages-send >> {$logFile} 2>&1");

// Proses 4: reminder:messages-send
echo "→ Menjalankan: php artisan reminder:messages-send\n";
exec("php {$basePath}/artisan reminder:messages-send >> {$logFile} 2>&1");

// Tambahkan baris kosong sebagai pemisah
file_put_contents($logFile, "\n", FILE_APPEND);

echo "✅ Semua proses selesai dijalankan.\n";
