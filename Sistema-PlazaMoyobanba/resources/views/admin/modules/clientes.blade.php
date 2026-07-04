<x-layouts.admin>
    <x-slot name="title">Clientes</x-slot>

    <div
        x-data="{
            showFilters: false,
            showCreateClient: {{ $errors->any() ? 'true' : 'false' }},
            showEditClient: false,
            lookupDoc: '',
            formFullName: @js((string) old('full_name', '')),
            formDni: @js((string) old('dni', '')),
            formPhone: @js((string) old('phone', '')),
            formEmail: @js((string) old('email', '')),
            editClientId: null,
            editClientFullName: '',
            editClientDni: '',
            editClientPhone: '',
            editClientEmail: '',
            lookupMessage: '',
            lookupMessageType: 'info',
            loadingLookup: false,
            resetCreateClientForm() {
                this.lookupDoc = '';
                this.formFullName = '';
                this.formDni = '';
                this.formPhone = '';
                this.formEmail = '';
                this.lookupMessage = '';
                this.lookupMessageType = 'info';
                this.loadingLookup = false;
            },
            openCreateClient() {
                this.resetCreateClientForm();
                this.showCreateClient = true;
            },
            closeCreateClient() {
                this.showCreateClient = false;
                this.resetCreateClientForm();
            },
            openEditClient(client) {
                this.editClientId = client.id;
                this.editClientFullName = client.full_name || '';
                this.editClientDni = client.dni || '';
                this.editClientPhone = client.phone || '';
                this.editClientEmail = client.email || '';
                this.showEditClient = true;
            },
            closeEditClient() {
                this.showEditClient = false;
                this.editClientId = null;
                this.editClientFullName = '';
                this.editClientDni = '';
                this.editClientPhone = '';
                this.editClientEmail = '';
            },
            async buscarDocumento() {
                this.lookupMessage = '';
                const doc = (this.lookupDoc || '').replace(/\\D/g, '');
                if (!(doc.length === 8 || doc.length === 11)) {
                    this.lookupMessageType = 'error';
                    this.lookupMessage = 'Ingresa un DNI (8) o RUC (11) válido.';
                    return;
                }
                this.loadingLookup = true;
                try {
                    const response = await fetch('/admin/clientes/lookup?document=' + encodeURIComponent(doc), {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                        },
                    });
                    const payload = await response.json();
                    if (!response.ok) {
                        this.lookupMessageType = 'error';
                        this.lookupMessage = payload.message || 'No se pudo consultar el documento.';
                        return;
                    }
                    this.formDni = payload.dni || doc;
                    this.formFullName = payload.full_name || '';
                    this.formPhone = payload.phone || '';
                    this.formEmail = payload.email || '';
                    this.lookupMessageType = 'ok';
                    if (payload.source === 'local') this.lookupMessage = 'Cliente encontrado en la base de datos.';
                    else if (payload.source === 'reniec') this.lookupMessage = 'Datos cargados desde RENIEC.';
                    else if (payload.source === 'sunat') this.lookupMessage = 'Datos cargados desde SUNAT.';
                    else this.lookupMessage = 'Datos cargados correctamente.';
                } catch (e) {
                    this.lookupMessageType = 'error';
                    this.lookupMessage = 'Error de conexión al consultar documento.';
                } finally {
                    this.loadingLookup = false;
                }
            }
        }"
        class="space-y-4"
    >
        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <form method="GET" action="{{ route('admin.clientes') }}" class="space-y-4">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                    <input type="text" name="q" value="{{ request('q') }}" class="w-full lg:flex-1 rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Buscar cliente por nombre, DNI, correo o código">

                    <button type="button" @click="showFilters = !showFilters" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50" aria-label="Filtros" title="Filtros">
                        <img src="{{ asset('images/flitro.svg') }}" alt="Filtros" class="w-4 h-4">
                    </button>

                    <button type="button" @click="openCreateClient()" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-blue-200 bg-blue-50 text-blue-800 text-sm font-semibold hover:bg-blue-100" aria-label="Añadir cliente" title="Añadir cliente">
                        <img src="{{ asset('images/agregar-cliente.svg') }}" alt="Añadir cliente" class="w-4 h-4">
                    </button>

                    <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-blue-900 text-white text-sm font-semibold hover:bg-blue-800" aria-label="Buscar" title="Buscar">
                        <img src="{{ asset('images/buscar.svg') }}" alt="Buscar" class="w-4 h-4 brightness-0 invert">
                    </button>
                </div>

                <div x-show="showFilters" x-transition class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4" style="display: none;">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Estado</label>
                        <select name="status" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Todos</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactivo</option>
                        </select>
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
                            <th class="px-4 py-3">Cliente</th>
                            <th class="px-4 py-3">DNI</th>
                            <th class="px-4 py-3">Correo</th>
                            <th class="px-4 py-3">Teléfono</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($clients as $client)
                            <tr class="border-t">
                                <td class="px-4 py-3 font-semibold">{{ $client->code }}</td>
                                <td class="px-4 py-3">{{ $client->full_name }}</td>
                                <td class="px-4 py-3">{{ $client->dni }}</td>
                                <td class="px-4 py-3">{{ $client->email ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $client->phone ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $client->active ? 'Activo' : 'Inactivo' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            data-id="{{ $client->id }}"
                                            data-full-name="{{ e($client->full_name) }}"
                                            data-dni="{{ e($client->dni) }}"
                                            data-phone="{{ e((string) $client->phone) }}"
                                            data-email="{{ e((string) $client->email) }}"
                                            @click="openEditClient({
                                                id: $el.dataset.id,
                                                full_name: $el.dataset.fullName,
                                                dni: $el.dataset.dni,
                                                phone: $el.dataset.phone,
                                                email: $el.dataset.email
                                            })"
                                            class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-amber-500 text-white text-xs font-semibold hover:bg-amber-600"
                                            aria-label="Editar"
                                            title="Editar"
                                        >
                                            <img src="{{ asset('images/editar.svg') }}" alt="Editar" class="w-4 h-4 brightness-0 invert">
                                        </button>
                                        <form action="{{ route('admin.clientes.toggle-status', $client) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-white text-xs font-semibold {{ $client->active ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-600 hover:bg-emerald-700' }}" aria-label="{{ $client->active ? 'Desactivar' : 'Activar' }}" title="{{ $client->active ? 'Desactivar' : 'Activar' }}">
                                                <img src="{{ asset('images/eliminar-descativar.svg') }}" alt="{{ $client->active ? 'Desactivar' : 'Activar' }}" class="w-4 h-4 brightness-0 invert">
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="px-4 py-3" colspan="7">Sin datos por ahora.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>{{ $clients->links() }}</div>

        <div x-cloak x-show="showCreateClient" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="absolute inset-0 bg-slate-900/50" @click="closeCreateClient()"></div>
            <div class="relative w-full max-w-xl rounded-2xl bg-white border border-slate-200 shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-900">Nuevo cliente</h3>
                    <button type="button" @click="closeCreateClient()" class="w-9 h-9 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-700">&times;</button>
                </div>
                <form method="POST" action="{{ route('admin.clientes.store') }}" class="p-6 space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 gap-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">DNI / RUC</label>
                        <div class="flex gap-2">
                            <input type="text" x-model="lookupDoc" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Ej: 12345678 o 20123456789">
                            <button type="button" @click="buscarDocumento" :disabled="loadingLookup" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-blue-900 text-white text-sm font-semibold hover:bg-blue-800 disabled:opacity-60" aria-label="Buscar" title="Buscar">
                                <span x-show="!loadingLookup"><img src="{{ asset('images/buscar.svg') }}" alt="Buscar" class="w-4 h-4 brightness-0 invert"></span>
                                <span x-show="loadingLookup" style="display:none;">Buscando...</span>
                            </button>
                        </div>
                        <template x-if="lookupMessage">
                            <div class="rounded-lg px-3 py-2 text-sm"
                                 :class="lookupMessageType === 'ok' ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-rose-50 border border-rose-200 text-rose-700'">
                                <span x-text="lookupMessage"></span>
                            </div>
                        </template>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nombre completo</label>
                        <input type="text" name="full_name" x-model="formFullName" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">DNI / RUC</label>
                            <input type="text" name="dni" x-model="formDni" maxlength="11" pattern="(\d{8}|\d{11})" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Teléfono</label>
                            <input type="text" name="phone" x-model="formPhone" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Correo</label>
                        <input type="email" name="email" x-model="formEmail" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-900 text-white text-sm font-semibold hover:bg-blue-800">Guardar cliente</button>
                    </div>
                </form>
            </div>
        </div>

        <div x-cloak x-show="showEditClient" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="absolute inset-0 bg-slate-900/50" @click="closeEditClient()"></div>
            <div class="relative w-full max-w-xl rounded-2xl bg-white border border-slate-200 shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-900">Editar cliente</h3>
                    <button type="button" @click="closeEditClient()" class="w-9 h-9 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-700">&times;</button>
                </div>
                <form :action="`{{ url('/admin/clientes') }}/${editClientId}`" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nombre completo</label>
                        <input type="text" name="full_name" x-model="editClientFullName" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">DNI / RUC</label>
                            <input type="text" name="dni" x-model="editClientDni" maxlength="11" pattern="(\d{8}|\d{11})" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Teléfono</label>
                            <input type="text" name="phone" x-model="editClientPhone" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Correo</label>
                        <input type="email" name="email" x-model="editClientEmail" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="closeEditClient()" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-900 text-white text-sm font-semibold hover:bg-blue-800">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
