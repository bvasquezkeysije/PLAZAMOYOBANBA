# CONCLUSIONES

1. Se identificaron y documentaron **9 vulnerabilidades críticas** en el sistema de gestión hotelera PlazaMoyobamba, todas pertenecientes al OWASP Top 10: SQL Injection, Mass Assignment, File Upload → RCE, Cross-Site Scripting (XSS) Almacenado, Referencia Directa Insegura (IDOR), Falta de Rate Limiting, Directory Traversal (LFI), DDoS por falta de protección y Debug Mode activado en producción. Cada vulnerabilidad fue explotada exitosamente en un entorno controlado, demostrando su impacto real.

2. La vulnerabilidad más crítica resultó ser la **subida de archivos maliciosos sin autenticación** (File Upload → RCE), que permitió obtener una webshell persistente en el servidor con permisos de www-data, desde la cual se pudo ejecutar comandos arbitrarios, leer archivos sensibles (.env, /etc/passwd) y establecer persistencia en el sistema.

3. El **Debug Mode activado** (APP_DEBUG=true) expuso información sensible del framework Laravel 12.58.0, incluyendo stack traces completos, variables de entorno con credenciales de base de datos, rutas internas del servidor y consultas SQL ejecutadas, facilitando significativamente el reconocimiento y la explotación de otras vulnerabilidades.

4. La falta de controles de **rate limiting** y **protección DDoS** dejó el servidor expuesto a ataques de denegación de servicio, verificándose que 300 conexiones simultáneas mediante Slowloris fueron suficientes para mantener ocupados los recursos del servidor sin que este cerrara las conexiones maliciosas.

5. Se comprobó que la combinación de múltiples vulnerabilidades permite un **ataque en cadena**: el Debug Mode facilita el reconocimiento, el File Upload permite la ejecución remota de comandos, y el Mass Assignment posibilita la escalación a administrador, lo que compromete totalmente la confidencialidad, integridad y disponibilidad del sistema.
