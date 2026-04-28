<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
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

    public function edit(User $user): View
    {
        if (auth()->id() === $user->id) {
            abort(403, 'No puedes modificar tu propio usuario mientras tienes la sesion iniciada.');
        }

        return view('users.edit', [
            'user' => $user->load('roles'),
            'roles' => Role::query()->with('permissions')->orderBy('label')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            abort(403, 'No puedes modificar tu propio usuario mientras tienes la sesion iniciada.');
        }

        $data = $request->validate([
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ]);

        $user->roles()->sync($data['roles']);

        return redirect()
            ->route('users.index')
            ->with('success', 'Roles de usuario actualizados correctamente.');
    }
}
