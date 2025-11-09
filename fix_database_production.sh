#!/bin/bash
# Script para corregir la base de datos en producción

cd /var/www/ch-logistic-api

echo "Obteniendo credenciales de la base de datos..."
DB_NAME=$(grep DB_DATABASE .env | cut -d '=' -f2)
DB_USER=$(grep DB_USERNAME .env | cut -d '=' -f2)
DB_PASS=$(grep DB_PASSWORD .env | cut -d '=' -f2)

echo "Base de datos: $DB_NAME"
echo "Usuario: $DB_USER"
echo ""

echo "Ejecutando script SQL para corregir columnas faltantes..."
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < fix_database_production.sql

if [ $? -eq 0 ]; then
    echo "✅ Base de datos corregida exitosamente"
else
    echo "❌ Error al ejecutar el script SQL"
    echo "Intentando con sudo mysql..."
    sudo mysql -u root -p "$DB_NAME" < fix_database_production.sql
fi

echo ""
echo "Limpiando cachés de Laravel..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo ""
echo "✅ Proceso completado. Intenta iniciar sesión ahora."




