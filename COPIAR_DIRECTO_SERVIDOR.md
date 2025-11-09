# CÓMO COPIAR EL PROYECTO DIRECTAMENTE AL SERVIDOR

## Opción 1: Usar SCP (desde tu máquina local)

```bash
# Desde tu máquina local, copia todo el proyecto:
scp -r /Users/carloshurtado/Documents/ch-logistic-api/* root@TU_IP_SERVIDOR:/var/www/ch-logistic-api/

# O copiar todo el directorio:
scp -r /Users/carloshurtado/Documents/ch-logistic-api root@TU_IP_SERVIDOR:/var/www/
```

## Opción 2: Usar rsync (mejor, solo copia cambios)

```bash
rsync -avz --exclude 'vendor' --exclude 'node_modules' --exclude '.git' \
  /Users/carloshurtado/Documents/ch-logistic-api/ \
  root@TU_IP_SERVIDOR:/var/www/ch-logistic-api/
```

## Opción 3: Crear un ZIP y subirlo

```bash
# En tu máquina local:
cd /Users/carloshurtado/Documents/ch-logistic-api
tar -czf proyecto.tar.gz --exclude='vendor' --exclude='node_modules' --exclude='.git' .

# Luego en el servidor:
# 1. Sube el archivo proyecto.tar.gz
# 2. En el servidor ejecuta:
cd /var/www/ch-logistic-api
tar -xzf proyecto.tar.gz
```

## Después de copiar, en el servidor:

```bash
cd /var/www/ch-logistic-api
composer install --no-dev --optimize-autoloader
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan migrate --force
sudo systemctl restart php8.2-fpm
```

