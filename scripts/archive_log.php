<?php

date_default_timezone_set('Asia/Jakarta');

// Path dasar aplikasi Laravel
$basePath = '/www/wwwroot/www.shanetfiber.co.id';

// Lokasi file log Laravel
$logFile = $basePath . '/storage/logs/laravel.log';

// Lokasi folder arsip log
$archiveBase = $basePath . '/storage/logs/archived';

// Format tanggal dan timestamp untuk nama arsip
$date = date('Y-m-d');
$timestamp = date('Y-m-d_H-i-s');
$archiveFolder = $archiveBase . '/' . $date;
$archivedLog = $archiveFolder . "/laravel_{$timestamp}.log";

// Cek apakah log file ada dan ukurannya > 0 KB
if (file_exists($logFile) && filesize($logFile) > 0) {

    // Cek apakah folder arsip sudah ada, jika belum buat foldernya
    if (!is_dir($archiveFolder)) {
        mkdir($archiveFolder, 0755, true);
    }

    // Pindahkan log lama ke arsip
    if (rename($logFile, $archivedLog)) {
        echo "Log archived to {$archivedLog}\n";

        // Buat file kosong baru untuk log
        touch($logFile);
        chmod($logFile, 0664);
        chown($logFile, 'www'); // Ganti dengan user yang sesuai, misalnya www-data
    } else {
        echo "Failed to archive log.\n";
        exit(1);
    }

} else {
    echo "Log file is empty or does not exist, skipping archive.\n";
    exit(0); // Tidak ada arsip jika file kosong atau tidak ada
}