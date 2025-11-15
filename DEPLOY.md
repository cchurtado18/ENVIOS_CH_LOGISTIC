# 🚀 Guía de Despliegue: Localhost → Producción

Esta guía explica cómo subir los cambios de tu localhost a producción de manera segura y automática.

---

## 📋 **FLUJO GENERAL**

```
Localhost (tu computadora)
    ↓ [git add, commit, push]
GitHub (repositorio)
    ↓ [git pull + comandos automáticos]
Servidor (producción)
```

---

## 🔵 **PASO 1: EN TU LOCALHOST (Tu computadora)**

### 1.1. Verificar que tienes cambios para subir
```bash
cd /Users/carloshurtado/Documents/ch-logistic-api
git status
```

### 1.2. Agregar los cambios
```bash
# Agregar todos los archivos modificados (excepto los ignorados)
git add .

# O agregar archivos específicos:
# git add app/Http/Controllers/AdminController.php
# git add resources/views/admin/index.blade.php
```

### 1.3. Hacer commit con un mensaje descriptivo
```bash
git commit -m "Descripción de los cambios que hiciste"
```

**Ejemplos de mensajes:**
- `git commit -m "Agregar navegación banner en admin views"`
- `git commit -m "Mejorar responsive del dashboard"`
- `git commit -m "Fix: corregir error al eliminar factura"`

### 1.4. Subir los cambios a GitHub
```bash
git push origin main
```

✅ **Si todo sale bien, verás un mensaje como:**
```
Enumerating objects: X, done.
Writing objects: 100% (X/X), done.
To https://github.com/cchurtado18/ENVIOS_CH_LOGISTIC.git
   abc1234..def5678  main -> main
```

---

## 🟢 **PASO 2: EN EL SERVIDOR (Producción)**

### 2.1. Conectarte al servidor
```bash
ssh root@tu-servidor-ip
# o
ssh root@161.35.143.171
```

### 2.2. Ir al directorio del proyecto
```bash
cd /var/www/ch-logistic-api
```

### 2.3. Usar el script de deploy automático

He creado un script que hace todo automáticamente. Solo ejecuta:

```bash
./deploy.sh
```

**¿Qué hace este script automáticamente?**
1. ✅ Descarta cambios locales en archivos de log/storage (para evitar conflictos)
2. ✅ Descarga los últimos cambios de GitHub (`git pull`)
3. ✅ Instala/actualiza dependencias de Composer (si hay cambios)
4. ✅ Ejecuta migraciones automáticamente (solo las nuevas, sin perder datos)
5. ✅ Limpia todos los cachés de Laravel
6. ✅ Optimiza la aplicación

---

## ⚙️ **ALTERNATIVA MANUAL (Si prefieres hacerlo paso a paso)**

Si no quieres usar el script automático, puedes ejecutar estos comandos uno por uno:

```bash
cd /var/www/ch-logistic-api

# 1. Descartar cambios en archivos que no importan (logs, etc)
git restore storage/logs/laravel.log 2>/dev/null || true

# 2. Descargar cambios de GitHub
git pull origin main

# 3. Instalar/actualizar dependencias (solo si composer.json cambió)
composer install --no-dev --optimize-autoloader

# 4. Ejecutar migraciones (solo las nuevas, automáticamente)
php artisan migrate --force

# 5. Limpiar cachés
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# 6. Optimizar (opcional, pero recomendado)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## ❓ **PREGUNTAS FRECUENTES**

### **¿Se perderán datos de la base de datos?**
**NO.** Las migraciones de Laravel son inteligentes:
- Solo ejecutan las migraciones que aún no se han ejecutado
- Si una migración ya se ejecutó antes, se omite automáticamente
- Las migraciones son **incrementales**, no destructivas

### **¿Qué pasa si hay un error durante el pull?**
- Si hay conflictos con archivos de log/storage, el script los descarta automáticamente
- Si hay conflictos con código real, te avisará y deberás resolverlos manualmente (caso muy raro)

### **¿Necesito hacer backup antes de hacer deploy?**
- Para código: No es necesario, GitHub ya es tu backup
- Para base de datos: Es recomendable hacer backup periódicamente:
  ```bash
  # En el servidor:
  mysqldump -u usuario -p nombre_base_datos > backup_$(date +%Y%m%d).sql
  ```

### **¿Puedo hacer deploy varias veces al día?**
**Sí.** Puedes hacer deploy cuantas veces quieras. Solo se aplicarán los cambios nuevos.

### **¿Qué archivos NO se suben a producción?**
Los archivos en `.gitignore` no se suben:
- `.env` (configuración local del servidor)
- `storage/logs/*` (logs)
- `database/database.sqlite` (base de datos local)
- `vendor/` (se instala en el servidor)
- etc.

---

## 🔧 **COMANDOS ÚTILES**

### Ver el historial de commits
```bash
git log --oneline -10
```

### Ver qué archivos cambiaron en el último commit
```bash
git show --name-status HEAD
```

### Deshacer un commit (si te equivocaste ANTES de hacer push)
```bash
git reset --soft HEAD~1  # Mantiene los cambios
git reset --hard HEAD~1   # Elimina los cambios (¡cuidado!)
```

### Ver diferencias entre localhost y servidor
```bash
# En localhost:
git log origin/main..HEAD

# En servidor:
git log HEAD..origin/main
```

---

## 📞 **SI ALGO SALE MAL**

1. **Error de permisos en el servidor:**
   ```bash
   chown -R www-data:www-data /var/www/ch-logistic-api
   chmod -R 755 /var/www/ch-logistic-api
   chmod -R 775 /var/www/ch-logistic-api/storage
   chmod -R 775 /var/www/ch-logistic-api/bootstrap/cache
   ```

2. **Error al ejecutar migraciones:**
   ```bash
   php artisan migrate:status  # Ver estado de migraciones
   php artisan migrate --force # Forzar migración
   ```

3. **Si el sitio no carga después del deploy:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   sudo systemctl restart php8.x-fpm  # Reemplaza 8.x con tu versión
   ```

---

## ✅ **CHECKLIST ANTES DE HACER DEPLOY**

- [ ] Probé los cambios en localhost y funcionan bien
- [ ] Hice commit de todos los cambios importantes
- [ ] El mensaje del commit es claro y descriptivo
- [ ] Hice `git push origin main` exitosamente
- [ ] Me conecté al servidor
- [ ] Ejecuté `./deploy.sh` o los comandos manuales
- [ ] Verifiqué que el sitio sigue funcionando después del deploy

---

¡Listo! Con esta guía puedes hacer deploy de forma segura y sin complicaciones. 🎉

