#!/bin/bash

# Path ke direktori project Laravel
PROJECT_DIR="/www/wwwroot/www.shanetfiber.co.id"
PHP_BIN="/www/server/php/83/bin/php"

cd "$PROJECT_DIR" || exit

# Tambahkan safe.directory ke git config
# git config --global --add safe.directory "$PROJECT_DIR"

# Jalankan composer require dengan update penuh dan hindari lock blocker
composer require barryvdh/laravel-dompdf --with-all-dependencies --no-interaction >> storage/logs/composer-install.log 2>&1

# Autoload dump
composer dump-autoload >> storage/logs/composer-dump.log 2>&1

# Clear dan optimize Laravel cache
$PHP_BIN artisan config:clear
$PHP_BIN artisan cache:clear
$PHP_BIN artisan optimize:clear
