# Security Testing - Sistema PlazaMoyobanba

## Información General
- **Servidor:** http://192.168.18.38:8001
- **Framework:** Laravel 12
- **PHP:** 8.2
- **Base de Datos:** PostgreSQL 16
- **Infraestructura:** Docker Compose (App + Nginx + PostgreSQL)

## Equipo y Ataques

| Miembro | Ataque | Tipo | OWASP Top 10 |
|---------|--------|------|-------------|
| **Tomi** | SQL Injection + DDoS | Inyección / Denegación | A03:2021 + A01:2021 |
| **Bri** | Mass Assignment | Escalación de privilegios | A01:2021 |
| **Alex** | File Upload / RCE | Ejecución remota | A03:2021 |

## Documentación (por fases PTES)
1. [[Fase 1 - Reconocimiento]] - Recopilación de información
2. [[Fase 2 - Escaneo]] - Identificación de vulnerabilidades
3. [[01 - Reconocimiento]] - (original, previo)
4. [[02 - SQL Injection]]
5. [[03 - Mass Assignment]]
6. [[04 - File Upload RCE]]
7. [[05 - Modificaciones al Codigo]]
8. [[06 - Comandos Utiles]]
9. [[07 - DDoS Attack]]
10. [[08 - Escaneos]]

## Rama con vulnerabilidades
- **Rama:** `sistema-con-vulnerabilidades`
- **Repo:** https://github.com/bvasquezkeysije/Sistema-PlazaMoyobanba
- **Estado:** ✅ Desplegada en 192.168.18.38:8001

## Estado del Servicio
```
Login:   http://192.168.18.38:8001/login       → HTTP 200
Register: http://192.168.18.38:8001/register    → HTTP 200
Dashboard: http://192.168.18.38:8001/dashboard  → HTTP 302 (redirect)
Admin:   http://192.168.18.38:8001/admin/dashboard → HTTP 403 (sin rol)
```

## Despliegue rápido
```bash
cd ~/Sistema-PlazaMoyobanba
git fetch origin
git checkout sistema-con-vulnerabilidades
docker compose down
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate --force
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force
docker compose exec app npm install && npm run build
```

## Herramientas usadas
- curl, Python 3 (requests), sqlmap
- Docker, Docker Compose
- Nmap, Metasploit
- k6, httrack, Selenium
- Tor (torsocks)

## Contacto
- Git: https://github.com/bvasquezkeysije/Sistema-PlazaMoyobanba
