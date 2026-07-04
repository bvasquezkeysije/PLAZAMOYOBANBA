<x-layouts.admin>
    <x-slot name="title">Usuarios</x-slot>

    <div
        x-data="{
            showFilters: false,
            showRolesModal: {{ ($errors->any() || session('open_roles_modal')) ? 'true' : 'false' }},
            showWorkersModal: {{ ($errors->worker->any() || session('open_workers_modal') || request()->has('workers_page') || request()->filled('worker_q')) ? 'true' : 'false' }},
            showCreateRoleModal: false,
            showCreateWorkerModal: false,
            roleSearch: '',
            workerSearch: @js($workerSearch ?? ''),
            showEditRoleModal: false,
            showEditWorkerModal: false,
            editRoleId: null,
            editRoleName: '',
            editWorkerId: null,
            editWorkerFullName: '',
            editWorkerDocumentNumber: '',
            editWorkerPhone: '',
            editWorkerEmail: '',
            editWorkerAddress: '',
            editWorkerRoleId: '',
            showEditUserModal: false,
            editUserId: null,
            editUserWorkerId: '',
            editUserUsername: '',
            editUserEmail: '',
            selectedRoleId: null,
            rolesData: @js($roles->map(fn($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'is_active' => (bool) $r->is_active,
                'permissions' => $r->permissions->pluck('name')->values(),
            ])->values()),
            workersData: @js($workers->items()),
            get filteredRoles() {
                if (!this.roleSearch) return this.rolesData;
                return this.rolesData.filter(r => r.name.toLowerCase().includes(this.roleSearch.toLowerCase()));
            },
            get filteredWorkers() {
                if (!this.workerSearch) return this.workersData;
                const q = this.workerSearch.toLowerCase();
                return this.workersData.filter(w =>
                    (w.full_name || '').toLowerCase().includes(q) ||
                    (w.document_number || '').toLowerCase().includes(q) ||
                    (w.email || '').toLowerCase().includes(q) ||
                    (w.code || '').toLowerCase().includes(q)
                );
            },
            get selectedRole() {
                return this.rolesData.find(r => Number(r.id) === Number(this.selectedRoleId)) ?? null;
            },
            openEditRole(role) {
                this.editRoleId = role.id;
                this.editRoleName = role.name;
                this.showEditRoleModal = true;
            },
            openEditUser(user) {
                this.editUserId = user.id;
                this.editUserWorkerId = user.worker_id;
                this.editUserUsername = user.username;
                this.editUserEmail = user.email;
                this.showEditUserModal = true;
            },
            openEditWorker(worker) {
                this.editWorkerId = worker.id;
                this.editWorkerFullName = worker.full_name;
                this.editWorkerDocumentNumber = worker.document_number;
                this.editWorkerPhone = worker.phone || '';
                this.editWorkerEmail = worker.email || '';
                this.editWorkerAddress = worker.address || '';
                this.editWorkerRoleId = worker.role_id;
                this.showEditWorkerModal = true;
            }
        }"
        class="space-y-4"
    >
        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <form method="GET" action="{{ route('admin.users.index') }}" class="space-y-4">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                    <input
                        type="text"
                        name="q"
                        value="{{ $search }}"
                        class="w-full lg:flex-1 rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Buscar por nombre, usuario o correo"
                    >

                    <button
                        type="button"
                        @click="showFilters = !showFilters"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50"
                        aria-label="Filtros"
                        title="Filtros"
                    >
                        <img src="{{ asset('images/flitro.svg') }}" alt="Filtros" class="w-4 h-4">
                    </button>

                    <button
                        type="button"
                        @click="showRolesModal = true"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-blue-200 bg-blue-50 text-blue-800 text-sm font-semibold hover:bg-blue-100"
                        aria-label="Roles y permisos"
                        title="Roles y permisos"
                    >
                        <img src="{{ asset('images/roles.svg') }}" alt="Roles" class="w-4 h-4">
                    </button>
                    <button
                        type="button"
                        @click="showWorkersModal = true"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-800 text-sm font-semibold hover:bg-indigo-100"
                        aria-label="Gestionar trabajadores"
                        title="Gestionar trabajadores"
                    >
                        <img src="{{ asset('images/trabajadores.svg') }}" alt="Gestionar trabajadores" class="w-4 h-4">
                    </button>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-blue-900 text-white text-sm font-semibold hover:bg-blue-800"
                        aria-label="Buscar"
                        title="Buscar"
                    >
                        <img src="{{ asset('images/buscar.svg') }}" alt="Buscar" class="w-4 h-4 brightness-0 invert">
                    </button>
                </div>

                <div
                    x-show="showFilters"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4"
                    style="display: none;"
                >
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tipo de usuario</label>
                        <select name="role" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Todos</option>
                            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="recepcionista" {{ request('role') === 'recepcionista' ? 'selected' : '' }}>Recepcionista</option>
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

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Estado</label>
                        <select name="status" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Todos</option>
                            <option value="activo" {{ request('status') === 'activo' ? 'selected' : '' }}>Activo</option>
                            <option value="inactivo" {{ request('status') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
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
                            <th class="px-4 py-3">Nombre</th>
                            <th class="px-4 py-3">Usuario</th>
                            <th class="px-4 py-3">Correo</th>
                            <th class="px-4 py-3">Rol</th>
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr class="border-t">
                                <td class="px-4 py-3">{{ $user->worker->full_name ?? $user->name }}</td>
                                <td class="px-4 py-3">{{ $user->username }}</td>
                                <td class="px-4 py-3">{{ $user->email }}</td>
                                <td class="px-4 py-3">{{ $user->roles->pluck('name')->join(', ') ?: 'Sin rol' }}</td>
                                <td class="px-4 py-3">{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            data-id="{{ $user->id }}"
                                            data-worker-id="{{ $user->worker_id }}"
                                            data-username="{{ e($user->username) }}"
                                            data-email="{{ e($user->email) }}"
                                            @click="openEditUser({
                                                id: $el.dataset.id,
                                                worker_id: $el.dataset.workerId,
                                                username: $el.dataset.username,
                                                email: $el.dataset.email
                                            })"
                                            class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-amber-500 text-white text-xs font-semibold hover:bg-amber-600"
                                            aria-label="Editar"
                                            title="Editar"
                                        >
                                            <img src="{{ asset('images/editar.svg') }}" alt="Editar" class="w-4 h-4 brightness-0 invert">
                                        </button>
                                        <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-white text-xs font-semibold {{ $user->is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-emerald-600 hover:bg-emerald-700' }}" aria-label="{{ $user->is_active ? 'Desactivar' : 'Activar' }}" title="{{ $user->is_active ? 'Desactivar' : 'Activar' }}">
                                                <img src="{{ asset('images/eliminar-descativar.svg') }}" alt="{{ $user->is_active ? 'Desactivar' : 'Activar' }}" class="w-4 h-4 brightness-0 invert">
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-500">No hay usuarios registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $users->links() }}
        </div>

        <div x-show="showRolesModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="absolute inset-0 bg-slate-900/50" @click="showRolesModal = false"></div>

            <div class="relative w-full max-w-5xl rounded-2xl bg-white border border-slate-200 shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-900">Roles y permisos</h3>
                    <button type="button" @click="showRolesModal = false" class="w-9 h-9 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-700" aria-label="Cerrar">&times;</button>
                </div>

                <div class="p-6 space-y-6 max-h-[80vh] overflow-y-auto">
                    @if (session('roles_success'))
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            {{ session('roles_success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="rounded-xl border border-slate-200 p-4">
                                <h4 class="font-semibold text-slate-800 mb-3">Roles</h4>
                                <div class="flex flex-col md:flex-row gap-2">
                                    <input
                                        type="text"
                                        x-model="roleSearch"
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        placeholder="Buscar rol por nombre"
                                    >
                                    <button
                                        type="button"
                                        @click="showCreateRoleModal = true"
                                        class="px-4 py-2 rounded-lg bg-blue-900 text-white text-sm font-semibold hover:bg-blue-800 whitespace-nowrap"
                                    >
                                        Añadir rol
                                    </button>
                                </div>
                            </div>

                            <div class="rounded-xl border border-slate-200 overflow-hidden">
                                <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 font-semibold text-slate-800">Roles actuales</div>
                                <div class="max-h-64 overflow-y-auto">
                                    <table class="min-w-full text-sm">
                                        <thead class="bg-white text-left">
                                            <tr>
                                                <th class="px-4 py-2">Rol</th>
                                                <th class="px-4 py-2">Permisos</th>
                                                <th class="px-4 py-2">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="role in filteredRoles" :key="role.id">
                                                <tr
                                                    class="border-t cursor-pointer hover:bg-blue-50"
                                                    :class="Number(selectedRoleId) === Number(role.id) ? 'bg-blue-100' : ''"
                                                    @click="selectedRoleId = role.id"
                                                >
                                                    <td class="px-4 py-2 font-semibold text-slate-800" x-text="role.name"></td>
                                                    <td class="px-4 py-2 text-slate-600" x-text="role.permissions.length"></td>
                                                    <td class="px-4 py-2">
                                                        <div class="flex items-center gap-2">
                                                            <button
                                                                type="button"
                                                                @click.stop="openEditRole(role)"
                                                                class="inline-flex items-center justify-center px-2.5 py-1 rounded-md bg-amber-500 text-white text-xs font-semibold hover:bg-amber-600"
                                                                aria-label="Editar"
                                                                title="Editar"
                                                            >
                                                                <img src="{{ asset('images/editar.svg') }}" alt="Editar" class="w-4 h-4 brightness-0 invert">
                                                            </button>
                                                            <form :action="`{{ url('/admin/usuarios/roles') }}/${role.id}/estado`" method="POST" @click.stop>
                                                                @csrf
                                                                @method('PATCH')
                                                                <button
                                                                    type="submit"
                                                                    class="inline-flex items-center justify-center px-2.5 py-1 rounded-md text-xs font-semibold"
                                                                    :class="role.is_active ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-emerald-600 text-white hover:bg-emerald-700'"
                                                                    :aria-label="role.is_active ? 'Desactivar' : 'Activar'"
                                                                    :title="role.is_active ? 'Desactivar' : 'Activar'"
                                                                >
                                                                    <img src="{{ asset('images/eliminar-descativar.svg') }}" :alt="role.is_active ? 'Desactivar' : 'Activar'" class="w-4 h-4 brightness-0 invert">
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </template>
                                            <tr x-show="filteredRoles.length === 0">
                                                <td colspan="3" class="px-4 py-4 text-center text-slate-500">No se encontraron roles.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>

                        <div class="rounded-xl border border-slate-200 p-4">
                            <template x-if="selectedRole">
                                <div class="space-y-4">
                                    <div class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2">
                                        <p class="text-xs text-blue-700">Rol seleccionado</p>
                                        <p class="font-semibold text-blue-900" x-text="selectedRole.name"></p>
                                    </div>

                                    <form method="POST" action="{{ route('admin.users.roles.permissions.sync') }}" class="space-y-4">
                                        @csrf
                                        <input type="hidden" name="role_id" :value="selectedRoleId">

                                        <div>
                                            <p class="block text-xs font-semibold text-slate-700 mb-2">Permisos del rol</p>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-72 overflow-y-auto rounded-lg border border-slate-200 p-3">
                                                @foreach ($permissions as $permission)
                                                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                                        <input
                                                            type="checkbox"
                                                            name="permissions[]"
                                                            value="{{ $permission->name }}"
                                                            class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                                            :checked="selectedRole && selectedRole.permissions.includes('{{ $permission->name }}')"
                                                        >
                                                        <span>{{ $permission->name }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>

                                        <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">Actualizar permisos</button>
                                    </form>
                                </div>
                            </template>

                            <template x-if="!selectedRole">
                                <div class="h-full min-h-56 flex items-center justify-center rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 text-center text-slate-600">
                                    Selecciona un rol de la tabla o crea uno nuevo para administrar sus permisos.
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="showCreateRoleModal" x-transition class="fixed inset-0 z-[60] flex items-center justify-center p-4" style="display: none;">
            <div class="absolute inset-0 bg-slate-900/55" @click="showCreateRoleModal = false"></div>
            <div class="relative w-full max-w-2xl rounded-2xl bg-white border border-slate-200 shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-900">Crear rol</h3>
                    <button type="button" @click="showCreateRoleModal = false" class="w-9 h-9 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-700">&times;</button>
                </div>
                <form method="POST" action="{{ route('admin.users.roles.store') }}" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre del rol</label>
                        <input type="text" name="name" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Ej: supervisor" required>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-blue-900 text-white text-sm font-semibold hover:bg-blue-800">Guardar rol</button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="showWorkersModal" x-transition class="fixed inset-0 z-[65] flex items-center justify-center p-4" style="display: none;">
            <div class="absolute inset-0 bg-slate-900/55" @click="showWorkersModal = false"></div>
            <div class="relative w-full max-w-6xl rounded-2xl bg-white border border-slate-200 shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-900">Gestionar trabajadores</h3>
                    <button type="button" @click="showWorkersModal = false" class="w-9 h-9 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-700">&times;</button>
                </div>
                <div class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                    @if (session('workers_success'))
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            {{ session('workers_success') }}
                        </div>
                    @endif
                    @if ($errors->worker->any())
                        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            {{ $errors->worker->first() }}
                        </div>
                    @endif

                    <div class="rounded-xl border border-slate-200 p-4 bg-white">
                        <div class="flex flex-col md:flex-row gap-2">
                            <input type="text" x-model="workerSearch" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Buscar trabajador por nombre, DNI, correo o código">
                            <button type="button" @click="showCreateWorkerModal = true" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-blue-900 text-white text-sm font-semibold hover:bg-blue-800 whitespace-nowrap" aria-label="Añadir trabajador" title="Añadir trabajador">
                                <img src="{{ asset('images/agregar-trabajador.svg') }}" alt="Añadir trabajador" class="w-4 h-4 brightness-0 invert">
                            </button>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 overflow-hidden">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-left">
                                <tr>
                                    <th class="px-4 py-3">Código</th>
                                    <th class="px-4 py-3">Trabajador</th>
                                    <th class="px-4 py-3">Documento</th>
                                    <th class="px-4 py-3">Correo</th>
                                    <th class="px-4 py-3">Teléfono</th>
                                    <th class="px-4 py-3">Rol</th>
                                    <th class="px-4 py-3">Estado</th>
                                    <th class="px-4 py-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="worker in filteredWorkers" :key="worker.id">
                                    <tr class="border-t">
                                        <td class="px-4 py-3 font-semibold" x-text="worker.code"></td>
                                        <td class="px-4 py-3" x-text="worker.full_name"></td>
                                        <td class="px-4 py-3" x-text="worker.document_number"></td>
                                        <td class="px-4 py-3" x-text="worker.email || '-'"></td>
                                        <td class="px-4 py-3" x-text="worker.phone || '-'"></td>
                                        <td class="px-4 py-3" x-text="worker.role ? worker.role.name : '-'"></td>
                                        <td class="px-4 py-3" x-text="worker.is_active ? 'Activo' : 'Inactivo'"></td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <button
                                                    type="button"
                                                    @click="openEditWorker(worker)"
                                                    class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-amber-500 text-white text-xs font-semibold hover:bg-amber-600"
                                                    aria-label="Editar"
                                                    title="Editar"
                                                >
                                                    <img src="{{ asset('images/editar.svg') }}" alt="Editar" class="w-4 h-4 brightness-0 invert">
                                                </button>
                                                <form :action="`{{ url('/admin/usuarios/trabajadores') }}/${worker.id}/estado`" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-white text-xs font-semibold" :class="worker.is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-emerald-600 hover:bg-emerald-700'" :aria-label="worker.is_active ? 'Desactivar' : 'Activar'" :title="worker.is_active ? 'Desactivar' : 'Activar'">
                                                        <img src="{{ asset('images/eliminar-descativar.svg') }}" :alt="worker.is_active ? 'Desactivar' : 'Activar'" class="w-4 h-4 brightness-0 invert">
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="filteredWorkers.length === 0">
                                    <td colspan="8" class="px-4 py-4 text-center text-slate-500">No hay trabajadores registrados.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div>{{ $workers->links() }}</div>
                </div>
            </div>
        </div>

        <div x-show="showCreateWorkerModal" x-transition class="fixed inset-0 z-[75] flex items-center justify-center p-4" style="display: none;">
            <div class="absolute inset-0 bg-slate-900/60" @click="showCreateWorkerModal = false"></div>
            <div class="relative w-full max-w-2xl rounded-2xl bg-white border border-slate-200 shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-900">Añadir trabajador</h3>
                    <button type="button" @click="showCreateWorkerModal = false" class="w-9 h-9 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-700">&times;</button>
                </div>
                <form method="POST" action="{{ route('admin.users.workers.store') }}" class="p-6 space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre completo</label>
                            <input type="text" name="full_name" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">DNI / RUC</label>
                            <input type="text" name="document_number" maxlength="11" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Rol</label>
                                <select name="role_id" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                                    <option value="">Seleccionar rol</option>
                                @foreach ($workerRoles as $itemRole)
                                    @if ($itemRole->name !== 'admin')
                                        <option value="{{ $itemRole->id }}">{{ ucfirst($itemRole->name) }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Correo</label>
                            <input type="email" name="email" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Teléfono</label>
                            <input type="text" name="phone" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Dirección</label>
                            <input type="text" name="address" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-blue-900 text-white text-sm font-semibold hover:bg-blue-800">Guardar trabajador</button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="showEditWorkerModal" x-transition class="fixed inset-0 z-[75] flex items-center justify-center p-4" style="display: none;">
            <div class="absolute inset-0 bg-slate-900/60" @click="showEditWorkerModal = false"></div>
            <div class="relative w-full max-w-2xl rounded-2xl bg-white border border-slate-200 shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-900">Editar trabajador</h3>
                    <button type="button" @click="showEditWorkerModal = false" class="w-9 h-9 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-700">&times;</button>
                </div>
                <form :action="`{{ url('/admin/usuarios/trabajadores') }}/${editWorkerId}`" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PATCH')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre completo</label>
                            <input type="text" name="full_name" x-model="editWorkerFullName" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">DNI / RUC</label>
                            <input type="text" name="document_number" x-model="editWorkerDocumentNumber" maxlength="11" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Rol</label>
                            <select name="role_id" x-model="editWorkerRoleId" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                                <option value="">Seleccionar rol</option>
                                @foreach ($workerRoles as $itemRole)
                                    @if ($itemRole->name !== 'admin')
                                        <option value="{{ $itemRole->id }}">{{ ucfirst($itemRole->name) }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Correo</label>
                            <input type="email" name="email" x-model="editWorkerEmail" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Teléfono</label>
                            <input type="text" name="phone" x-model="editWorkerPhone" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Dirección</label>
                            <input type="text" name="address" x-model="editWorkerAddress" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-blue-900 text-white text-sm font-semibold hover:bg-blue-800">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="showEditRoleModal" x-transition class="fixed inset-0 z-[60] flex items-center justify-center p-4" style="display: none;">
            <div class="absolute inset-0 bg-slate-900/55" @click="showEditRoleModal = false"></div>
            <div class="relative w-full max-w-lg rounded-2xl bg-white border border-slate-200 shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-900">Editar rol</h3>
                    <button type="button" @click="showEditRoleModal = false" class="w-9 h-9 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-700">&times;</button>
                </div>
                <form :action="`{{ url('/admin/usuarios/roles') }}/${editRoleId}`" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre del rol</label>
                        <input type="text" name="name" x-model="editRoleName" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-blue-900 text-white text-sm font-semibold hover:bg-blue-800">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="showEditUserModal" x-transition class="fixed inset-0 z-[60] flex items-center justify-center p-4" style="display: none;">
            <div class="absolute inset-0 bg-slate-900/55" @click="showEditUserModal = false"></div>
            <div class="relative w-full max-w-xl rounded-2xl bg-white border border-slate-200 shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-900">Editar usuario</h3>
                    <button type="button" @click="showEditUserModal = false" class="w-9 h-9 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-700">&times;</button>
                </div>
                <form :action="`{{ url('/admin/usuarios') }}/${editUserId}`" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Trabajador</label>
                        <select name="worker_id" x-model="editUserWorkerId" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                            <option value="">Seleccionar trabajador</option>
                            @foreach ($assignableWorkers as $aw)
                                <option value="{{ $aw->id }}">{{ $aw->full_name }} - {{ $aw->document_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Usuario</label>
                        <input type="text" name="username" x-model="editUserUsername" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Correo</label>
                        <input type="email" name="email" x-model="editUserEmail" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-blue-900 text-white text-sm font-semibold hover:bg-blue-800">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
