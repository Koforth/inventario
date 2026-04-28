@extends('layout')

@section('title', $title . ' | Almacen')
@section('page-title', $title)
@section('page-subtitle', $subtitle)

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="table-card p-3 p-md-4">
                <h2 class="h5 mb-3">Nuevo registro</h2>
                <form method="POST" action="{{ route($routeName . '.store') }}" class="d-grid gap-3">
                    @csrf
                    <div>
                        <label for="nombre" class="form-label fw-semibold">Nombre</label>
                        <input id="nombre" name="nombre" class="form-control" maxlength="120" required>
                    </div>
                    @if ($descriptionField)
                        <div>
                            <label for="descripcion" class="form-label fw-semibold">Descripcion</label>
                            <textarea id="descripcion" name="descripcion" rows="3" class="form-control" maxlength="255"></textarea>
                        </div>
                    @endif
                    <button class="btn btn-primary">Guardar</button>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="table-card">
                <div class="border-bottom p-3 p-md-4">
                    <form method="GET" action="{{ route($routeName . '.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-9">
                            <label for="search" class="form-label fw-semibold">Buscar</label>
                            <input id="search" name="search" value="{{ $search }}" class="form-control">
                        </div>
                        <div class="col-md-3 d-grid">
                            <button class="btn btn-primary">Buscar</button>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                @if ($descriptionField)
                                    <th>Descripcion</th>
                                @endif
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td class="fw-semibold">{{ $item->nombre }}</td>
                                    @if ($descriptionField)
                                        <td>{{ $item->descripcion ?: '-' }}</td>
                                    @endif
                                    <td>
                                        <div class="d-flex justify-content-end gap-2">
                                            <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#edit{{ $item->id }}">
                                                Editar
                                            </button>
                                            <form method="POST" action="{{ route($routeName . '.destroy', $item) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-outline-danger btn-sm">Eliminar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="collapse" id="edit{{ $item->id }}">
                                    <td colspan="{{ $descriptionField ? 3 : 2 }}" class="bg-light">
                                        <form method="POST" action="{{ route($routeName . '.update', $item) }}" class="row g-2 align-items-end">
                                            @csrf
                                            @method('PUT')
                                            <div class="{{ $descriptionField ? 'col-md-4' : 'col-md-8' }}">
                                                <label class="form-label fw-semibold">Nombre</label>
                                                <input name="nombre" value="{{ $item->nombre }}" class="form-control" required maxlength="120">
                                            </div>
                                            @if ($descriptionField)
                                                <div class="col-md-5">
                                                    <label class="form-label fw-semibold">Descripcion</label>
                                                    <input name="descripcion" value="{{ $item->descripcion }}" class="form-control" maxlength="255">
                                                </div>
                                            @endif
                                            <div class="col-md-3 d-grid">
                                                <button class="btn btn-primary">Actualizar</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $descriptionField ? 3 : 2 }}" class="py-5 text-center text-muted">
                                        No hay registros para mostrar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-top px-3 px-md-4 py-3">
                    {{ $items->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection
