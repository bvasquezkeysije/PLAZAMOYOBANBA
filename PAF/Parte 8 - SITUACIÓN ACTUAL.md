# SITUACIÓN ACTUAL

## Reseña de la empresa

El Hotel "PLAZA MOYOBAMBA" es una organización dedicada a la prestación de servicios de alojamiento y atención turística integral en la región Lambayeque, Perú. Opera bajo una estructura organizativa que permite la gestión eficiente de sus recursos y la seguridad de la información de sus huéspedes. Su dirección es Calle Ollantay Cuadra 1, 14007 La Victoria, Perú, Chiclayo, Peru, 14007.

## Organigrama del área de TI

El área de Soporte TI depende directamente de la Gerencia General y está conformada por un administrador de sistemas encargado de la infraestructura digital, el dominio corporativo y el mantenimiento del sistema de gestión hotelera (Sistema PlazaMoyobamba). No existe un equipo de seguridad informática dedicado ni un CISO (Chief Information Security Officer).

## Diagnóstico inicial del estado de seguridad

El sistema PlazaMoyobamba presentaba al inicio del proyecto las siguientes condiciones críticas:

- **Sin autenticación robusta:** El login era vulnerable a SQL Injection, permitiendo el acceso sin credenciales válidas.
- **Sin control de acceso:** Cualquier usuario registrado podía escalar a administrador mediante Mass Assignment.
- **Sin validación de archivos:** El endpoint de subida de fotos de perfil aceptaba cualquier tipo de archivo sin autenticación.
- **Sin sanitización de entradas:** Los campos de texto permitían inyección de scripts (XSS almacenado).
- **Sin rate limiting:** El endpoint de login no limitaba intentos, facilitando ataques de fuerza bruta.
- **Sin protección DDoS:** El servidor no implementaba límites de conexión ni timeouts.
- **Sin control de acceso en APIs:** Los endpoints REST devolvían datos sensibles sin verificar autorización.
- **Debug Mode activado en producción:** APP_DEBUG=true exponía información sensible del servidor.

## Recursos tecnológicos actuales

| Recurso | Especificación |
|---------|---------------|
| Servidor | VPS Ubuntu 22.04, 2 vCPU, 4 GB RAM |
| Contenedorización | Docker 24.x (3 contenedores: app, web, db) |
| Framework backend | Laravel 12.58.0 / PHP 8.2.32 |
| Servidor web | Nginx 1.24 |
| Base de datos | PostgreSQL 16.14 |
| Almacenamiento | 50 GB SSD |
