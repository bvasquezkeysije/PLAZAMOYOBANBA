<x-layouts.admin>
    <x-slot name="title">Libro de huéspedes</x-slot>

    <div x-data="{ showFilters:false, showCreate:false }" class="space-y-4">
        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <form method="GET" action="{{ route('admin.huespedes') }}" class="space-y-4">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                    <input type="text" name="q" value="{{ request('q') }}" class="w-full lg:flex-1 rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Buscar por código, huésped, documento o habitación">
                    <button type="button" @click="showFilters=!showFilters" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50" aria-label="Filtros" title="Filtros">
                        <img src="{{ asset('images/flitro.svg') }}" alt="Filtros" class="w-4 h-4">
                    </button>
                    <button type="button" @click="showCreate=true" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-blue-200 bg-blue-50 text-blue-800 text-sm font-semibold hover:bg-blue-100" aria-label="Registrar huésped" title="Registrar huésped">
                        <img src="{{ asset('images/anadir-huesped.svg') }}" alt="Registrar huésped" class="w-4 h-4">
                    </button>
                    <a href="{{ route('admin.huespedes.print', request()->query()) }}" target="_blank" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 text-sm font-semibold hover:bg-emerald-100 text-center" aria-label="Imprimir registro" title="Imprimir registro">
                        <img src="{{ asset('images/imprimir.svg') }}" alt="Imprimir registro" class="w-4 h-4">
                    </a>
                    <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-blue-900 text-white text-sm font-semibold hover:bg-blue-800" aria-label="Buscar" title="Buscar">
                        <img src="{{ asset('images/buscar.svg') }}" alt="Buscar" class="w-4 h-4 brightness-0 invert">
                    </button>
                </div>

                <div x-show="showFilters" x-transition class="grid grid-cols-1 md:grid-cols-3 gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4" style="display:none;">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Estado</label>
                        <select name="status" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Todos</option>
                            <option value="hospedado" {{ request('status')==='hospedado' ? 'selected' : '' }}>Hospedado</option>
                            <option value="salio" {{ request('status')==='salio' ? 'selected' : '' }}>Salió</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Desde</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Hasta</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left">
                        <tr>
                            <th class="px-4 py-3">Código</th>
                            <th class="px-4 py-3">Huésped</th>
                            <th class="px-4 py-3">Documento</th>
                            <th class="px-4 py-3">Nacionalidad</th>
                            <th class="px-4 py-3">Habitación</th>
                            <th class="px-4 py-3">Ingreso</th>
                            <th class="px-4 py-3">Salida</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($registers as $reg)
                        <tr class="border-t">
                            <td class="px-4 py-3 font-semibold">{{ $reg->code }}</td>
                            <td class="px-4 py-3">{{ $reg->full_name }}</td>
                            <td class="px-4 py-3">{{ $reg->document_type }} {{ $reg->document_number }}</td>
                            <td class="px-4 py-3">{{ $reg->nationality }}</td>
                            <td class="px-4 py-3">Hab. {{ $reg->room->room_number ?? '-' }}</td>
                            <td class="px-4 py-3">{{ \Carbon\Carbon::parse($reg->check_in_at)->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">{{ $reg->check_out_at ? \Carbon\Carbon::parse($reg->check_out_at)->format('d/m/Y H:i') : '-' }}</td>
                            <td class="px-4 py-3">{{ $reg->status === 'hospedado' ? 'Hospedado' : 'Salió' }}</td>
                            <td class="px-4 py-3">
                                @if ($reg->status === 'hospedado')
                                    <form method="POST" action="{{ route('admin.huespedes.checkout', $reg) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700">Registrar salida</button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-500">Completado</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr class="border-t"><td class="px-4 py-3" colspan="9">Sin registros de huéspedes por ahora.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>{{ $registers->links() }}</div>

        <div x-cloak x-show="showCreate" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
            <div class="absolute inset-0 bg-slate-900/55" @click="showCreate=false"></div>
            <div class="relative w-full max-w-2xl rounded-2xl bg-white border border-slate-200 shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-900">Nuevo registro de huésped</h3>
                    <button type="button" @click="showCreate=false" class="w-9 h-9 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-700">&times;</button>
                </div>
                <form method="POST" action="{{ route('admin.huespedes.store') }}" class="p-6 space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nombre completo</label>
                            <input type="text" name="full_name" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Habitación</label>
                            <select name="room_id" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                                <option value="">Seleccionar habitación</option>
                                @foreach($rooms as $room)
                                    <option value="{{ $room->id }}">Hab. {{ $room->room_number }} ({{ $room->type }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tipo documento</label>
                            <select name="document_type" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                                <option value="DNI">DNI</option>
                                <option value="CE">CE</option>
                                <option value="PASAPORTE">Pasaporte</option>
                                <option value="OTRO">Otro</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nro documento</label>
                            <input type="text" name="document_number" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nacionalidad</label>
                            <input type="text" name="nationality" value="PERUANA" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Ingreso</label>
                            <input type="datetime-local" name="check_in_at" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Salida (opcional)</label>
                            <input type="datetime-local" name="check_out_at" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Observación (opcional)</label>
                        <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-900 text-white text-sm font-semibold hover:bg-blue-800">Guardar registro</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
