<?php

/**
 * archive‑laravel‑log.php
 * Pindah log Laravel ke folder arsip harian + hapus arsip > 30 hari
 */

date_default_timezone_set('Asia/Jakarta');

// ===  Konfigurasi dasar  =====================================================
$basePath      = dirname(__DIR__);                          // root proyek
$logFile       = $basePath . '/storage/logs/laravel.log';   // file log aktif
$archiveBase   = $basePath . '/storage/logs/archived';      // direktori arsip
$retentionDays = 30;                                        // lama penyimpanan

// ===  1) Arsipkan log hari ini  ==============================================
if (file_exists($logFile) && filesize($logFile) > 0) {
    $date         = date('Y-m-d');
    $timestamp    = date('Y-m-d_H-i-s');
    $archiveDir   = $archiveBase . '/' . $date;
    $archivedLog  = $archiveDir . "/laravel_{$timestamp}.log";

    // Pastikan folder arsip harian ada
    if (!is_dir($archiveDir) && !mkdir($archiveDir, 0755, true)) {
        fwrite(STDERR, "❌  Gagal membuat folder arsip $archiveDir\n");
        exit(1);
    }

    // Pindahkan log
    if (rename($logFile, $archivedLog)) {
        echo "✅  Log di‑arsipkan ke $archivedLog\n";
        // Buat file log baru kosong untuk Laravel
        touch($logFile);
        chmod($logFile, 0644);
        chown($logFile, 'www');          // sesuaikan user (mis. www-data)
    } else {
        fwrite(STDERR, "❌  Gagal memindahkan log ke $archivedLog\n");
        exit(1);
    }
} else {
    echo "ℹ️  Log kosong atau tidak ada, lewati proses arsip.\n";
}

// ===  2) Hapus folder arsip lama  ============================================
// Hitung batas waktu
$cutOff = new DateTime("-{$retentionDays} days");

$objects = new FilesystemIterator($archiveBase, FilesystemIterator::SKIP_DOTS);
foreach ($objects as $object) {
    if (!$object->isDir()) {
        // Hanya dipakai untuk folder berformat YYYY-MM-DD
        continue;
    }

    $folderName = $object->getBasename();
    // Validasi format tanggal supaya tidak menghapus folder tak terduga
    $folderDate = DateTime::createFromFormat('Y-m-d', $folderName);
    if ($folderDate === false) {
        continue;
    }

    if ($folderDate < $cutOff) {
        // Rekursif hapus folder & isinya
        deleteDir($object->getPathname());
        echo "🗑️  Arsip $folderName dihapus (lebih dari $retentionDays hari)\n";
    }
}

// ===  3) Jalankan Artisan: hapus message_logs lama  ===========================
echo "➡️  Menjalankan Artisan logs:delete-old-messages...\n";
$cmd = escapeshellcmd("php $basePath/artisan logs:delete-old-messages");
exec($cmd, $output, $exitCode);

echo implode("\n", $output) . "\n";
if ($exitCode === 0) {
    echo "✅  Artisan command selesai.\n";
} else {
    fwrite(STDERR, "❌  Artisan logs:delete-old-messages gagal (exit $exitCode)\n");
}

/**
 * Hapus direktori secara rekursif
 */
function deleteDir(string $dir): void
{
    $items = new FilesystemIterator($dir, FilesystemIterator::SKIP_DOTS);
    foreach ($items as $item) {
        $item->isDir() ? deleteDir($item->getPathname()) : unlink($item->getPathname());
    }
    rmdir($dir);
}