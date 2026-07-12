# Parte 1 - Reconocimiento (Reconnaissance)

**Fase PTES:** Recolección pasiva y activa de información del objetivo.
**Target:** http://37.60.230.11
**Fecha:** 2026-07-12

---

## Información General

| Dato | Valor |
|------|-------|
| **URL** | http://37.60.230.11 |
| **IP** | 37.60.230.11 |
| **Hostname** | vmi3426663.contaboserver.net |
| **País** | Rumania (RO) |
| **Servidor web** | nginx 1.27.5 |
| **Backend** | PHP 8.2.32 |
| **Framework** | Laravel (cookies XSRF-TOKEN, sistema-plazamoyobanba-session) |
| **Base de datos** | PostgreSQL 16 |
| **Sistema operativo** | Linux (Ubuntu - SSH OpenSSH 9.6p1) |
| **Infraestructura** | Docker Compose (Nginx + PHP-FPM Alpine + PostgreSQL Alpine) |

---

## Puertos abiertos

| Puerto | Servicio | Versión | Estado |
|--------|----------|---------|--------|
| 22/tcp | SSH | OpenSSH 9.6p1 Ubuntu | Abierto |
| 80/tcp | HTTP | nginx 1.27.5 | Abierto |
| 5432/tcp | PostgreSQL | - | Abierto |

**Nota:** El puerto 5432 (PostgreSQL) está expuesto públicamente. Esto es crítico porque permite conexión directa a la BD si se obtienen credenciales.

---

## Detección de tecnologías (whatweb)

```bash
whatweb http://37.60.230.11
```

```
http://37.60.230.11 [302 Found]
  Cookies[XSRF-TOKEN, sistema-plazamoyobanba-session]
  Country[ROMANIA][RO], HTML5
  HTTPServer[nginx/1.27.5], IP[37.60.230.11]
  Meta-Refresh-Redirect[/login]
  PHP[8.2.32], RedirectLocation[/login]
  Title[Redirecting to /login]
  X-Powered-By[PHP/8.2.32], nginx[1.27.5]

http://37.60.230.11/login [200 OK]
  Cookies[XSRF-TOKEN, sistema-plazamoyobanba-session]
  PasswordField[password], Script[module]
  Title[Sistema PlazaMoyobanba]
  X-Powered-By[PHP/8.2.32]
```

### Análisis de tecnologías
- **PHP 8.2.32** ligeramente desactualizado (último 8.5.1)
- **Laravel** identificado por cookies XSRF-TOKEN (framework)
- **nginx 1.27.5** como servidor web
- **Formulario de login** con campo password detectado automáticamente
- **CSRF Token** requerido en todos los formularios POST

---

## Detección de CSRF Token

Solo con whatweb y curl detectamos que el login requiere token CSRF:

1. **whatweb** muestra cookie `XSRF-TOKEN` → Laravel usa CSRF
2. **curl GET /login** → encontramos campo oculto `_token` en el HTML
3. **curl POST sin token** → HTTP 419 (CSRF mismatch)

```bash
# Ver campo _token en el HTML
curl -s http://37.60.230.11/login | grep _token

# Probar POST sin token → 419
curl -s -X POST http://37.60.230.11/login \
  -d "login=admin&password=test" \
  -o /dev/null -w "%{http_code}"
# Resultado: 419
```

---

## Rutas descubiertas (Gobuster)

```bash
gobuster dir -u http://37.60.230.11 -w /usr/share/wordlists/common.txt -t 20
```

| Ruta | Estado | Descripción |
|------|--------|-------------|
| `/login` | 200 | Formulario de inicio de sesión |
| `/register` | 200 | Registro de usuarios (abierto al público) |
| `/forgot-password` | 200 | Formulario de recuperación de contraseña |
| `/up` | 200 | Health check del sistema |
| `/robots.txt` | 200 | Sin restricciones |
| `/favicon.ico` | 200 | Icono del sitio |
| `/upload` | 405 | Subida de archivos (método POST) |
| `/logout` | 405 | Cierre de sesión (método POST) |
| `/password` | 405 | Cambio de contraseña (método POST) |
| `/profile` | 302 | Perfil de usuario (requiere autenticación) |
| `/dashboard` | 302 | Dashboard admin (requiere autenticación) |
| `/images/` | 301 | Directorio de imágenes (403 listado) |
| `/build/` | 301 | Directorio de builds (403 listado) |
| `/uploads/` | 301 | Directorio de archivos subidos (403 listado) |

### Rutas administrativas bajo /admin/

| Ruta | Estado | Descripción |
|------|--------|-------------|
| `/admin/dashboard` | 302 | Panel principal |
| `/admin/clientes` | 302 | CRUD de clientes |
| `/admin/usuarios` | 302 | Gestión de usuarios |

---

## Headers de seguridad (ausentes)

```
strict-transport-security:  NO
referrer-policy:            NO
permissions-policy:         NO
content-security-policy:    NO
x-content-type-options:     NO
X-Frame-Options:            NO
X-XSS-Protection:           NO
```

---

## Resumen de fase de reconocimiento

| Hallazgo | Detalle |
|----------|---------|
| **Tecnologías** | nginx 1.27.5 + PHP 8.2.32 + Laravel + PostgreSQL 16 |
| **Puerto crítico** | 5432 PostgreSQL expuesto públicamente |
| **Puntos de entrada** | /login, /register, /upload, /forgot-password |
| **CSRF habilitado** | Sí, token requerido en POST |
| **Registro abierto** | Sí, cualquiera puede crear cuenta |
| **IP interna expuesta** | 172.18.0.4 (contenedor Docker) en /images |
| **PHP desactualizado** | 8.2.32 (RCE público en < 8.3.8) |
