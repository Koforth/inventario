@extends('layout')

@section('title', 'Usuarios | Sistema de Inventario')
@section('page-title', 'Usuarios')
@section('page-subtitle', 'Administracion de roles y permisos del sistema')

@section('content')
    <div class="table-card">
        <div class="border-bottom p-3 p-md-4">
            <form method="GET" action="{{ route('users.index') }}" class="row g-3 align-items-end">
                <div class="col-md-10">
                    <label for="search" class="form-label fw-semibold">Buscar usuario</label>
                    <input
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="Nombre, correo o rol"
                    >
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-primary">Buscar</button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Roles actuales</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="fw-semibold">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @forelse ($user->roles as $role)
                                    <span class="badge text-bg-secondary">{{ $role->label }}</span>
                                @empty
                                    <span class="badge text-bg-light text-dark">Sin rol</span>
                                @endforelse
                                @if (auth()->id() === $user->id)
                                    <span class="badge text-bg-info ms-1">Sesion actual</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if (auth()->id() === $user->id)
                                    <button class="btn btn-outline-secondary btn-sm" disabled>No editable</button>
                                @else
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary btn-sm">
                                        Cambiar roles
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-5 text-center text-muted">
                                No hay usuarios para mostrar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-top px-3 px-md-4 py-3">
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
