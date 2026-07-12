# ANEXOS

## Anexo A: Evidencias de vulnerabilidades

| # | Vulnerabilidad | Archivo de evidencia | Descripción |
|---|---------------|---------------------|-------------|
| 1 | SQL Injection | `ATAQUES/02 - SQL Injection.md` | Login bypass + extracción de datos (26 tablas, 10 usuarios) |
| 2 | Mass Assignment | `ATAQUES/03 - Mass Assignment.md` | Escalación a admin vía PATCH /profile |
| 3 | File Upload → RCE | `ATAQUES/04 - File Upload RCE.md` | Webshell subida a /uploads/shell.php |
| 4 | XSS Almacenado | `ATAQUES/05 - XSS Almacenado.md` | Script injectado en campo bio del perfil |
| 5 | IDOR | `ATAQUES/06 - IDOR.md` | Acceso a /api/sales/121 sin autenticación |
| 6 | DDoS (Slowloris) | `ATAQUES/07 - DDoS Attack.md` | 300 conexiones simultáneas sin bloqueo |
| 7 | No Rate Limit | `ATAQUES/08 - No Rate Limit.md` | 30 requests en paralelo sin throttling |
| 8 | LFI / Path Traversal | `ATAQUES/09 - LFI Directory Traversal.md` | Lectura de .env y /etc/passwd |
| 9 | Debug Mode | `ATAQUES/10 - Debug Mode.md` | Stack trace con credenciales y rutas |

## Anexo B: Comandos de explotación verificados

Todos los comandos de explotación se encuentran documentados en los archivos individuales de cada vulnerabilidad dentro del directorio `ATAQUES/`, así como en la Fase 3 de la metodología PTES (`ATAQUES/Fase 3 - Obtencion de acceso.md`).

## Anexo C: Capturas de pantalla

Las capturas de pantalla de cada exploit (login bypass, shell.php funcionando, stack trace, etc.) deben adjuntarse impresas o en formato digital según los requisitos de presentación del informe final.

## Anexo D: Base de datos restaurada

El punto de restauración de la base de datos se encuentra respaldado en el VPS en `/root/backup_paf_20260712.sql` y etiquetado en el repositorio Git como `restore-point-20260712`.
