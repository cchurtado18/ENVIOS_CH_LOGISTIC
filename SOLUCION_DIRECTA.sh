#!/bin/bash
# Script para solucionar el problema directamente en el servidor

cd /var/www/ch-logistic-api

echo "1. Actualizando código..."
git pull origin main

echo "2. Obteniendo credenciales de la base de datos..."
DB_NAME=$(grep DB_DATABASE .env | cut -d '=' -f2)
DB_USER=$(grep DB_USERNAME .env | cut -d '=' -f2)
DB_PASS=$(grep DB_PASSWORD .env | cut -d '=' -f2)

echo "3. Añadiendo columnas faltantes..."
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" << 'SQL'
ALTER TABLE shipments ADD COLUMN IF NOT EXISTS metadata JSON NULL;
ALTER TABLE shipments ADD COLUMN IF NOT EXISTS internal_status VARCHAR(255) NULL;
ALTER TABLE shipments ADD COLUMN IF NOT EXISTS user_id BIGINT UNSIGNED NULL;
SQL

# Si IF NOT EXISTS no funciona, intentar sin verificación
if [ $? -ne 0 ]; then
    echo "Intentando sin IF NOT EXISTS..."
    mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" << 'SQL' 2>/dev/null
ALTER TABLE shipments ADD COLUMN metadata JSON NULL;
SQL
    mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" << 'SQL' 2>/dev/null
ALTER TABLE shipments ADD COLUMN internal_status VARCHAR(255) NULL;
SQL
    mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" << 'SQL' 2>/dev/null
ALTER TABLE shipments ADD COLUMN user_id BIGINT UNSIGNED NULL;
SQL
fi

echo "4. Limpiando cachés..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "5. Reiniciando servicios..."
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx

echo "✅ ¡Listo! Intenta iniciar sesión ahora."




