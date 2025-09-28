<?php
// file: artisan_command.php
// Jalankan: php artisan_command.php

$projectDir = '/www/wwwroot/www.shanetfiber.co.id';
$logDir = $projectDir . '/storage/logs';
if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/' . date('Ymd_His') . '_artisan_command.log';

// Fungsi untuk tulis log
function logLine($msg) {
    global $logFile;
    echo $msg . PHP_EOL;
    file_put_contents($logFile, $msg . PHP_EOL, FILE_APPEND);
}

chdir($projectDir) or exit(logLine("❌ Gagal pindah ke folder project"));

logLine("=== Mulai menjalankan script: " . date('Y-m-d H:i:s') . " ===");

// Jalankan composer require
$commands = [
    'composer require predis/predis',
    'composer require jenssegers/agent',
    'composer dump-autoload',
    'php artisan config:clear',
    'php artisan cache:clear',
    'php artisan optimize:clear',
    'php artisan route:clear',
    'php artisan view:clear',
];

foreach ($commands as $cmd) {
    logLine(">>> Menjalankan: $cmd");
    exec($cmd . ' 2>&1', $output, $returnVar);
    foreach ($output as $line) {
        logLine($line);
    }
    $output = [];
    if ($returnVar !== 0) {
        logLine("❌ Perintah gagal dengan kode: $returnVar");
    } else {
        logLine("✅ Selesai: $cmd");
    }
}

logLine("=== Script selesai: " . date('Y-m-d H:i:s') . " ===");
logLine("Log tersimpan di: $logFile");
