<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Sistema PlazaMoyobanba') }}</title>
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <div x-data="{ sidebarOpen: true, userMenuOpen: false }" class="min-h-screen flex">
            <aside
                class="bg-slate-900 text-white transition-all duration-200 flex flex-col min-h-screen"
                :class="sidebarOpen ? 'w-64' : 'w-20'"
            >
                <div class="pt-4 px-3 flex" :class="sidebarOpen ? 'justify-end' : 'justify-center'">
                    <button
                        @click="sidebarOpen = !sidebarOpen"
                        class="w-10 h-10 rounded-lg bg-transparent border border-transparent text-white/90 hover:bg-slate-700/70 hover:border-slate-600 flex items-center justify-center transition-colors"
                        :title="sidebarOpen ? 'Cerrar menú' : 'Abrir menú'"
                    >
                        <span x-show="sidebarOpen" class="text-xl leading-none">✕</span>
                        <span x-show="!sidebarOpen" class="text-xl leading-none">☰</span>
                    </button>
                </div>

                <div class="pt-4 pb-5 px-2 flex items-center justify-center border-b border-slate-700 mb-4">
                    <img
                        src="{{ asset('images/logo-plazamoyobanba-sidebar.png') }}"
                        alt="PlazaMoyobanba Sidebar"
                        class="object-contain transition-all duration-200"
                        :class="sidebarOpen ? 'w-[160px] h-[160px]' : 'w-12 h-12'"
                    >
                </div>

                <nav class="p-3 space-y-2">
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded {{ request()->routeIs('admin.dashboard') ? 'bg-amber-400 text-slate-900 font-semibold' : 'hover:bg-slate-700' }}">
                        <span class="inline-flex items-center gap-2" :class="sidebarOpen ? '' : 'justify-center w-full'">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 13h8V3H3v10zM13 21h8v-8h-8v8zM13 3v6h8V3h-8zM3 21h8v-6H3v6z"/>
                            </svg>
                            <span x-show="sidebarOpen">Dashboard</span>
                        </span>
                    </a>
                    <a href="{{ route('admin.ventas') }}" class="block px-3 py-2 rounded {{ request()->routeIs('admin.ventas') ? 'bg-amber-400 text-slate-900 font-semibold' : 'hover:bg-slate-700' }}">
                        <span class="inline-flex items-center gap-2" :class="sidebarOpen ? '' : 'justify-center w-full'">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 17l6-6 4 4 8-8"/>
                                <path d="M14 7h7v7"/>
                            </svg>
                            <span x-show="sidebarOpen">Ventas</span>
                        </span>
                    </a>
                    <a href="{{ route('admin.productos') }}" class="block px-3 py-2 rounded {{ request()->routeIs('admin.productos') ? 'bg-amber-400 text-slate-900 font-semibold' : 'hover:bg-slate-700' }}">
                        <span class="inline-flex items-center gap-2" :class="sidebarOpen ? '' : 'justify-center w-full'">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 7l9-4 9 4-9 4-9-4z"/>
                                <path d="M3 7v10l9 4 9-4V7"/>
                            </svg>
                            <span x-show="sidebarOpen">Productos</span>
                        </span>
                    </a>
                    <a href="{{ route('admin.habitaciones') }}" class="block px-3 py-2 rounded {{ request()->routeIs('admin.habitaciones') ? 'bg-amber-400 text-slate-900 font-semibold' : 'hover:bg-slate-700' }}">
                        <span class="inline-flex items-center gap-2" :class="sidebarOpen ? '' : 'justify-center w-full'">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 11h18v8H3z"/>
                                <path d="M7 11V7h4v4M13 11V6h4v5"/>
                            </svg>
                            <span x-show="sidebarOpen">Habitaciones</span>
                        </span>
                    </a>
                    <a href="{{ route('admin.huespedes') }}" class="block px-3 py-2 rounded {{ request()->routeIs('admin.huespedes*') ? 'bg-amber-400 text-slate-900 font-semibold' : 'hover:bg-slate-700' }}">
                        <span class="inline-flex items-center gap-2" :class="sidebarOpen ? '' : 'justify-center w-full'">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 6.253v13"/>
                                <path d="M12 6.253C10.832 5.477 9.246 5 7.5 5 4.462 5 2 6.343 2 8v11c0-1.657 2.462-3 5.5-3 1.746 0 3.332.477 4.5 1.253"/>
                                <path d="M12 6.253C13.168 5.477 14.754 5 16.5 5c3.038 0 5.5 1.343 5.5 3v11c0-1.657-2.462-3-5.5-3-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            <span x-show="sidebarOpen">Libro de huéspedes</span>
                        </span>
                    </a>
                    <a href="{{ route('admin.clientes') }}" class="block px-3 py-2 rounded {{ request()->routeIs('admin.clientes') ? 'bg-amber-400 text-slate-900 font-semibold' : 'hover:bg-slate-700' }}">
                        <span class="inline-flex items-center gap-2" :class="sidebarOpen ? '' : 'justify-center w-full'">
                            <img src="{{ asset('images/clienetes.svg') }}" alt="Clientes" class="w-5 h-5 object-contain">
                            <span x-show="sidebarOpen">Clientes</span>
                        </span>
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="block px-3 py-2 rounded {{ request()->routeIs('admin.users.*') ? 'bg-amber-400 text-slate-900 font-semibold' : 'hover:bg-slate-700' }}">
                        <span class="inline-flex items-center gap-2" :class="sidebarOpen ? '' : 'justify-center w-full'">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="8" r="4"/>
                                <path d="M4 20c0-4.4 3.6-8 8-8s8 3.6 8 8"/>
                            </svg>
                            <span x-show="sidebarOpen">Usuarios</span>
                        </span>
                    </a>
                    <a href="{{ route('admin.reportes') }}" class="block px-3 py-2 rounded {{ request()->routeIs('admin.reportes*') ? 'bg-amber-400 text-slate-900 font-semibold' : 'hover:bg-slate-700' }}">
                        <span class="inline-flex items-center gap-2" :class="sidebarOpen ? '' : 'justify-center w-full'">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 3v18h18"/>
                                <path d="M7 14l3-3 3 2 4-5"/>
                            </svg>
                            <span x-show="sidebarOpen">Reportes</span>
                        </span>
                    </a>
                </nav>

                <div class="mt-auto p-3 border-t border-slate-700/70">
                    <a href="{{ route('admin.configuracion') }}" class="block px-3 py-2 rounded {{ request()->routeIs('admin.configuracion') ? 'bg-amber-400 text-slate-900 font-semibold' : 'hover:bg-slate-700' }}">
                        <span class="inline-flex items-center gap-2" :class="sidebarOpen ? '' : 'justify-center w-full'">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="3"/>
                                <path d="M19.4 15a1.7 1.7 0 0 0 .34 1.87l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .27 1.7 1.7 0 0 0-.8 1.46V21a2 2 0 0 1-4 0v-.09a1.7 1.7 0 0 0-.8-1.46 1.7 1.7 0 0 0-1-.27 1.7 1.7 0 0 0-1.87.34l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.27-1 1.7 1.7 0 0 0-1.46-.8H2.8a2 2 0 0 1 0-4h.09a1.7 1.7 0 0 0 1.46-.8 1.7 1.7 0 0 0 .27-1 1.7 1.7 0 0 0-.34-1.87l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.27 1.7 1.7 0 0 0 .8-1.46V2.8a2 2 0 0 1 4 0v.09a1.7 1.7 0 0 0 .8 1.46 1.7 1.7 0 0 0 1 .27 1.7 1.7 0 0 0 1.87-.34l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.7 1.7 0 0 0 19.4 9a1.7 1.7 0 0 0 .27 1 1.7 1.7 0 0 0 1.46.8h.09a2 2 0 0 1 0 4h-.09a1.7 1.7 0 0 0-1.46.8 1.7 1.7 0 0 0-.27 1z"/>
                            </svg>
                            <span x-show="sidebarOpen">Configuración</span>
                        </span>
                    </a>
                </div>
            </aside>

            <div class="flex-1 flex flex-col">
                <header class="h-16 bg-white border-b px-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <h1 class="text-lg font-semibold text-gray-800">{{ $title ?? 'Panel de Administracion' }}</h1>
                    </div>
                    <div class="relative">
                        <button
                            @click="userMenuOpen = !userMenuOpen"
                            class="px-4 py-2 rounded-xl border border-gray-300 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50"
                        >
                            {{ strtoupper(auth()->user()->name) }}
                        </button>

                        <div
                            x-show="userMenuOpen"
                            @click.outside="userMenuOpen = false"
                            x-transition
                            class="absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded-2xl shadow-xl p-2 z-20"
                            style="display: none;"
                        >
                            <a href="#" class="block px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-100">Mi perfil</a>
                            <a href="#" class="block px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-100">Configuración</a>
                            <a href="#" class="block px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-100">Ayuda</a>
                            <a href="#" class="block px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-100">Soporte</a>
                            <hr class="my-2 border-gray-200">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-3 py-2 rounded-lg text-sm text-red-600 hover:bg-red-50">
                                    Cerrar sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </header>

                <main class="p-6 h-[calc(100vh-4rem)] overflow-y-auto">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
