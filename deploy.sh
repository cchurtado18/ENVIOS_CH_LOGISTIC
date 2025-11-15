#!/bin/bash

# Script de deploy automático para producción
# Uso: ./deploy.sh

set -e  # Salir si hay algún error

echo "🚀 Iniciando despliegue..."
echo ""

# Colores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Error: No se encontró el archivo artisan. Asegúrate de estar en el directorio del proyecto.${NC}"
    exit 1
fi

# 1. Descartar cambios locales en archivos que no importan
echo -e "${YELLOW}📦 Paso 1/7: Limpiando archivos locales no importantes...${NC}"
git restore storage/logs/laravel.log 2>/dev/null || true
git restore database/database.sqlite 2>/dev/null || true
echo -e "${GREEN}✅ Limpieza completada${NC}"
echo ""

# 2. Verificar estado actual
echo -e "${YELLOW}📊 Paso 2/7: Verificando estado de Git...${NC}"
git status
echo ""

# 3. Hacer pull de los cambios
echo -e "${YELLOW}⬇️  Paso 3/7: Descargando cambios de GitHub...${NC}"
if git pull origin main; then
    echo -e "${GREEN}✅ Cambios descargados correctamente${NC}"
else
    echo -e "${RED}❌ Error al descargar cambios. Revisa los conflictos manualmente.${NC}"
    exit 1
fi
echo ""

# 4. Instalar/actualizar dependencias de Composer
echo -e "${YELLOW}📦 Paso 4/7: Instalando dependencias de Composer...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction
echo -e "${GREEN}✅ Dependencias instaladas${NC}"
echo ""

# 5. Ejecutar migraciones (solo las nuevas, automáticamente)
echo -e "${YELLOW}🗄️  Paso 5/7: Ejecutando migraciones de base de datos...${NC}"
if php artisan migrate --force; then
    echo -e "${GREEN}✅ Migraciones ejecutadas${NC}"
else
    echo -e "${RED}❌ Error al ejecutar migraciones. Revisa los logs.${NC}"
    exit 1
fi
echo ""

# 6. Limpiar cachés
echo -e "${YELLOW}🧹 Paso 6/7: Limpiando cachés de Laravel...${NC}"
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
echo -e "${GREEN}✅ Cachés limpiados${NC}"
echo ""

# 7. Optimizar aplicación
echo -e "${YELLOW}⚡ Paso 7/7: Optimizando aplicación...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo -e "${GREEN}✅ Aplicación optimizada${NC}"
echo ""

echo -e "${GREEN}🎉 ¡Despliegue completado exitosamente!${NC}"
echo ""
echo "✅ Cambios aplicados"
echo "✅ Migraciones ejecutadas (solo las nuevas)"
echo "✅ Cachés limpiados y optimizados"
echo ""
echo "💡 Tip: Verifica que tu sitio funcione correctamente visitando la URL."

