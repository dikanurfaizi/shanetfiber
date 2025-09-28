sudo apt install -y nodejs
sudo apt install -y npm

cd /www/wwwroot/www.shanetfiber.co.id

# Hapus node_modules & lock file lama
rm -rf node_modules
rm -f package-lock.json yarn.lock

# Install ulang semua dependensi
npm install

# Pastikan Vite dan laravel-vite-plugin terpasang
npm install vite laravel-vite-plugin --save-dev
