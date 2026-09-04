@extends('layout')

@section('title', 'Crear rol | SmartZone')
@section('page-title', 'Crear rol')
@section('page-subtitle', 'Definicion de un nuevo rol con sus permisos')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3 p-md-4">
            <form method="POST" action="{{ route('roles.store') }}">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="label" class="form-label fw-semibold">Nombre del rol</label>
                        <input
                            id="label"
                            name="label"
                            value="{{ old('label') }}"
                            class="form-control"
                            required
                            autofocus
                        >
                        @error('label') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="name" class="form-label fw-semibold">Identificador</label>
                        <input
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            class="form-control"
                            required
                            placeholder="ej: jefe-ventas"
                        >
                        <div class="form-text">Solo minusculas, numeros y guiones. No se puede cambiar despues.</div>
                        @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-label fw-semibold">Permisos del rol</div>

                    <div class="card border-primary mb-3">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h3 class="h6 mb-0">Permisos por modulo</h3>
                            <button class="btn btn-sm btn-light js-collapse-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#modulePermissionsCreate" aria-expanded="true" aria-controls="modulePermissionsCreate">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                        <div id="modulePermissionsCreate" class="collapse show">
                            <div class="card-body">
                                <div class="row g-2">
                                    @forelse ($modulePermissions as $permission)
                                        <div class="col-md-4">
                                            <label class="border rounded-3 p-3 d-flex gap-2 h-100">
                                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="form-check-input mt-1" @checked(in_array($permission->id, old('permissions', [])))>
                                                <span>
                                                    <span class="fw-semibold d-block">{{ $permission->label }}</span>
                                                    <span class="text-muted small"><code>{{ $permission->name }}</code></span>
                                                </span>
                                            </label>
                                        </div>
                                    @empty
                                        <div class="col-12 text-muted small">No hay permisos por modulo.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-secondary mb-3">
                        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                            <h3 class="h6 mb-0">Permisos especiales</h3>
                            <button class="btn btn-sm btn-light js-collapse-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#specialPermissionsCreate" aria-expanded="false" aria-controls="specialPermissionsCreate">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <div id="specialPermissionsCreate" class="collapse">
                            <div class="card-body">
                                <div class="row g-2">
                                    @forelse ($specialPermissions as $permission)
                                        <div class="col-md-4">
                                            <label class="border rounded-3 p-3 d-flex gap-2 h-100">
                                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="form-check-input mt-1" @checked(in_array($permission->id, old('permissions', [])))>
                                                <span>
                                                    <span class="fw-semibold d-block">{{ $permission->label }}</span>
                                                    <span class="text-muted small"><code>{{ $permission->name }}</code></span>
                                                </span>
                                            </label>
                                        </div>
                                    @empty
                                        <div class="col-12 text-muted small">No hay permisos especiales.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    @error('permissions') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    @error('permissions.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="d-grid d-sm-flex justify-content-sm-end gap-2">
                    <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button class="btn btn-primary">Crear rol</button>
                </div>
            </form>
        </div>
    </div>
@endsection