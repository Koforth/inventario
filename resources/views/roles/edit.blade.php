@extends('layout')

@section('title', 'Editar rol | SmartZone')
@section('page-title', 'Editar rol')
@section('page-subtitle', $role->label)

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3 p-md-4">
            <form method="POST" action="{{ route('roles.update', $role) }}">
                @csrf
                @method('PUT')

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="label" class="form-label fw-semibold">Nombre del rol</label>
                        <input
                            id="label"
                            name="label"
                            value="{{ old('label', $role->label) }}"
                            class="form-control"
                            required
                            autofocus
                        >
                        @error('label') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Identificador</label>
                        <code class="d-block form-control bg-light">{{ $role->name }}</code>
                        <div class="form-text">El identificador no se puede cambiar.</div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-label fw-semibold">Permisos del rol</div>

                    @foreach ([
                        'module' => ['title' => 'Permisos por modulo', 'wrapper' => 'primary', 'target' => 'modulePermissionsEdit'],
                    ] as $group => $config)
                        <div class="card border-{{ $config['wrapper'] }} mb-3">
                            <div class="card-header bg-{{ $config['wrapper'] }} text-white d-flex justify-content-between align-items-center">
                                <h3 class="h6 mb-0">{{ $config['title'] }}</h3>
                                <button class="btn btn-sm btn-light js-collapse-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $config['target'] }}" aria-expanded="true" aria-controls="{{ $config['target'] }}">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                            <div id="{{ $config['target'] }}" class="collapse show">
                                <div class="card-body">
                                    <div class="row g-2">
                                        @forelse ($modulePermissions as $permission)
                                            <div class="col-md-4">
                                                <label class="border rounded-3 p-3 d-flex gap-2 h-100">
                                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="form-check-input mt-1" @checked(in_array($permission->id, old('permissions', $role->permissions->pluck('id')->all())))>
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
                    @endforeach

                    <div class="card border-secondary mb-3">
                        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                            <h3 class="h6 mb-0">Permisos especiales</h3>
                            <button class="btn btn-sm btn-light js-collapse-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#specialPermissionsEdit" aria-expanded="false" aria-controls="specialPermissionsEdit">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <div id="specialPermissionsEdit" class="collapse">
                            <div class="card-body">
                                <div class="row g-2">
                                    @forelse ($specialPermissions as $permission)
                                        <div class="col-md-4">
                                            <label class="border rounded-3 p-3 d-flex gap-2 h-100">
                                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="form-check-input mt-1" @checked(in_array($permission->id, old('permissions', $role->permissions->pluck('id')->all())))>
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

                    @if ($role->name === 'super-admin')
                        <div class="alert alert-warning small mb-0">
                            Este rol esta protegido. Solo un usuario con permiso <code>roles.manage</code> puede modificar sus permisos.
                        </div>
                    @endif
                </div>

                <div class="d-grid d-sm-flex justify-content-sm-end gap-2">
                    <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button class="btn btn-primary">Guardar rol</button>
                </div>
            </form>
        </div>
    </div>
@endsection