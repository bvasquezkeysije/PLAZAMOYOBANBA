# 05 - Modificaciones al Código

## Rama con vulnerabilidades
- **Repositorio:** https://github.com/bvasquezkeysije/Sistema-PlazaMoyobanba
- **Rama:** `sistema-con-vulnerabilidades`

### Para desplegar en el servidor
```bash
cd ~/Sistema-PlazaMoyobanba
git fetch origin
git checkout sistema-con-vulnerabilidades
docker compose down
docker compose up -d --build
```

---

## 1. SQL Injection - LoginRequest.php

**Ruta:** `app/Http/Requests/Auth/LoginRequest.php`

**Responsable:** Keysi

**Cambio:**
```php
use App\Models\User;
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

**Responsable:** Brigit

**Cambio:**
```php
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

**Responsable:** Alexander

**Cambio (al final del archivo):**
```php
// 🔥 VULNERABILIDAD: Subida de archivos sin autenticación
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
