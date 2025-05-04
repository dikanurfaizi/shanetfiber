<?php

date_default_timezone_set('Asia/Jakarta');

$basePath = '/www/wwwroot/shanetfiber.my.id';
$logFile = $basePath . '/storage/logs/laravel.log';
$archiveBase = $basePath . '/storage/logs/archived';

$date = date('Y-m-d');
$timestamp = date('Y-m-d_H-i-s');
$archiveFolder = $archiveBase . '/' . $date;
$archivedLog = $archiveFolder . "/laravel_{$timestamp}.log";

// Cek apakah log file ada
if (!file_exists($logFile)) {
    echo "Log file not found: {$logFile}\n";
    exit(1);
}

// Buat folder jika belum ada
if (!is_dir($archiveFolder)) {
    mkdir($archiveFolder, 0755, true);
}

// Pindahkan log lama ke archive
if (rename($logFile, $archivedLog)) {
    echo "Log archived to {$archivedLog}\n";

    // Buat file kosong baru
    touch($logFile);
    chmod($logFile, 0664);
    chown($logFile, 'www'); // Ganti ini sesuai user web server-mu
} else {
    echo "Failed to archive log.\n";
    exit(1);
}