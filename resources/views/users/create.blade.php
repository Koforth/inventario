@extends('layout')

@section('title', 'Crear usuario | Sistema de Inventario')
@section('page-title', 'Crear usuario')
@section('page-subtitle', 'Alta interna de usuarios del sistema')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3 p-md-4">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-semibold">Nombre completo</label>
                        <input
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            class="form-control"
                            required
                            autofocus
                        >
                        @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label fw-semibold">Correo electronico</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="form-control"
                            required
                        >
                        @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="password" class="form-label fw-semibold">Contraseña temporal</label>
                        <input id="password" type="password" name="password" class="form-control" required>
                        @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label fw-semibold">Confirmar Contraseña</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-label fw-semibold">Roles del usuario</div>
                    <div class="card border-primary mb-3">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h3 class="h6 mb-0">Roles por modulo</h3>
                            <button class="btn btn-sm btn-light js-collapse-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#moduleRolesCreate" aria-expanded="true" aria-controls="moduleRolesCreate">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                        <div id="moduleRolesCreate" class="collapse show">
                            <div class="card-body">
                                <div class="row g-2">
                                    @foreach ($moduleRoles as $role)
                                        <div class="col-md-4">
                                            <label class="border rounded-3 p-3 d-flex gap-2 h-100">
                                                <input type="checkbox" name="roles[]" value="{{ $role->id }}" class="form-check-input mt-1" @checked(in_array($role->id, old('roles', [])))>
                                                <span>
                                                    <span class="fw-semibold d-block">{{ $role->label }}</span>
                                                    <span class="text-muted small">{{ $role->permissions->pluck('label')->join(', ') }}</span>
                                                </span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-secondary mb-3">
                        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                            <h3 class="h6 mb-0">Roles especiales</h3>
                            <button class="btn btn-sm btn-light js-collapse-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#specialRolesCreate" aria-expanded="true" aria-controls="specialRolesCreate">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                        <div id="specialRolesCreate" class="collapse show">
                            <div class="card-body">
                                <div class="row g-2">
                                    @foreach ($specialRoles as $role)
                                        <div class="col-md-4">
                                            <label class="border rounded-3 p-3 d-flex gap-2 h-100">
                                                <input type="checkbox" name="roles[]" value="{{ $role->id }}" class="form-check-input mt-1" @checked(in_array($role->id, old('roles', [])))>
                                                <span>
                                                    <span class="fw-semibold d-block">{{ $role->label }}</span>
                                                    <span class="text-muted small">{{ $role->permissions->pluck('label')->join(', ') }}</span>
                                                </span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-info mb-3">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <h3 class="h6 mb-0">Roles con mas de un permiso</h3>
                            <button class="btn btn-sm btn-light js-collapse-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#multiPermissionRolesCreate" aria-expanded="false" aria-controls="multiPermissionRolesCreate">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <div id="multiPermissionRolesCreate" class="collapse">
                            <div class="card-body">
                                <div class="row g-2">
                                    @foreach ($multiPermissionRoles as $role)
                                        <div class="col-md-4">
                                            <label class="border rounded-3 p-3 d-flex gap-2 h-100">
                                                <input type="checkbox" name="roles[]" value="{{ $role->id }}" class="form-check-input mt-1" @checked(in_array($role->id, old('roles', [])))>
                                                <span>
                                                    <span class="fw-semibold d-block">{{ $role->label }}</span>
                                                    <span class="text-muted small">{{ $role->permissions->pluck('label')->join(', ') }}</span>
                                                </span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-dark mb-3">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <h3 class="h6 mb-0">Roles con un solo permiso</h3>
                            <button class="btn btn-sm btn-light js-collapse-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#singlePermissionRolesCreate" aria-expanded="false" aria-controls="singlePermissionRolesCreate">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <div id="singlePermissionRolesCreate" class="collapse">
                            <div class="card-body">
                                <div class="row g-2">
                                    @foreach ($singlePermissionRoles as $role)
                                        <div class="col-md-4">
                                            <label class="border rounded-3 p-3 d-flex gap-2 h-100">
                                                <input type="checkbox" name="roles[]" value="{{ $role->id }}" class="form-check-input mt-1" @checked(in_array($role->id, old('roles', [])))>
                                                <span>
                                                    <span class="fw-semibold d-block">{{ $role->label }}</span>
                                                    <span class="text-muted small">{{ $role->permissions->pluck('label')->join(', ') }}</span>
                                                </span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @error('roles') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    @error('roles.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="d-grid d-sm-flex justify-content-sm-end gap-2">
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button class="btn btn-primary">Crear usuario</button>
                </div>
            </form>
        </div>
    </div>
@endsection
