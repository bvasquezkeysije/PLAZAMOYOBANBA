# 01 - Reconocimiento

## Target
- **URL:** http://192.168.18.38:8001
- **Servidor:** PHP 8.2.12 (Alpine Linux en Docker)
- **Framework:** Laravel 12
- **BD:** PostgreSQL 16
- **Sistema:** Hotel PlazaMoyobanba (Sistema de gestión hotelera)
- **Infra:** Docker Compose (php:8.2-fpm-alpine + nginx:1.27-alpine + postgres:16-alpine)

## Puertos
```
8001/tcp  open  http  nginx 1.27
5432/tcp  open  postgresql 16 (solo interno Docker)
```

## Rutas encontradas
| Ruta | Estado | Descripción |
|------|--------|-------------|
| `/login` | 200 | Formulario de login |
| `/register` | 200 | Registro de usuarios (abierto) |
| `/dashboard` | 302 | Redirección al dashboard |
| `/admin/dashboard` | 403 | Panel admin (solo rol admin) |
| `/admin/usuarios` | 403 | Gestión de usuarios |
| `/admin/ventas` | 403 | Gestión de ventas |
| `/profile` | 200 | Perfil de usuario (requiere auth) |
| `/forgot-password` | 200 | Recuperación de contraseña |
| `/robots.txt` | 200 | Allow all |

## Headers de Seguridad (ausentes)
```
X-Frame-Options: no configurado
X-Content-Type-Options: no configurado
Content-Security-Policy: no configurado
X-XSS-Protection: no configurado
```

## Tecnologías detectadas
- **Frontend:** Laravel Blade + TailwindCSS + Alpine.js + Vite
- **Backend:** PHP 8.2 con Eloquent ORM
- **Auth:** Laravel Breeze (autenticación por sesión)
- **CSRF:** Protección activa (tokens en formularios)
- **Roles:** Spatie Laravel Permission (admin, gerente, recepcionista, contador, limpieza)

## Assets verificados (todos OK)
```
/build/assets/app-BK7y2LJC.css     → 200 (59KB)
/build/assets/app-DsIK1Lmc.js      → 200 (88KB)
/images/logo-plazamoyobanba.png    → 200 (141KB)
/images/logo-plazamoyobanba-sidebar.png → 200 (141KB)
/images/fondo-login.png            → 200 (963KB)
/favicon.ico                       → 200 (1.3KB)
```

## Credenciales por defecto (seeders)
```
admin@gmail.com / 123
admin / 123
adminrapido / 123
```

## Usuarios admin del sistema (del seed)
```
bvasquezkeysije@uss.edu.pe / 76636255
dgarciabriggitl@uss.edu.pe / 76465678
vquispejorgetom@uss.edu.pe / 72838203
cleonalexandgra@uss.edu.pe / 73149801
```

## Observaciones
- Registro de usuarios completamente abierto (sin invitación)
- Consultas SQL con Eloquent (protegidas contra SQLi en código original)
- Sin rate limiting visible en login
- Sin headers de seguridad
- Vite dev server NO accesible externamente
