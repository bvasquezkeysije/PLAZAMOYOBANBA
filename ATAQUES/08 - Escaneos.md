# 08 - Escaneos Realizados

**Target:** http://37.60.230.11
**Fecha:** 2026-07-12

---

## Índice
1. [NMAP - Escaneo de puertos y servicios](#1-nmap---escaneo-de-puertos-y-servicios)
2. [WhatWeb - Detección de tecnologías](#2-whatweb---detección-de-tecnologías)
3. [Gobuster - Enumeración de rutas](#3-gobuster---enumeración-de-rutas)
4. [Nikto - Escaneo de vulnerabilidades web](#4-nikto---escaneo-de-vulnerabilidades-web)
5. [SearchSploit - Búsqueda de exploits](#5-searchsploit---búsqueda-de-exploits)
6. [NMAP http-methods - Métodos HTTP](#6-nmap-http-methods---métodos-http)
7. [FFUF - Fuzzing de parámetros ocultos](#7-ffuf---fuzzing-de-parámetros-ocultos)

---

## 1. NMAP - Escaneo de puertos y servicios

### Instalación en Arch
```bash
sudo pacman -S nmap
```

### Escaneo
```bash
nmap -sV 37.60.230.11
```

### Resultados
```
PORT      STATE    SERVICE    VERSION
22/tcp    open     ssh        OpenSSH 9.6p1 Ubuntu
23/tcp    filtered telnet
25/tcp    filtered smtp
80/tcp    open     http       nginx 1.27.5
2323/tcp  filtered 3d-nfsd
5432/tcp  open     postgresql PostgreSQL DB
5555/tcp  filtered freeciv
22939/tcp filtered unknown
```

### Análisis
- **Puerto 80 (HTTP):** nginx 1.27.5 — servidor web público, punto de entrada principal.
- **Puerto 5432 (PostgreSQL):** Base de datos expuesta directamente a internet. Crítico si se obtienen credenciales vía SQLi.
- **Puerto 22 (SSH):** OpenSSH 9.6p1 Ubuntu — acceso remoto al servidor.
- **Puertos filtrados (23,25,2323,5555,22939):** Probable firewall (iptables/ufw) bloqueando tráfico.

---

## 2. WhatWeb - Detección de tecnologías

### Instalación en Arch
```bash
sudo pacman -S whatweb
```

### Escaneo
```bash
whatweb http://37.60.230.11
```

### Resultados
```
http://37.60.230.11 [302 Found]
  Cookies[XSRF-TOKEN, sistema-plazamoyobanba-session],
  Country[ROMANIA][RO],
  HTML5,
  HTTPServer[nginx/1.27.5],
  IP[37.60.230.11],
  Meta-Refresh-Redirect[/login],
  PHP[8.2.32],
  RedirectLocation[/login],
  Title[Redirecting to /login],
  X-Powered-By[PHP/8.2.32],
  nginx[1.27.5]

http://37.60.230.11/login [200 OK]
  Cookies[XSRF-TOKEN, sistema-plazamoyobanba-session],
  PasswordField[password],
  Script[module],
  Title[Sistema PlazaMoyobanba],
  X-Powered-By[PHP/8.2.32]
```

### Análisis
- **PHP 8.2.32** con **nginx 1.27.5**.
- **Framework Laravel** (cookies `XSRF-TOKEN` y `sistema-plazamoyobanba-session`).
- **Formulario de login** con campo `password` detectado automáticamente.

---

## 3. Gobuster - Enumeración de rutas

### Instalación en Arch
```bash
sudo pacman -S gobuster
```

### Escaneo
```bash
gobuster dir -u http://37.60.230.11 -w /usr/share/wordlists/common.txt -t 20
```

### Wordlist
```bash
# Descargar wordlist de SecLists
curl -sL "https://raw.githubusercontent.com/danielmiessler/SecLists/master/Discovery/Web-Content/common.txt" -o /usr/share/wordlists/common.txt
```

### Resultados
```
.htaccess            (Status: 403) [Size: 153]
.htpasswd            (Status: 403) [Size: 153]
.hta                 (Status: 403) [Size: 153]
build                (Status: 301) [--> http://37.60.230.11/build/]
dashboard            (Status: 302) [--> /admin/dashboard]
favicon.ico          (Status: 200) [Size: 1346]
forgot-password      (Status: 200) [Size: 3892]
images               (Status: 301) [--> http://37.60.230.11/images/]
index.php            (Status: 302) [--> /login]
login                (Status: 200) [Size: 4754]
logout               (Status: 405) [Size: 844346]
profile              (Status: 302) [--> http://37.60.230.11/login]
register             (Status: 200) [Size: 4137]
password             (Status: 405) [Size: 844356]
robots.txt           (Status: 200) [Size: 24]
up                   (Status: 200) [Size: 1843]
uploads              (Status: 301) [--> http://37.60.230.11/uploads/]
upload               (Status: 405) [Size: 844346]
```

### Análisis
| Ruta | Método | Estado | Descripción |
|------|--------|--------|-------------|
| `/login` | GET | 200 | Formulario de login |
| `/register` | GET | 200 | Registro abierto — Mass Assignment |
| `/upload` | POST | 405 | Subida de archivos — RCE |
| `/uploads/` | GET | 301 | Directorio de archivos subidos (403 listado) |
| `/forgot-password` | GET | 200 | Recuperación de contraseña |
| `/profile` | GET | 302 → /login | Perfil de usuario (requiere auth) |
| `/dashboard` | GET | 302 → /admin/dashboard | Dashboard admin (requiere auth) |
| `/build/` | GET | 301 | Assets (403 listado) |
| `/images/` | GET | 301 | Imágenes (403 listado) |
| `/up` | GET | 200 | Health check ("Application up") |
| `/password` | POST | 405 | Cambio de contraseña |
| `/logout` | POST | 405 | Cerrar sesión |
| `/robots.txt` | GET | 200 | `User-agent: *\nDisallow:` |

---

## 4. Nikto - Escaneo de vulnerabilidades web

### Instalación en Arch
```bash
sudo pacman -S nikto perl-xml-writer
```

### Escaneo
```bash
nikto -h http://37.60.230.11
```

### Resultados
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

### Análisis
- **Cookie XSRF-TOKEN sin HttpOnly** → vulnerable a robo mediante XSS.
- **PHP 8.2.32 desactualizado** → posible RCE (exploit público).
- **IP interna expuesta**: `172.18.0.4` (IP del contenedor Docker) en `/images`.
- **Headers de seguridad ausentes**: HSTS, CSP, X-Content-Type-Options.

---

## 5. SearchSploit - Búsqueda de exploits

### Instalación en Arch
```bash
sudo pacman -S exploitdb
```

### Escaneo
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

### Análisis
- PHP < 8.3.8 tiene un **RCE público** (`52047.py`) — posible explotación remota.
- PostgreSQL con múltiples vectores de **RCE** si se obtienen credenciales.
- PostgreSQL expuesto en puerto 5432 hace estos exploits especialmente peligrosos.

---

## 6. NMAP http-methods - Métodos HTTP

### Escaneo
```bash
nmap --script http-methods 37.60.230.11
```

### Resultados
```
PORT   STATE SERVICE
80/tcp open  http
| http-methods:
|_  Supported Methods: GET HEAD POST
```

### Análisis
Solo métodos GET, HEAD y POST — no hay PUT/DELETE expuestos. Configuración estándar de Laravel.

---

## 7. FFUF - Fuzzing de parámetros ocultos

### Instalación en Arch
```bash
go install github.com/ffuf/ffuf/v2@latest
export PATH=$PATH:~/go/bin
```

### Escaneo de parámetros GET en login
```bash
ffuf -w /usr/share/wordlists/common.txt \
  -u "http://37.60.230.11/login?FUZZ=1" \
  -fc 404 \
  -c -t 20
```

### Escaneo de rutas bajo /admin/
```bash
ffuf -w /usr/share/wordlists/common.txt \
  -u "http://37.60.230.11/admin/FUZZ" \
  -fc 404 \
  -c -t 20
```

### Nota sobre CSRF
El endpoint `/login` requiere el token `_token` de Laravel para peticiones POST. Esto limita el fuzzing automatizado de parámetros POST porque el token cambia en cada request. El fuzzing GET no tiene esta limitación.
