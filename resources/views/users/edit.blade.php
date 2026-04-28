@extends('layout')

@section('title', 'Editar roles | Sistema de Inventario')
@section('page-title', 'Cambiar roles')
@section('page-subtitle', $user->name)

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3 p-md-4">
            <div class="mb-4">
                <div class="text-muted small fw-semibold text-uppercase">Usuario</div>
                <h1 class="h4 mb-1">{{ $user->name }}</h1>
                <div class="text-muted">{{ $user->email }}</div>
            </div>

            <form method="POST" action="{{ route('users.update', $user) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <div class="form-label fw-semibold">Roles del usuario</div>
                    <div class="row g-2">
                        @foreach ($roles as $role)
                            <div class="col-md-4">
                                <label class="border rounded-3 p-3 d-flex gap-2 h-100">
                                    <input
                                        type="checkbox"
                                        name="roles[]"
                                        value="{{ $role->id }}"
                                        class="form-check-input mt-1"
                                        @checked(in_array($role->id, old('roles', $user->roles->pluck('id')->all())))
                                    >
                                    <span>
                                        <span class="fw-semibold d-block">{{ $role->label }}</span>
                                        <span class="text-muted small">{{ $role->permissions->pluck('label')->join(', ') }}</span>
                                    </span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('roles') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    @error('roles.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="d-grid d-sm-flex justify-content-sm-end gap-2">
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button class="btn btn-primary">Guardar roles</button>
                </div>
            </form>
        </div>
    </div>
@endsection
