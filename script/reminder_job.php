<?php

// Atur timezone agar timestamp sesuai jam lokal
date_default_timezone_set('Asia/Jakarta');

// Path dasar aplikasi Laravel berdasarkan lokasi file ini
$basePath = 'D:/Development/Shanetfiber/shanetfiber.my.id';

// Lokasi file log schedule
$logFile = $basePath . '/storage/logs/schedule.log';

// Waktu sekarang untuk log separator
$timestamp = date('Y-m-d H:i:s');

// Tambahkan separator log
file_put_contents($logFile, "  ============================ $timestamp ===========================\n", FILE_APPEND);

// Jalankan `reminder:messages-send`
exec("php {$basePath}/artisan reminder:messages-send >> {$logFile} 2>&1");

// Baris kosong pemisah
file_put_contents($logFile, "\n", FILE_APPEND);
