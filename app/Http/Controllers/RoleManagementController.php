<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleManagementController extends Controller
{
    private const PROTECTED_ROLES = ['super-admin'];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $roles = Role::query()
            ->withCount(['permissions', 'users'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('label', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->orderBy('label')
            ->paginate(10)
            ->withQueryString();

        return view('roles.index', [
            'roles' => $roles,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        [$modulePermissions, $specialPermissions] = $this->groupedPermissions();

        return view('roles.create', [
            'modulePermissions' => $modulePermissions,
            'specialPermissions' => $specialPermissions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateRole($request);

        $role = Role::create([
            'name' => $data['name'],
            'label' => $data['label'],
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Rol creado correctamente.');
    }

    public function edit(Role $role): View
    {
        $role->load('permissions');

        [$modulePermissions, $specialPermissions] = $this->groupedPermissions();

        return view('roles.edit', [
            'role' => $role,
            'modulePermissions' => $modulePermissions,
            'specialPermissions' => $specialPermissions,
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        if ($this->isProtected($role) && ! $this->canManageProtected($request)) {
            abort(403, 'No tienes permiso para modificar roles protegidos.');
        }

        $data = $this->validateRole($request, $role);

        $role->update([
            'label' => $data['label'],
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        if ($this->isProtected($role) && ! $this->canManageProtected($request)) {
            abort(403, 'No tienes permiso para eliminar roles protegidos.');
        }

        if ($role->name === 'super-admin') {
            return back()->withErrors(['role' => 'El rol Super Admin no puede eliminarse.']);
        }

        if ($role->users()->exists()) {
            return back()->withErrors(['role' => 'No puedes eliminar un rol con usuarios asignados.']);
        }

        $role->permissions()->detach();
        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', 'Rol eliminado correctamente.');
    }

    private function validateRole(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:60',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('roles', 'name')->ignore($role?->id),
            ],
            'label' => ['required', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')],
        ]);
    }

    private function groupedPermissions(): array
    {
        $moduleNames = [
            'products.manage', 'warehouse.manage', 'inventory.entries.manage', 'kardex.manage',
            'customers.manage', 'sales.manage', 'cash.manage', 'quotes.manage', 'layaways.manage',
            'receipts.manage', 'purchases.manage', 'reports.manage', 'technicians.manage',
            'workshop.manage', 'users.manage',
        ];

        $permissions = Permission::orderBy('label')->get();

        $module = $permissions->filter(fn (Permission $p) => in_array($p->name, $moduleNames, true))->values();
        $special = $permissions->reject(fn (Permission $p) => in_array($p->name, $moduleNames, true))->values();

        return [$module, $special];
    }

    private function isProtected(Role $role): bool
    {
        return in_array($role->name, self::PROTECTED_ROLES, true);
    }

    private function canManageProtected(Request $request): bool
    {
        return $request->user()->can('roles.manage');
    }
}
