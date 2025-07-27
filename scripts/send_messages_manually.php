<?php

// Atur timezone agar timestamp sesuai jam lokal
date_default_timezone_set('Asia/Jakarta');

// Ambil basePath otomatis: parent folder dari folder script ini
$basePath = dirname(__DIR__);

// Lokasi file log schedule
$logFile = $basePath . '/storage/logs/schedule.log';
$composerLog = $basePath . '/storage/logs/composer-dump.log';

// Waktu sekarang untuk log separator
$timestamp = date('Y-m-d H:i:s');

// Tambahkan separator log
file_put_contents($logFile, "============================ $timestamp ============================\n", FILE_APPEND);

// Jalankan perintah artisan dan log output-nya
$commands = [
    "php artisan config:clear",
    "php artisan cache:clear",
    "php artisan route:clear",
    "php artisan view:clear",
    "composer dump-autoload"
];

// Eksekusi perintah
foreach ($commands as $command) {
    if (str_contains($command, 'composer')) {
        exec("cd {$basePath} && $command >> {$composerLog} 2>&1");
    } else {
        exec("cd {$basePath} && $command >> {$logFile} 2>&1");
    }
}

// Lanjutkan dengan perintah tambahan
exec("cd {$basePath} && php artisan queue:flush >> {$logFile} 2>&1");
exec("cd {$basePath} && php artisan message:send-manually >> {$logFile} 2>&1");

// Tambahkan baris kosong sebagai pemisah
file_put_contents($logFile, "\n", FILE_APPEND);
