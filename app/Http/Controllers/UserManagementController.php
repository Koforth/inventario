<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    private const ROLES = ['admin', 'empleado', 'invitado'];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'search' => $search,
            'roles' => self::ROLES,
        ]);
    }

    public function edit(User $user): View
    {
        if (auth()->id() === $user->id) {
            abort(403, 'No puedes modificar tu propio usuario mientras tienes la sesion iniciada.');
        }

        return view('users.edit', [
            'user' => $user,
            'roles' => self::ROLES,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            abort(403, 'No puedes modificar tu propio usuario mientras tienes la sesion iniciada.');
        }

        $data = $request->validate([
            'role' => ['required', Rule::in(self::ROLES)],
        ]);

        $user->update($data);

        return redirect()
            ->route('users.index')
            ->with('success', 'Rol de usuario actualizado correctamente.');
    }
}
