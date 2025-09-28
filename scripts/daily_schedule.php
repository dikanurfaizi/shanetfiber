<?php

// 1. Set timezone
date_default_timezone_set('Asia/Jakarta');

// 2. Tentukan base path & lokasi log
$basePath = dirname(__DIR__);
$logFile  = $basePath . '/storage/logs/schedule.log';

// 3. Pastikan folder /storage/logs ada
if (!is_dir(dirname($logFile))) {
    mkdir(dirname($logFile), 0755, true);
}

// 4. Timestamp & separator
$timestamp  = date('Y-m-d H:i:s');
$separator  = "\n============================ $timestamp ============================\n";

// 5. Tulis header ke log + echo ke console
file_put_contents($logFile, $separator, FILE_APPEND);
echo "[$timestamp] Menjalankan scheduled tasks...\n";

// 6. Jalankan artisan commands & arahkan outputnya ke file log
exec("php {$basePath}/artisan create_billing:cron            >> {$logFile} 2>&1");
exec("php {$basePath}/artisan billing:update-paylater-secret >> {$logFile} 2>&1");

// 7. Tambahkan baris kosong pemisah
file_put_contents($logFile, "\n", FILE_APPEND);

echo "✅ Semua proses selesai dijalankan.\n";
