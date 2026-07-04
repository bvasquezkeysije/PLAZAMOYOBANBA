# 05 - Modificaciones al Código

## Rama con vulnerabilidades
- **Repositorio:** https://github.com/bvasquezkeysije/Sistema-PlazaMoyobanba
- **Rama:** `sistema-con-vulnerabilidades`
- **Commit inicial seguro:** `main`
- **Commits vulnerables:** En rama `sistema-con-vulnerabilidades`

## Para desplegar en servidor
```bash
cd ~/Sistema-PlazaMoyobanba
git fetch origin
git checkout sistema-con-vulnerabilidades
docker compose down && docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate --force
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force
docker compose exec app npm install && npm run build
```

---

## 1. SQL Injection - LoginRequest.php

**Ruta:** `app/Http/Requests/Auth/LoginRequest.php`
**Responsable:** Tomi

**Cambio:** Se reemplazó `Auth::attempt()` por `DB::select()` con concatenación directa.

```php
// CÓDIGO ORIGINAL (seguro):
public function authenticate(): void
{
    $this->ensureIsNotRateLimited();
    $this->validates('login' => ['required', 'string']);
    
    $field = filter_var($this->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
    
    if (! Auth::attempt([$field => $this->login, 'password' => $this->password])) {
        RateLimiter::hit($this->throttleKey());
        throw ValidationException::withMessages(['login' => trans('auth.failed')]);
    }
    
    RateLimiter::clear($this->throttleKey());
}

// CÓDIGO MODIFICADO (vulnerable):
use Illuminate\Support\Facades\DB;

public function authenticate(): void
{
    $this->ensureIsNotRateLimited();

    $login = $this->string('login')->toString();
    $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

    // 🔥 VULNERABILIDAD: SQL Injection directa
    $sql = "SELECT * FROM users WHERE $field = '$login' AND is_active = true LIMIT 1";
    $users = DB::select($sql);

    if (!empty($users)) {
        $user = User::find($users[0]->id);
        if ($user) {
            Auth::login($user);
            RateLimiter::clear($this->throttleKey());
            return;
        }
    }

    RateLimiter::hit($this->throttleKey());
    throw ValidationException::withMessages([
        'login' => trans('auth.failed'),
    ]);
}
```

## 2. Mass Assignment - ProfileController.php

**Ruta:** `app/Http/Controllers/ProfileController.php`
**Responsable:** Bri

**Cambio:** Se agregó asignación de roles de Spatie desde el request.

```php
// CÓDIGO ORIGINAL (seguro):
public function update(ProfileUpdateRequest $request): RedirectResponse
{
    $user = $request->user();
    $user->fill($request->validated());
    
    if ($user->isDirty('email')) {
        $user->email_verified_at = null;
    }
    $user->save();
    
    return Redirect::route('profile.edit')->with('status', 'profile-updated');
}

// CÓDIGO MODIFICADO (vulnerable):
public function update(ProfileUpdateRequest $request): RedirectResponse
{
    $user = $request->user();
    $user->fill($request->validated());

    // 🔥 VULNERABILIDAD: Asignación de roles desde el request
    if ($request->has('role_name')) {
        $user->syncRoles([$request->role_name]);
    }

    if ($user->isDirty('email')) {
        $user->email_verified_at = null;
    }
    $user->save();

    return Redirect::route('profile.edit')->with('status', 'profile-updated');
}
```

## 3. File Upload - web.php

**Ruta:** `routes/web.php`
**Responsable:** Alex

**Cambio:** Se agregó ruta POST `/upload` sin middleware de autenticación.

```php
// CÓDIGO ORIGINAL (seguro): No existe ruta /upload

// CÓDIGO MODIFICADO (vulnerable) - al final de web.php:
use Illuminate\Http\Request;

// 🔥 VULNERABILIDAD: Subida de archivos sin autenticación
Route::post('/upload', function (Request $request) {
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

## 4. Fix nginx - default.conf

**Ruta:** `docker/nginx/default.conf`
**Motivo:** La configuración original usaba `SCRIPT_FILENAME` fijo a `index.php`, impidiendo ejecutar otros archivos PHP (como la webshell subida).

```nginx
// ANTES (roto): todas las .php van a index.php
fastcgi_param SCRIPT_FILENAME /var/www/html/public/index.php;

// DESPUÉS (funcional):
fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
```

---

## Verificación de cambios
```bash
# Verificar SQLi
grep "DB::select" app/Http/Requests/Auth/LoginRequest.php

# Verificar Mass Assignment
grep "syncRoles" app/Http/Controllers/ProfileController.php

# Verificar File Upload
grep "Route::post.*/upload" routes/web.php
```
