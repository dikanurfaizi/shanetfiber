<?php

// Atur timezone agar timestamp sesuai jam lokal
date_default_timezone_set('Asia/Jakarta');

// Path dasar aplikasi Laravel berdasarkan lokasi file ini
$basePath = '/www/wwwroot/www.shanetfiber.co.id';

// Lokasi file log schedule
$logFile = $basePath . '/storage/logs/schedule.log';

// Waktu sekarang untuk log separator
$timestamp = date('Y-m-d H:i:s');

// Tambahkan separator log
file_put_contents($logFile, "  ============================ $timestamp ===========================\n", FILE_APPEND);

exec("php {$basePath}/artisan cache:clear >> {$logFile} 2>&1");
exec("php {$basePath}/artisan queue:flush >> {$logFile} 2>&1");
// exec("php {$basePath}/artisan queue:restart >> {$logFile} 2>&1");
// Jalankan `reminder:messages-send`
exec("php {$basePath}/artisan reminder:messages-send >> {$logFile} 2>&1");

// Baris kosong pemisah
file_put_contents($logFile, "\n", FILE_APPEND);
