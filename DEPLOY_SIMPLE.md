# 🚀 CÓMO SUBIR CAMBIOS: Guía Simple

## 📌 **EL PROBLEMA QUE TENÍAS:**

Hiciste:
1. ✅ `git push` en localhost → Cambios subidos a GitHub
2. ✅ `git pull` en servidor → Archivos descargados
3. ❌ **Pero los cambios NO se vieron** → ¿Por qué?

**RAZÓN:** Laravel tiene **cachés** que guardan versiones viejas de tus archivos. Aunque descargaste los archivos nuevos con `git pull`, Laravel sigue usando los cachés viejos.

---

## ✅ **LA SOLUCIÓN SIMPLE:**

Después de hacer `git pull` en el servidor, **SIEMPRE** ejecuta estos comandos:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

Esto limpia los cachés y Laravel usa tus archivos nuevos.

---

## 📝 **PROCESO COMPLETO (Paso a Paso):**

### **PASO 1: En tu LOCALHOST**

```bash
cd /Users/carloshurtado/Documents/ch-logistic-api

# Ver qué cambió
git status

# Agregar cambios
git add .

# Hacer commit
git commit -m "Descripción de los cambios"

# Subir a GitHub
git push origin main
```

✅ Si ves algo como "Writing objects: 100%", está bien subido.

---

### **PASO 2: En el SERVIDOR**

```bash
# 1. Conectarte al servidor
ssh root@tu-servidor-ip

# 2. Ir al proyecto
cd /var/www/ch-logistic-api

# 3. Descartar cambios en archivos que no importan (logs, etc)
git restore storage/logs/laravel.log 2>/dev/null || true

# 4. Descargar cambios de GitHub
git pull origin main

# 5. ⭐ IMPORTANTE: Limpiar cachés (esto es lo que faltaba antes)
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

✅ **Listo.** Ahora tus cambios se verán en producción.

---

## 🔄 **SOLUCIÓN AUTOMÁTICA (Mejor):**

En vez de hacer esos 4 comandos cada vez, usa el script que creé:

```bash
cd /var/www/ch-logistic-api
./deploy.sh
```

Este script hace **todo automáticamente**:
- Descarta archivos temporales
- Hace `git pull`
- Limpia todos los cachés
- Ejecuta migraciones (si hay nuevas)
- Optimiza la aplicación

**Solo una vez, haz el script ejecutable:**
```bash
chmod +x deploy.sh
```

---

## ❓ **PREGUNTAS:**

### **¿Por qué necesito limpiar cachés?**
Laravel guarda versiones compiladas de tus archivos en caché para que sea más rápido. Cuando cambias un archivo, necesitas limpiar el caché para que use la versión nueva.

### **¿Se pierden datos de la base de datos?**
**NO.** Solo limpias cachés, no tocas la base de datos.

### **¿Qué pasa si hay nuevas migraciones?**
El script `deploy.sh` las ejecuta automáticamente. Solo aplica las nuevas, no afecta datos existentes.

### **¿Puedo hacer deploy varias veces?**
**Sí**, todas las veces que quieras. Es seguro.

---

## 🎯 **RESUMEN:**

**Antes (NO funcionaba):**
```
Localhost: git push
Servidor: git pull
Resultado: ❌ Cambios no se ven
```

**Ahora (SÍ funciona):**
```
Localhost: git push
Servidor: git pull
Servidor: php artisan config:clear
Servidor: php artisan cache:clear
Servidor: php artisan view:clear
Servidor: php artisan route:clear
Resultado: ✅ Cambios se ven

O MEJOR:
Servidor: ./deploy.sh
Resultado: ✅ Todo automático
```

---

## 🚨 **SI AÚN NO FUNCIONA:**

1. Verifica que los archivos se descargaron:
   ```bash
   git status
   git log --oneline -1
   ```

2. Verifica permisos:
   ```bash
   ls -la storage/
   chmod -R 775 storage/
   chmod -R 775 bootstrap/cache/
   ```

3. Revisa errores:
   ```bash
   tail -f storage/logs/laravel.log
   ```

---

¡Eso es todo! Ahora tus cambios se verán correctamente. 🎉

