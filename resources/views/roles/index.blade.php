@extends('layout')

@section('title', 'Roles | SmartZone')
@section('page-title', 'Roles')
@section('page-subtitle', 'Administracion de roles y sus permisos')

@section('content')
    <div class="table-card">
        <div class="border-bottom p-3 p-md-4">
            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('roles.create') }}" class="btn btn-primary">Crear rol</a>
            </div>

            <form method="GET" action="{{ route('roles.index') }}" class="row g-3 align-items-end">
                <div class="col-md-10">
                    <label for="search" class="form-label fw-semibold">Buscar rol</label>
                    <input
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="Nombre o identificador"
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
                        <th>Rol</th>
                        <th>Identificador</th>
                        <th class="text-center">Permisos</th>
                        <th class="text-center">Usuarios</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td class="fw-semibold">
                                {{ $role->label }}
                                @if ($role->name === 'super-admin')
                                    <span class="badge text-bg-danger ms-1">Protegido</span>
                                @endif
                            </td>
                            <td><code>{{ $role->name }}</code></td>
                            <td class="text-center">{{ $role->permissions_count }}</td>
                            <td class="text-center">{{ $role->users_count }}</td>
                            <td class="text-end">
                                <a href="{{ route('roles.edit', $role) }}" class="btn btn-outline-primary btn-sm">
                                    Editar permisos
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-5 text-center text-muted">
                                No hay roles para mostrar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-top px-3 px-md-4 py-3">
            {{ $roles->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
