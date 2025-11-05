# Guía de Despliegue - CH Logistic API

## 📋 Requisitos del Servidor

- PHP 8.2 o superior
- MySQL 5.7+ o PostgreSQL 10+
- Composer
- Node.js 18+ (para assets, si aplica)
- Cron configurado
- Servidor web (Nginx o Apache)
- Extensión PHP OpenSSL
- Extensión PHP PDO
- Extensión PHP Mbstring
- Extensión PHP XML
- Extensión PHP Ctype
- Extensión PHP JSON
- Extensión PHP Fileinfo
- Extensión PHP cURL (para scraping)

---

## 🚀 Pasos de Despliegue

### 1. Subir el Código al Servidor

```bash
# Opción A: Clonar desde Git
git clone tu-repositorio.git
cd ch-logistic-api

# Opción B: Subir archivos via FTP/SFTP
```

### 2. Instalar Dependencias

```bash
# Instalar dependencias de producción (sin dev)
composer install --no-dev --optimize-autoloader
```

### 3. Configurar Variables de Entorno

```bash
# Copiar archivo .env.example a .env
cp .env.example .env

# Editar .env con tus configuraciones
nano .env
```

**Configuraciones importantes en `.env`:**

```env
APP_NAME="CH Logistic"
APP_ENV=production
APP_KEY=base64:tu-clave-aqui
APP_DEBUG=false
APP_URL=https://tudominio.com

# Base de datos (MySQL recomendado)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ch_logistic
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password

# Email (Gmail, SendGrid, etc.)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-password-app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME="${APP_NAME}"

# Sesiones
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true

# Cache
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### 4. Generar Clave de Aplicación

```bash
php artisan key:generate
```

### 5. Ejecutar Migraciones

```bash
php artisan migrate --force
```

### 6. Crear Usuario Administrador

```bash
php artisan tinker
```

Luego en tinker:

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'name' => 'Admin',
    'email' => 'admin@chlogistic.com',
    'password' => Hash::make('tu_password_seguro'),
    'phone' => '1234567890',
    'department' => 'Administración',
    'address' => 'Dirección del admin',
    'role' => 'admin'
]);

exit
```

### 7. Optimizar para Producción

```bash
# Cachear configuración
php artisan config:cache

# Cachear rutas
php artisan route:cache

# Cachear vistas
php artisan view:cache

# Optimizar autoloader
composer dump-autoload --optimize
```

### 8. Configurar Permisos

```bash
# Dar permisos de escritura a storage y cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Si usas Apache, cambiar el propietario
chown -R www-data:www-data storage bootstrap/cache
```

### 9. Configurar Cron Job

El cron es **crítico** para que funcionen las actualizaciones automáticas de tracking.

Agregar al crontab del servidor:

```bash
crontab -e
```

Agregar esta línea:

```bash
* * * * * cd /ruta/completa/a/tu/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

**Ejemplo:**
```bash
* * * * * cd /var/www/ch-logistic-api && php artisan schedule:run >> /dev/null 2>&1
```

Verificar que cron esté funcionando:
```bash
php artisan schedule:list
```

### 10. Configurar Servidor Web

#### Nginx

Crear archivo de configuración en `/etc/nginx/sites-available/ch-logistic`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name tudominio.com www.tudominio.com;
    root /ruta/a/ch-logistic-api/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Activar configuración:
```bash
ln -s /etc/nginx/sites-available/ch-logistic /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

#### Apache

Crear archivo `.htaccess` en `public/.htaccess`:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

Configurar virtual host en Apache.

### 11. Configurar SSL (HTTPS)

**Recomendado: Let's Encrypt**

```bash
# Instalar certbot
sudo apt-get update
sudo apt-get install certbot python3-certbot-nginx

# Generar certificado
sudo certbot --nginx -d tudominio.com -d www.tudominio.com

# Renovar automáticamente
sudo certbot renew --dry-run
```

Después de SSL, actualizar `.env`:
```env
SESSION_SECURE_COOKIE=true
```

---

## 🔍 Verificación Post-Despliegue

### 1. Verificar Aplicación

```bash
# Revisar logs
tail -f storage/logs/laravel.log

# Probar ruta de health check
curl https://tudominio.com/up
```

### 2. Verificar Cron

```bash
# Ver cron configurado
crontab -l

# Ejecutar manualmente para probar
php artisan schedule:run

# Verificar logs del scheduler
tail -f storage/logs/scheduler.log
```

### 3. Verificar Base de Datos

```bash
php artisan db:show
```

### 4. Probar Funcionalidades

1. **Tracking Público**: https://tudominio.com/track
2. **Login**: https://tudominio.com/login
3. **Dashboard**: https://tudominio.com/dashboard
4. **Admin**: https://tudominio.com/admin

---

## 🔐 Seguridad

### Checklist de Seguridad

- ✅ `APP_DEBUG=false` en producción
- ✅ `APP_ENV=production`
- ✅ HTTPS configurado
- ✅ `.env` no subido a Git (ya está en `.gitignore`)
- ✅ Permisos correctos en storage y bootstrap
- ✅ Cron configurado para scheduler
- ✅ Passwords fuertes para admin
- ✅ Firewall configurado en servidor
- ✅ Backups automáticos de base de datos

### Configuraciones Adicionales

Agregar a `.env`:
```env
# Configuración de sesiones seguras
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# Headers de seguridad (configurar en servidor web)
```

---

## 🔄 Actualizaciones Futuras

Cuando subas actualizaciones:

```bash
# 1. Mantener configuración
git pull origin main

# 2. Instalar nuevas dependencias
composer install --no-dev --optimize-autoloader

# 3. Ejecutar nuevas migraciones
php artisan migrate --force

# 4. Limpiar y recachear
php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Si hay cambios en JS/CSS (si aplica)
npm install
npm run build
```

---

## 📞 Soporte

Si encuentras problemas:

1. Revisa logs: `storage/logs/laravel.log`
2. Verifica permisos: `storage/` y `bootstrap/cache/`
3. Verifica cron: `php artisan schedule:list`
4. Verifica base de datos: `php artisan db:show`
5. Revisa configuración: `php artisan config:show`

---

## 🎉 ¡Sistema Listo!

Tu sistema de tracking está desplegado y funcionando.

**Funcionalidades activas:**
- ✅ Tracking público
- ✅ Sistema de autenticación
- ✅ Dashboard de cliente
- ✅ Panel de administrador
- ✅ Notificaciones por email
- ✅ Actualizaciones automáticas (cada hora)
- ✅ Procesamiento de pending trackings (cada 30 min)

