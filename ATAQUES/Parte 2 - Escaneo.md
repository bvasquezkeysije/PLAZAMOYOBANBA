# Parte 2 - Escaneo (Scanning)

**Fase PTES:** Identificación activa de puertos, servicios, versiones y vulnerabilidades específicas.
**Target:** http://37.60.230.11
**Fecha:** 2026-07-12

---

## 1. Escaneo de servicios (Nmap -sV)

```bash
nmap -sV 37.60.230.11
```

```
PORT      STATE    SERVICE    VERSION
22/tcp    open     ssh        OpenSSH 9.6p1 Ubuntu 3ubuntu3.16
80/tcp    open     http       nginx 1.27.5
5432/tcp  open     postgresql PostgreSQL DB
```

### Puertos filtrados (bloqueados por firewall)
```
23/tcp    filtered telnet
25/tcp    filtered smtp
2323/tcp  filtered 3d-nfsd
5555/tcp  filtered freeciv
22939/tcp filtered unknown
```

**Análisis:** PostgreSQL expuesto en puerto 5432 es crítico. Si se obtienen credenciales vía SQLi, se puede acceder directamente a la BD desde cualquier IP.

---

## 2. Métodos HTTP permitidos

```bash
nmap --script http-methods 37.60.230.11
```

```
PORT   STATE SERVICE
80/tcp open  http
| http-methods:
|_  Supported Methods: GET HEAD POST
```

**Análisis:** Solo GET, HEAD y POST. No hay PUT/DELETE/PATCH expuestos. Configuración segura por defecto de Laravel.

---

## 3. Escaneo de vulnerabilidades web (Nikto)

```bash
nikto -h http://37.60.230.11
```

```
+ Server: nginx/1.27.5
+ /: Cookie XSRF-TOKEN created without the httponly flag.
+ /: Retrieved x-powered-by header: PHP/8.2.32.
+ PHP/8.2.32 appears to be outdated (current is at least 8.5.1).
+ /images: RFC-1918 IP address found: "172.18.0.4"
+ /images: Web server may reveal internal IP in Location header
+ /: Missing security headers:
    - strict-transport-security
    - referrer-policy
    - permissions-policy
    - content-security-policy
    - x-content-type-options
```

### Hallazgos nikto

| Hallazgo | Severidad | Descripción |
|----------|-----------|-------------|
| Cookie XSRF-TOKEN sin HttpOnly | Alta | Permite robo de token mediante XSS |
| PHP 8.2.32 desactualizado | Alta | RCE público para PHP < 8.3.8 |
| IP interna expuesta (172.18.0.4) | Media | Revela infraestructura Docker interna |
| Headers de seguridad ausentes | Media | HSTS, CSP, X-CT-O, etc. no configurados |

---

## 4. Búsqueda de exploits (SearchSploit)

```bash
searchsploit nginx 1.27.5
searchsploit php 8.2.32
searchsploit postgresql
```

### Resultados

**nginx 1.27.5:** Sin exploits públicos conocidos (versión reciente).

**PHP 8.2.32:**
| Exploit | Ruta |
|---------|------|
| PHP < 8.3.8 - Remote Code Execution (Unauthenticated) | `php/webapps/52047.py` |

**PostgreSQL:**
| Exploit | Ruta |
|---------|------|
| PostgreSQL 9.4-0.5.3 - Privilege Escalation | `linux/local/45184.sh` |
| PostgreSQL 9.3-11.7 - Remote Code Execution | `multiple/remote/46813.rb` |
| PostgreSQL 9.4-11.7 - RCE | `multiple/remote/50847.py` |
| PostgreSQL 9.6.1 - RCE | `multiple/remote/51247.py` |

**Análisis:** PHP < 8.3.8 tiene un RCE público. PostgreSQL tiene múltiples RCE si se obtienen credenciales. Combinado con el puerto 5432 expuesto, es crítico.

---

## 5. Fuzzing de rutas y parámetros (FFUF)

```bash
# Enumeración de rutas bajo /admin/
ffuf -w /usr/share/wordlists/common.txt \
  -u "http://37.60.230.11/admin/FUZZ" \
  -fc 404 -c -t 20
```

### Rutas descubiertas bajo /admin/

| Ruta | Estado |
|------|--------|
| `/admin/clientes` | 302 → /login |
| `/admin/dashboard` | 302 → /login |
| `/admin/usuarios` | 302 → /login |

Todas redirigen al login sin autenticación. Confirmación de rutas administrativas.

### Fuzzing de parámetros GET en login

```bash
ffuf -w /usr/share/wordlists/common.txt \
  -u "http://37.60.230.11/login?FUZZ=1" \
  -fc 404 -c -t 20
```

**Nota:** El fuzzing de parámetros POST no es viable automáticamente porque Laravel requiere token CSRF (`_token`) que cambia en cada request.

---

## 6. Exploración de endpoints adicionales

### /forgot-password (recuperación de contraseña)

```bash
curl -s http://37.60.230.11/forgot-password | grep -i "input\|form"
```

Formulario con campo `email` y token CSRF. Potencial para enumeración de usuarios si responde distinto entre emails existentes vs no existentes.

### /up (health check)

```bash
curl -s http://37.60.230.11/up
```

Responde "Application up" con tiempo de respuesta en ms. Sin utilidad para ataque.

### /uploads/ (archivos subidos)

```bash
curl -s http://37.60.230.11/uploads/  # 403 Forbidden
```

Listado de directorio bloqueado, pero archivos específicos siguen siendo accesibles si se conoce su nombre (ej: `/uploads/shell.php`).

---

## Resumen de fase de escaneo

| # | Vulnerabilidad Potencial | Severidad | Herramienta |
|---|--------------------------|-----------|-------------|
| 1 | PostgreSQL expuesto públicamente (5432) | Crítica | nmap |
| 2 | PHP desactualizado con RCE público | Crítica | whatweb / searchsploit |
| 3 | Cookie XSRF-TOKEN sin HttpOnly | Alta | nikto |
| 4 | IP interna Docker expuesta | Media | nikto |
| 5 | Headers de seguridad ausentes | Media | nikto |
| 6 | Formulario registro abierto (/register) | Alta | gobuster |
| 7 | Endpoint subida archivos (/upload) | Crítica | gobuster |
| 8 | Fuzzing rutas admin confirmadas | Baja | ffuf |
| 9 | No rate limiting visible en login | Media | curl (prueba manual) |
| 10 | Debug Mode activado (APP_DEBUG=true) | Media | curl (prueba manual) |
