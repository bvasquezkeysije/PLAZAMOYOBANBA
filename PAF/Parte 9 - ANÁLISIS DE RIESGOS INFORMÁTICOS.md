# ANÁLISIS DE RIESGOS INFORMÁTICOS

A continuación se identifican y evalúan los riesgos que amenazan la integridad, disponibilidad y confidencialidad de la información del Sistema PlazaMoyobamba, y que impactan en salud, seguridad, normas legales y aspectos culturales de la organización.

## Matriz de riesgos

| Riesgo | Probabilidad | Impacto | Nivel | Acción preventiva | Evidencia |
|---|---|---|---|---|---|
| Pérdida de datos de huéspedes por SQLi | Alta | Alto | Crítico | Consultas parametrizadas + WAF | Prueba de penetración SQLi |
| Escalación de privilegios no autorizada | Alta | Alto | Crítico | Protección Mass Assignment ($guarded) | Prueba de penetración Mass Assignment |
| Ejecución remota de código (RCE) por subida de archivos | Alta | Crítico | Crítico | Validación de tipo de archivo + almacenamiento externo | Webshell shell.php subida |
| Robo de sesiones por XSS almacenado | Media | Alto | Alto | Sanitización de salida (Blade {{ }}) + CSP | Script inyectado en bio |
| Acceso no autorizado a datos de ventas (IDOR) | Alta | Alto | Alto | Políticas de autorización (Laravel Policies) | Acceso a /api/sales/121 sin auth |
| Denegación de servicio (DDoS) | Media | Alto | Alto | Rate limiting + límites de conexión Nginx | Slowloris con 300 conexiones |
| Exposición de credenciales por Debug Mode | Alta | Crítico | Crítico | APP_DEBUG=false en producción | Stack trace con credenciales BD |
| Filtración de archivos sensibles (LFI) | Alta | Alto | Alto | Validación de path en endpoint /download | Lectura de .env y /etc/passwd |
