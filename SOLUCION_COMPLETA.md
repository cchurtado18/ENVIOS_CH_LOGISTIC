# SOLUCIÓN COMPLETA - Ejecutar en el servidor

## Paso 1: Actualizar código
cd /var/www/ch-logistic-api
git pull origin main

## Paso 2: Añadir columna metadata directamente en MySQL
sudo mysql -u root -p << 'MYSQL_SCRIPT'
USE tu_base_de_datos;
ALTER TABLE shipments ADD COLUMN IF NOT EXISTS metadata JSON NULL;
ALTER TABLE shipments ADD COLUMN IF NOT EXISTS internal_status VARCHAR(255) NULL;
ALTER TABLE shipments ADD COLUMN IF NOT EXISTS user_id BIGINT UNSIGNED NULL;
MYSQL_SCRIPT

## Paso 3: Si MySQL no soporta "IF NOT EXISTS", usa esto:
sudo mysql -u root -p tu_base_de_datos -e "ALTER TABLE shipments ADD COLUMN metadata JSON NULL;" 2>/dev/null || echo "Columna metadata ya existe o error"
sudo mysql -u root -p tu_base_de_datos -e "ALTER TABLE shipments ADD COLUMN internal_status VARCHAR(255) NULL;" 2>/dev/null || echo "Columna internal_status ya existe o error"
sudo mysql -u root -p tu_base_de_datos -e "ALTER TABLE shipments ADD COLUMN user_id BIGINT UNSIGNED NULL;" 2>/dev/null || echo "Columna user_id ya existe o error"

## Paso 4: Limpiar cachés
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

## Paso 5: Reiniciar servicios
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx

