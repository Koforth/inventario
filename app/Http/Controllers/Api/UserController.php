<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::with('roles')->orderBy('name')->get();

        return response()->json($users);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['roles'][0] ?? null,
        ]);

        $roleIds = Role::whereIn('name', $data['roles'])->pluck('id')->all();
        $user->roles()->sync($roleIds);

        return response()->json(['message' => 'Usuario creado.', 'user' => $user->load('roles')], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => ['nullable', 'string', 'min:8'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['exists:roles,name'],
        ]);

        if (isset($data['password'])) {
            $data['password'] = $data['password'];
        }

        $user->update($data);

        if (isset($data['roles'])) {
            $roleIds = Role::whereIn('name', $data['roles'])->pluck('id')->all();
            $user->roles()->sync($roleIds);
        }

        return response()->json(['message' => 'Usuario actualizado.', 'user' => $user->fresh('roles')]);
    }
}