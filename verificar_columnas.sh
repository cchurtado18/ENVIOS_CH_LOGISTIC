#!/bin/bash
# Script para verificar qué columnas tiene la tabla shipments

cd /var/www/ch-logistic-api

# Obtener credenciales del .env
DB_NAME=$(grep DB_DATABASE .env | cut -d '=' -f2)
DB_USER=$(grep DB_USERNAME .env | cut -d '=' -f2)

echo "Verificando columnas de la tabla 'shipments'..."
echo "Base de datos: $DB_NAME"
echo ""
echo "Ejecutando: DESCRIBE shipments;"
echo ""

sudo mysql -u $DB_USER -p $DB_NAME -e "DESCRIBE shipments;" 2>/dev/null || mysql -u $DB_USER -p$DB_PASS $DB_NAME -e "DESCRIBE shipments;"

echo ""
echo "--- Verificando columnas específicas ---"
echo ""

# Verificar metadata
sudo mysql -u $DB_USER -p $DB_NAME -e "SELECT COUNT(*) as existe FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$DB_NAME' AND TABLE_NAME = 'shipments' AND COLUMN_NAME = 'metadata';" 2>/dev/null || echo "metadata: No se pudo verificar"

# Verificar internal_status
sudo mysql -u $DB_USER -p $DB_NAME -e "SELECT COUNT(*) as existe FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$DB_NAME' AND TABLE_NAME = 'shipments' AND COLUMN_NAME = 'internal_status';" 2>/dev/null || echo "internal_status: No se pudo verificar"

# Verificar user_id
sudo mysql -u $DB_USER -p $DB_NAME -e "SELECT COUNT(*) as existe FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$DB_NAME' AND TABLE_NAME = 'shipments' AND COLUMN_NAME = 'user_id';" 2>/dev/null || echo "user_id: No se pudo verificar"

