<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->q;
        $role = $request->role;
        $status = $request->status;

        $users = User::query()
            ->with(['roles', 'worker'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('username', 'like', '%'.$search.'%')
                        ->orWhereHas('worker', function ($ws) use ($search) {
                            $ws->where('full_name', 'like', '%'.$search.'%')
                                ->orWhere('document_number', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($role, function ($query) use ($role) {
                $query->whereHas('roles', function ($sub) use ($role) {
                    $sub->where('name', $role);
                });
            })
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('is_active', $status === 'activo');
            })
            ->latest()
            ->paginate(10);

        $users->appends(request()->query());

        $roles = Role::query()->with('permissions')->orderBy('name')->get();
        $workerRoles = Role::query()->orderBy('name')->get();
        $permissions = Permission::query()->orderBy('name')->get();
        $workerSearch = $request->worker_q;
        $workers = Worker::query()
            ->with('role')
            ->when($workerSearch, function ($query) use ($workerSearch) {
                $query->where(function ($sub) use ($workerSearch) {
                    $sub->where('full_name', 'like', '%'.$workerSearch.'%')
                        ->orWhere('document_number', 'like', '%'.$workerSearch.'%')
                        ->orWhere('email', 'like', '%'.$workerSearch.'%')
                        ->orWhere('code', 'like', '%'.$workerSearch.'%');
                });
            })
            ->latest()
            ->paginate(8, ['*'], 'workers_page');
        $workers->appends(request()->query());
        $assignableWorkers = Worker::query()
            ->whereHas('role', fn ($q) => $q->where('name', '!=', 'limpieza'))
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'document_number']);

        return view('admin.users.index', compact('users', 'search', 'role', 'status', 'roles', 'workerRoles', 'permissions', 'workers', 'workerSearch', 'assignableWorkers'));
    }

    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:roles,name'],
        ]);

        Role::create(['name' => $validated['name'], 'is_active' => true]);

        return back()
            ->with('roles_success', 'Rol creado correctamente.')
            ->with('open_roles_modal', true);
    }

    public function syncRolePermissions(Request $request)
    {
        $validated = $request->validate([
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'max:100'],
        ]);

        $role = Role::findOrFail($validated['role_id']);
        $role->syncPermissions($validated['permissions'] ?? []);

        return back()
            ->with('roles_success', 'Permisos del rol actualizados correctamente.')
            ->with('open_roles_modal', true);
    }

    public function updateRole(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:roles,name,'.$role->id],
        ]);

        $role->update(['name' => $validated['name']]);

        return back()
            ->with('roles_success', 'Rol actualizado correctamente.')
            ->with('open_roles_modal', true);
    }

    public function toggleRoleStatus(Role $role)
    {
        $role->update(['is_active' => !$role->is_active]);

        return back()
            ->with('roles_success', $role->is_active ? 'Rol activado correctamente.' : 'Rol desactivado correctamente.')
            ->with('open_roles_modal', true);
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:80', 'unique:users,username,'.$user->id],
            'email' => ['required', 'email', 'max:150', 'unique:users,email,'.$user->id],
            'worker_id' => ['required', 'integer', 'exists:workers,id', 'unique:users,worker_id,'.$user->id],
        ]);

        $worker = Worker::findOrFail((int) $validated['worker_id']);
        $user->update([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'worker_id' => $worker->id,
            'name' => $worker->full_name,
        ]);

        return back()->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggleUserStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        return back()->with('success', $user->is_active ? 'Usuario activado correctamente.' : 'Usuario desactivado correctamente.');
    }

    public function storeWorker(Request $request)
    {
        $validated = $request->validateWithBag('worker', [
            'full_name' => ['required', 'string', 'max:160'],
            'document_number' => ['required', 'regex:/^(\d{8}|\d{11})$/', 'unique:workers,document_number'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150', 'unique:workers,email'],
            'address' => ['nullable', 'string', 'max:200'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ]);

        $next = (int) (Worker::query()->max('id') ?? 0) + 1;
        $validated['code'] = sprintf('TRB-%04d', $next);
        $validated['is_active'] = true;

        Worker::query()->create($validated);

        return back()
            ->with('workers_success', 'Trabajador registrado correctamente.')
            ->with('open_workers_modal', true);
    }

    public function updateWorker(Request $request, Worker $worker)
    {
        $validated = $request->validateWithBag('worker', [
            'full_name' => ['required', 'string', 'max:160'],
            'document_number' => ['required', 'regex:/^(\d{8}|\d{11})$/', 'unique:workers,document_number,'.$worker->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150', 'unique:workers,email,'.$worker->id],
            'address' => ['nullable', 'string', 'max:200'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ]);

        $worker->update($validated);

        return back()
            ->with('workers_success', 'Trabajador actualizado correctamente.')
            ->with('open_workers_modal', true);
    }

    public function toggleWorkerStatus(Worker $worker)
    {
        $worker->update(['is_active' => !$worker->is_active]);

        return back()
            ->with('workers_success', $worker->is_active ? 'Trabajador activado correctamente.' : 'Trabajador desactivado correctamente.')
            ->with('open_workers_modal', true);
    }
}
