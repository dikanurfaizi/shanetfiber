#!/bin/bash

# Path project Laravel
PROJECT_DIR="/www/wwwroot/www.shanetfiber.co.id"

# Update composer ke versi terbaru (v2.x)
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
php -r "unlink('composer-setup.php');"

# Masuk ke direktori project
cd "$PROJECT_DIR" || exit

# Jalankan composer update untuk memperbarui semua dependensi
/usr/local/bin/composer update --no-interaction >> storage/logs/composer-update.log 2>&1

# Dump autoload dan clear cache
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear