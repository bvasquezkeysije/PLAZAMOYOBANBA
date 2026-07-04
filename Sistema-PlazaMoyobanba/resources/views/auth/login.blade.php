<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Sistema PlazaMoyobanba') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <style>[x-cloak] { display: none !important; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">

    @if ($errors->has('login') || $errors->has('password'))
    <div x-data="{ showErrorModal: true }" x-show="showErrorModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-slate-900/35 backdrop-blur-[1px]"></div>
        <div class="relative w-full max-w-sm rounded-2xl bg-white shadow-xl border border-slate-100 p-4 sm:p-5">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-full bg-red-50 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <div class="flex-1">
                    <h2 class="text-base font-semibold text-slate-900">No se pudo iniciar sesión</h2>
                    <p class="text-sm text-slate-600 mt-0.5">Verifica tu correo o usuario y tu contraseña.</p>
                </div>
                <button type="button" @click="showErrorModal = false" class="text-slate-400 hover:text-slate-700 text-lg leading-none">&times;</button>
            </div>
            <div class="mt-4 flex justify-end">
                <button type="button" @click="showErrorModal = false" class="px-3.5 py-1.5 rounded-lg bg-slate-900 text-white text-xs font-semibold tracking-wide hover:bg-slate-800">
                    Entendido
                </button>
            </div>
        </div>
    </div>
    @endif

    <div class="flex min-h-screen">
        <div class="w-full max-w-md bg-white flex flex-col">
            <div class="flex-1 flex flex-col justify-center px-10 py-8">
                <div class="text-center mb-8">
                    <img src="{{ asset('images/logo-plazamoyobanba.png') }}" alt="PlazaMoyobanba" class="mx-auto" style="max-width: 80%; height: auto;">
                </div>

                <h2 class="text-2xl font-bold text-slate-800 mb-1">Iniciar Sesión</h2>
                <p class="text-sm text-slate-500 mb-6">Ingrese su usuario y contraseña para acceder.</p>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="login" class="block text-sm font-semibold text-slate-700 mb-1.5">Correo o usuario</label>
                        <input id="login" type="text" name="login" :value="old('login')" required autofocus autocomplete="username"
                            class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-700 focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none transition"
                            placeholder="Ingresa tu usuario">
                        @error('login') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Contraseña</label>
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                            class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-700 focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none transition"
                            placeholder="Ingresa tu contraseña">
                        @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-5">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="remember" class="rounded border-slate-300 text-amber-500 focus:ring-amber-400">
                            <span class="ml-2 text-sm text-slate-600">Mantener sesión activa</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-lg text-sm transition shadow-md shadow-amber-500/30">
                        Ingresar
                    </button>

                    @if (Route::has('password.request'))
                    <div class="text-center mt-4">
                        <a href="{{ route('password.request') }}" class="text-sm text-slate-500 hover:text-slate-700 underline">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                    @endif
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