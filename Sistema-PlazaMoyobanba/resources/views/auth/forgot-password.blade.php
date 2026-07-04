<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Sistema PlazaMoyobanba') }} - Recuperar contraseña</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <style>[x-cloak] { display: none !important; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">

    <div class="flex min-h-screen">
        <div class="w-full max-w-md bg-white flex flex-col">
            <div class="flex-1 flex flex-col justify-center px-10 py-8">
                <div class="text-center mb-8">
                    <a href="{{ route('login') }}">
                        <img src="{{ asset('images/logo-plazamoyobanba.png') }}" alt="PlazaMoyobanba" class="mx-auto" style="max-width: 80%; height: auto;">
                    </a>
                </div>

                <h2 class="text-2xl font-bold text-slate-800 mb-1">Recuperar contraseña</h2>
                <p class="text-sm text-slate-500 mb-6">¿Olvidaste tu contraseña? No hay problema. Indícanos tu correo y te enviaremos un enlace para restablecerla.</p>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Correo electrónico</label>
                        <input id="email" type="email" name="email" :value="old('email')" required autofocus
                            class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-700 focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none transition"
                            placeholder="Ingresa tu correo">
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-lg text-sm transition shadow-md shadow-amber-500/30">
                        Enviar enlace de recuperación
                    </button>

                    <div class="text-center mt-4">
                        <a href="{{ route('login') }}" class="text-sm text-slate-500 hover:text-slate-700 underline">
                            Volver al inicio de sesión
                        </a>
                    </div>
                </form>
            </div>

            <div class="px-10 py-4 border-t border-slate-200 text-center">
                <p class="text-xs text-slate-400">Implementado por <a href="https://www.inticap.com/portal/" target="_blank" class="text-amber-600 hover:text-amber-700 font-medium">Inticap</a></p>
            </div>
        </div>

        <div class="hidden lg:flex flex-1 relative items-center justify-center bg-cover bg-center" style="background-image: url('{{ asset('images/fondo-login.png') }}');">
            <div class="absolute inset-0 bg-black/40"></div>
            <div class="relative text-center px-8">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-3">PLAZA MOYOBANBA</h2>
                <p class="text-white/80 text-lg max-w-lg mx-auto">JR. SERAFIN FILOMENO NRO. 613 SAN MARTIN - MOYOBANBA - MOYOBANBA</p>
            </div>
        </div>
    </div>

</body>
</html>