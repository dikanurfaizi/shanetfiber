#!/bin/bash
LARAVEL_PATH="/www/wwwroot/www.shanetfiber.co.id"
USER="www"
GROUP="www"

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Fixing Laravel permissions..." >> $LARAVEL_PATH/storage/logs/permission_fix.log

# Pastikan folder ada
if [ ! -d "$LARAVEL_PATH/storage" ]; then
  echo "Storage folder not found!" >> $LARAVEL_PATH/storage/logs/permission_fix.log
  exit 1
fi

# Ubah kepemilikan
chown -R $USER:$GROUP $LARAVEL_PATH/storage $LARAVEL_PATH/bootstrap/cache

# Ubah izin folder
find $LARAVEL_PATH/storage -type d -exec chmod 775 {} \;
find $LARAVEL_PATH/bootstrap/cache -type d -exec chmod 775 {} \;

# Ubah izin file
find $LARAVEL_PATH/storage -type f -exec chmod 664 {} \;
find $LARAVEL_PATH/bootstrap/cache -type f -exec chmod 664 {} \;

# Terapkan setgid agar group turun ke file baru juga
chmod g+s $LARAVEL_PATH/storage $LARAVEL_PATH/storage/logs

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Permission fix done." >> $LARAVEL_PATH/storage/logs/permission_fix.log
exit 0
