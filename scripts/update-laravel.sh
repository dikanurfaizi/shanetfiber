#!/usr/bin/env bash
###############################################################################
# update-laravel.sh
# One-click update & deploy for a Laravel project.
#
# Pilihan:
#   ./update-laravel.sh install   → composer install
#   ./update-laravel.sh update    → composer update
###############################################################################

set -euo pipefail

# ➜ PARAMETER DASAR – ubah sesuai kebutuhan
PROJECT_DIR="/www/wwwroot/www.shanetfiber.co.id"
COMPOSER_BIN="$(command -v composer || true)"
PHP_BIN="/usr/bin/php"
LOG_DIR="$PROJECT_DIR/storage/logs"
LOGFILE="$LOG_DIR/deploy-$(date '+%Y%m%d').log"

# ➜ PASTIKAN LOG DIR ADA
mkdir -p "$LOG_DIR"

timestamp() { date '+%Y-%m-%d %H:%M:%S'; }
# Taruh di paling atas setelah set -euo pipefail
trap "echo \"$(timestamp)  Menonaktifkan maintenance mode (trap)\" | tee -a \"$LOGFILE\"; $PHP_BIN artisan up" EXIT

echo "$(timestamp)  ===== Mulai deployment =====" | tee -a "$LOGFILE"

cd "$PROJECT_DIR" || {
  echo "$(timestamp)  ERROR: Tidak bisa masuk ke $PROJECT_DIR" | tee -a "$LOGFILE"
  exit 1
}

if [ ! -f composer.json ]; then
  echo "$(timestamp)  ERROR: composer.json tidak ditemukan di $PROJECT_DIR" | tee -a "$LOGFILE"
  exit 1
fi

###############################################################################
# 1. Maintenance Mode
###############################################################################
echo "$(timestamp)  Enable maintenance mode" | tee -a "$LOGFILE"
$PHP_BIN artisan down --retry=60 || true

###############################################################################
# 2.  TARIK KODE TERBARU (opsional – hapus jika pakai rsync/SFTP)
###############################################################################
# if [ -d ".git" ]; then
#   BRANCH=$(git rev-parse --abbrev-ref HEAD)
#   echo "$(timestamp)  Git pull origin $BRANCH" | tee -a "$LOGFILE"
#   git pull --ff-only >>"$LOGFILE" 2>&1
# fi

###############################################################################
# 3. Update Composer (jika ada versi baru)
###############################################################################
if [ -z "$COMPOSER_BIN" ]; then
  echo "$(timestamp)  Composer not found, downloading…" | tee -a "$LOGFILE"
  curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
  $PHP_BIN /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer --quiet
  rm -f /tmp/composer-setup.php
  COMPOSER_BIN="/usr/local/bin/composer"
fi

if ! $COMPOSER_BIN self-update --dry-run | grep -q 'You are already using'; then
  echo "$(timestamp)  Updating Composer to latest stable" | tee -a "$LOGFILE"
  $COMPOSER_BIN self-update --2 >>"$LOGFILE" 2>&1
fi

###############################################################################
# 4. Composer Install / Update
###############################################################################
ACTION="${1:-install}"  # Default = install
case "$ACTION" in
  install)
    echo "$(timestamp)  Composer install (no-dev, optimized)" | tee -a "$LOGFILE"
    $COMPOSER_BIN install \
      --no-dev \
      --prefer-dist \
      --no-interaction \
      --optimize-autoloader \
      --apcu-autoloader >>"$LOGFILE" 2>&1
    ;;
  update)
    echo "$(timestamp)  Composer update (no-dev, optimized)" | tee -a "$LOGFILE"
    $COMPOSER_BIN update \
      --no-dev \
      --prefer-dist \
      --no-interaction \
      --optimize-autoloader \
      --apcu-autoloader >>"$LOGFILE" 2>&1
    ;;
  *)
    echo "Usage: $0 [install|update]"
    exit 1
    ;;
esac

###############################################################################
# 5. Laravel Optimize & Migrate
###############################################################################
echo "$(timestamp)  Jalankan migrasi database" | tee -a "$LOGFILE"
$PHP_BIN artisan migrate --force >>"$LOGFILE" 2>&1

echo "$(timestamp)  Jalankan cache & config optimise" | tee -a "$LOGFILE"
$PHP_BIN artisan config:clear   >>"$LOGFILE" 2>&1
$PHP_BIN artisan cache:clear    >>"$LOGFILE" 2>&1
$PHP_BIN artisan view:clear     >>"$LOGFILE" 2>&1
$PHP_BIN artisan route:cache    >>"$LOGFILE" 2>&1
$PHP_BIN artisan config:cache   >>"$LOGFILE" 2>&1
$PHP_BIN artisan optimize       >>"$LOGFILE" 2>&1

###############################################################################
# 6. Keluar dari Maintenance Mode
###############################################################################
echo "$(timestamp)  Disable maintenance mode" | tee -a "$LOGFILE"
$PHP_BIN artisan up

echo "$(timestamp)  ===== Deployment selesai dengan sukses =====" | tee -a "$LOGFILE"
exit 0
