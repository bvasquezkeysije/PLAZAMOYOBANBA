# Fase 1 - Reconocimiento (Reconnaissance)

**Target:** http://37.60.230.11
**Fecha:** Julio 2026

---

## Información obtenida

### Headers HTTP
```
$ curl -I http://37.60.230.11/
HTTP/1.1 302 Found
Server: nginx/1.27.5
Content-Type: text/html; charset=utf-8
X-Powered-By: PHP/8.2.32
Location: /login
Set-Cookie: XSRF-TOKEN=...
Set-Cookie: sistema-plazamoyobanba-session=...
```

### Puertos abiertos (nmap)
```
$ nmap -sV -p 22,80,443,5432 37.60.230.11 --min-rate=5000
PORT     STATE    SERVICE     VERSION
22/tcp   open     ssh         OpenSSH
80/tcp   open     http        nginx 1.27.5
443/tcp  filtered https
5432/tcp filtered postgresql
```

### Endpoints descubiertos (dirb)
```
$ dirb http://37.60.230.11/
+ http://37.60.230.11/admin (CODE:302)
+ http://37.60.230.11/api (CODE:200)
+ http://37.60.230.11/login (CODE:200)
+ http://37.60.230.11/logout (CODE:302)
+ http://37.60.230.11/profile (CODE:302)
+ http://37.60.230.11/register (CODE:200)
+ http://37.60.230.11/uploads (CODE:403)
+ http://37.60.230.11/storage (CODE:403)
```

### Tecnologías identificadas
- Servidor web: **Nginx 1.27.5**
- Backend: **PHP 8.2.32** con **Laravel 12.58.0**
- Base de datos: **PostgreSQL 16.14**
- Sesiones manejadas con cookies Laravel (XSRF-TOKEN, sistema-plazamoyobanba-session)
- Sin HTTPS (puerto 443 filtrado)
- Sin WAF visible, sin rate limiting aparente
- URL amigables con estructura REST (Laravel)
