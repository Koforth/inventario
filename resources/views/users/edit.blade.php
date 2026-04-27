@extends('layout')

@section('title', 'Editar rol | Sistema de Inventario')
@section('page-title', 'Cambiar rol')
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
                    <label for="role" class="form-label fw-semibold">Rol del usuario</label>
                    <select id="role" name="role" class="form-select" required>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>
                                {{ ucfirst($role) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="d-grid d-sm-flex justify-content-sm-end gap-2">
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button class="btn btn-primary">Guardar rol</button>
                </div>
            </form>
        </div>
    </div>
@endsection
