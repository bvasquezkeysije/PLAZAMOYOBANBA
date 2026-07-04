# 01 - Reconocimiento

## Target
- **URL:** http://192.168.18.31:8001
- **Servidor:** PHP 8.2.12
- **Framework:** Laravel
- **BD:** PostgreSQL
- **Sistema:** Hotel PlazaMoyobanba

## Puertos
```
8001/tcp open http PHP 8.2.12
```

## Rutas encontradas
| Ruta | Estado | Descripción |
|------|--------|-------------|
| `/login` | 200 | Login |
| `/register` | 200 | Registro abierto |
| `/dashboard` | 302 | Redirect a admin |
| `/admin/dashboard` | 403 | Solo admin |
| `/admin/usuarios` | 403 | Solo admin |
| `/admin/ventas` | 403 | Solo admin |
| `/profile` | 200 | Perfil (auth) |
| `/forgot-password` | 200 | Recuperar pass |
| `/robots.txt` | 200 | Allow all |

## Credenciales por defecto (seeders)
```
admin@gmail.com / 123
admin / 123
adminrapido / 123
```

## Usuarios admin del sistema
```
bvasquezkeysije@uss.edu.pe / 76636255
dgarciabriggitl@uss.edu.pe / 76465678
vquispejorgetom@uss.edu.pe / 72838203
cleonalexandgra@uss.edu.pe / 73149801
```
