<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    private const SENSITIVE_ROLES = ['super-admin'];
    private const MODULE_PERMISSIONS = [
        'products.manage',
        'warehouse.manage',
        'inventory.entries.manage',
        'kardex.manage',
        'customers.manage',
        'sales.manage',
        'cash.manage',
        'quotes.manage',
        'layaways.manage',
        'receipts.manage',
        'purchases.manage',
        'reports.manage',
        'technicians.manage',
        'workshop.manage',
        'users.manage',
    ];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $users = User::query()
            ->with('roles')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('roles', fn ($query) => $query->where('label', 'like', "%{$search}%"));
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'search' => $search,
        ]);
    }

    public function edit(Request $request, User $user): View
    {
        if (auth()->id() === $user->id) {
            abort(403, 'No puedes modificar tu propio usuario mientras tienes la sesion iniciada.');
        }

        $user->load('roles');

        if ($this->hasSensitiveRole($user) && ! $this->canManageSensitiveRoles($request)) {
            abort(403, 'No tienes permiso para modificar roles sensibles.');
        }

        $roles = $this->availableRoles($request);

        $moduleRoles = $roles->filter(function (Role $role) {
            $permissionNames = $role->permissions->pluck('name');
            return $permissionNames->isNotEmpty()
                && $permissionNames->every(fn (string $name) => in_array($name, self::MODULE_PERMISSIONS, true));
        })->values();

        $specialRoles = $roles->reject(
            fn (Role $role) => $moduleRoles->contains('id', $role->id)
        )->values();

        return view('users.edit', [
            'user' => $user,
            'roles' => $roles,
            'singlePermissionRoles' => $roles->filter(
                fn (Role $role) => $role->permissions->count() === 1
            )->values(),
            'multiPermissionRoles' => $roles->filter(
                fn (Role $role) => $role->permissions->count() > 1
            )->values(),
            'moduleRoles' => $moduleRoles,
            'specialRoles' => $specialRoles,
        ]);
    }

    public function create(Request $request): View
    {
        $roles = $this->availableRoles($request);

        $moduleRoles = $roles->filter(function (Role $role) {
            $permissionNames = $role->permissions->pluck('name');
            return $permissionNames->isNotEmpty()
                && $permissionNames->every(fn (string $name) => in_array($name, self::MODULE_PERMISSIONS, true));
        })->values();

        $specialRoles = $roles->reject(
            fn (Role $role) => $moduleRoles->contains('id', $role->id)
        )->values();

        return view('users.create', [
            'roles' => $roles,
            'singlePermissionRoles' => $roles->filter(
                fn (Role $role) => $role->permissions->count() === 1
            )->values(),
            'multiPermissionRoles' => $roles->filter(
                fn (Role $role) => $role->permissions->count() > 1
            )->values(),
            'moduleRoles' => $moduleRoles,
            'specialRoles' => $specialRoles,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $allowedRoleIds = $this->availableRoles($request)->pluck('id')->all();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['integer', Rule::in($allowedRoleIds)],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $user->roles()->sync($data['roles']);

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            abort(403, 'No puedes modificar tu propio usuario mientras tienes la sesion iniciada.');
        }

        $user->load('roles');

        if ($this->hasSensitiveRole($user) && ! $this->canManageSensitiveRoles($request)) {
            abort(403, 'No tienes permiso para modificar roles sensibles.');
        }

        $allowedRoleIds = $this->availableRoles($request)->pluck('id')->all();

        $data = $request->validate([
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['integer', Rule::in($allowedRoleIds)],
        ]);

        $user->roles()->sync($data['roles']);

        return redirect()
            ->route('users.index')
            ->with('success', 'Roles de usuario actualizados correctamente.');
    }

    private function availableRoles(Request $request)
    {
        return Role::query()
            ->with('permissions')
            ->when(
                ! $this->canManageSensitiveRoles($request),
                fn ($query) => $query->whereNotIn('name', self::SENSITIVE_ROLES)
            )
            ->orderBy('label')
            ->get();
    }

    private function canManageSensitiveRoles(Request $request): bool
    {
        return $request->user()->can('roles.manage');
    }

    private function hasSensitiveRole(User $user): bool
    {
        return $user->roles->contains(fn (Role $role) => in_array($role->name, self::SENSITIVE_ROLES, true));
    }
}
