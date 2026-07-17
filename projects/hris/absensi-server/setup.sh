#!/bin/bash
# ============================================
# HRIS Moduvox - Server Setup Script
# ============================================
# Jalankan script ini setelah git clone di server:
#   chmod +x setup.sh && ./setup.sh
# ============================================

set -e

echo "=========================================="
echo "  HRIS Moduvox - Auto Setup"
echo "=========================================="

# 1. Copy .env
if [ ! -f .env ]; then
    cp .env.example .env
    echo "[✓] .env berhasil dibuat dari .env.example"
    echo ""
    echo "  ⚠️  PENTING: Edit .env dulu untuk sesuaikan:"
    echo "     - DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD"
    echo "     - APP_URL (sesuaikan domain)"
    echo ""
    read -p "  Sudah edit .env? (y/n): " confirm
    if [ "$confirm" != "y" ]; then
        echo "  → Edit .env terlebih dahulu, lalu jalankan ulang script ini."
        exit 1
    fi
else
    echo "[✓] .env sudah ada"
fi

# 2. Install dependencies
echo ""
echo "[...] Menginstall composer dependencies..."
composer install --no-dev --optimize-autoloader
echo "[✓] Composer dependencies terinstall"

# 3. Generate app key
php artisan key:generate --force
echo "[✓] App key di-generate"

# 4. Create database tables
echo ""
echo "[...] Menjalankan migration..."
php artisan migrate --force
echo "[✓] Database migration selesai"

# 5. Seed dummy data
echo ""
echo "[...] Seeding data dummy..."
php artisan db:seed --force
echo "[✓] Data dummy berhasil di-seed"

# 6. Optimize
echo ""
echo "[...] Optimizing..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link 2>/dev/null || true
echo "[✓] Optimisasi selesai"

# 7. Set permissions
chmod -R 775 storage bootstrap/cache
echo "[✓] Permission storage & cache di-set"

echo ""
echo "=========================================="
echo "  ✅ SETUP SELESAI!"
echo "=========================================="
echo ""
echo "  Langkah selanjutnya:"
echo "  1. Pastikan Nginx/Apache pointing ke folder public/"
echo "  2. Akses: http://YOUR_DOMAIN"
echo "  3. Login otomatis sebagai admin (demo bypass)"
echo ""
