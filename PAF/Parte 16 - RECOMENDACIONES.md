# RECOMENDACIONES

1. **Deshabilitar APP_DEBUG en producción:** Cambiar `APP_DEBUG=false` en el archivo `.env` del contenedor y reconstruir la aplicación. Esto evita la exposición de stack traces, variables de entorno y rutas internas del servidor.

2. **Implementar validación estricta de tipos de archivo en uploads:** Restringir extensiones permitidas únicamente a formatos de imagen (jpg, png, gif, webp), validar el MIME type del lado del servidor (no solo del cliente), y almacenar los archivos fuera del documento root o con ejecución de scripts deshabilitada.

3. **Proteger endpoints contra Mass Assignment:** Revisar todos los modelos de Eloquent para asegurar que la propiedad `$guarded` o `$fillable` esté correctamente definida, y evitar enviar campos sensibles como `role_name`, `is_admin` desde el request. Usar siempre `Request->validated()` en lugar de `Request->all()`.

4. **Implementar rate limiting en login y API:** Configurar Laravel's built-in `throttle` middleware en las rutas de login y API para limitar el número de intentos por IP por minuto. Complementar con un WAF (Web Application Firewall) como Cloudflare, ModSecurity o AWS WAF.

5. **Sanitizar entradas de usuario contra XSS y SQLi:** Usar consultas parametrizadas (Eloquent ORM ya lo hace, pero verificar queries raw) y escapar toda salida HTML con `{{ }}` de Blade (no usar `{!! !!}` sin saneamiento). Implementar Content Security Policy (CSP) headers.

6. **Implementar control de acceso en APIs (IDOR):** Verificar que el usuario autenticado tenga permisos sobre el recurso solicitado mediante políticas de autorización (Laravel Policies) en cada endpoint que exponga datos sensibles (ventas, perfiles, reservas).

7. **Proteger contra DDoS:** Configurar límites de conexión simultánea en Nginx (`limit_conn`), tiempo de espera (`client_body_timeout`), y considerar un servicio de mitigación DDoS como Cloudflare.

8. **Realizar auditorías periódicas:** Ejecutar análisis de seguridad automatizados (OWASP ZAP, Nikto, WPScan si aplica) de forma mensual y pruebas de penetración manuales de forma trimestral para identificar nuevas vulnerabilidades.
