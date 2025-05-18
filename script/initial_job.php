<?php

// Atur timezone agar timestamp sesuai jam lokal
date_default_timezone_set('Asia/Jakarta');

// Ambil basePath otomatis: parent folder dari folder script
$basePath = dirname(__DIR__);

// Lokasi file log schedule
$logFile = $basePath . '/storage/logs/schedule-initial-payment.log';

// Waktu sekarang untuk log separator
$timestamp = date('Y-m-d H:i:s');

// Tambahkan separator log
file_put_contents($logFile, "  ============================ $timestamp ===========================\n", FILE_APPEND);

// Jalankan Laravel command initial-payment:messages-send
exec("php {$basePath}/artisan initial-payment:messages-send >> {$logFile} 2>&1");

// Tambahkan baris kosong sebagai pemisah
file_put_contents($logFile, "\n", FILE_APPEND);
