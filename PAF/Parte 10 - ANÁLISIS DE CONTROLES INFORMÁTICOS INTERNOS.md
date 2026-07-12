# ANÁLISIS DE CONTROLES INFORMÁTICOS INTERNOS

Se evalúan los mecanismos internos que el Hotel PlazaMoyobamba tiene para asegurar la información.

| Control | Descripción | Estado actual | Mejora propuesta | Evidencia |
|---|---|---|---|---|
| Control de acceso por roles | Usuarios con roles (admin, user) | Deficiente: role_name modificable vía API sin restricción | Implementar $guarded en modelos + Policies | Mass Assignment exitoso: admin3 creado |
| Autenticación de usuarios | Login con email + password | Deficiente: sin rate limiting, vulnerable a SQLi | Rate limiting (throttle) + consultas parametrizadas | SQLi bypass exitoso |
| Validación de archivos subidos | Subida de foto de perfil | Deficiente: sin validación de tipo ni autenticación | Validar MIME type + extensiones + requerir auth | Webshell shell.php subida y ejecutada |
| Sanitización de entradas | Campos de texto (bio, nombre) | Deficiente: sin sanitización, XSS posible | Escapar salida con {{ }} + CSP headers | XSS almacenado en campo bio |
| Registro de eventos (logging) | Logs de Laravel (storage/logs) | Parcial: logs existentes pero sin rotación ni monitoreo | Implementar rotación de logs + monitoreo centralizado | Logs accesibles por LFI |
| Protección contra DDoS | — | Inexistente: sin límites de conexión | Configurar limit_conn en Nginx + Cloudflare | Slowloris mantuvo 300 conexiones |
| Respaldo y recuperación | Backup de BD | Sí, backup manual en VPS | Automatizar backups diarios + probar restauración | backup_paf_*.sql en /root |
| Modo debug en producción | APP_DEBUG | Activado (true) | Desactivar (false) | Stack trace con credenciales expuestas |
