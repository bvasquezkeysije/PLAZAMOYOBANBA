# ANÁLISIS DE RESULTADOS

A continuación se presentan los resultados obtenidos tras la ejecución de las pruebas de penetración sobre el sistema Hotel PlazaMoyobamba, comparando la situación inicial (antes de las medidas correctivas propuestas) con la situación final esperada.

| Medidas de seguridad | Indicador | Valor inicial | Valor final | Mejora | Evidencia |
|---|---|---|---|---|---|
| Parche de SQLi (consultas parametrizadas + validación) | Login bypass exitoso | 100% (login sin credenciales válidas) | 0% | Eliminación total | Anexo: captura login bloqueado |
| Protección Mass Assignment ($guarded en modelos) | Escalación a admin vía PATCH | 100% (role_name modificable) | 0% | Eliminación total | Anexo: respuesta 422 al intentar escalar |
| Validación de tipo de archivo en uploads | Subida de webshell (RCE) | 100% (shell.php subida y ejecutada) | 0% | Eliminación total | Anexo: shell.php rechazado |
| Sanitización de salida (Blade {{ }}) + CSP headers | XSS almacenado persistente | Script ejecutado en perfil | 0% | Eliminación total | Anexo: script no renderizado |
| Implementación de rate limiting (throttle middleware) | Requests POST en paralelo sin bloqueo | 30 requests en 1583ms sin límite | 30 requests bloqueados tras N intentos | -- | Anexo: respuesta 429 Too Many Attempts |
| Control de acceso por políticas (Laravel Policies) | Acceso a /api/sales/{id} sin auth | 120 registros accesibles sin autenticar | 0 accesos sin token | Eliminación total | Anexo: 401 Unauthorized |
| Validación de path en endpoint /download | Lectura de archivos fuera del webroot | 100% (../.env, /etc/passwd legibles) | 0% | Eliminación total | Anexo: path traversal bloqueado |
| Configuración Nginx: limit_conn, timeouts, WAF | Conexiones Slowloris (DDoS) | 300 conexiones sin cerrar | Conexiones cerradas al exceder límite | Mitigación parcial | Anexo: conexiones rechazadas |
| APP_DEBUG=false + desactivación de errores detallados | Stack trace con credenciales expuestas | 100% (credenciales BD visibles) | 0% | Eliminación total | Anexo: error genérico sin stack trace |

