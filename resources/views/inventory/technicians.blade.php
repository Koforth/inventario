@extends('layout')

@section('title', 'Tecnicos | SmartZone')
@section('page-title', 'Tecnicos')
@section('page-subtitle', 'Listado de tecnicos para reparacion y mantenimiento')

@section('content')
    <div class="row g-4">
        <div class="col-xl-4">
            <div class="table-card p-3 p-md-4">
                <h2 class="h5 mb-3">Nuevo tecnico</h2>
                <form method="POST" action="{{ route('inventory.technicians.store') }}" class="row g-3">
                    @csrf
                    <div class="col-12"><label class="form-label fw-semibold">Nombre</label><input name="name" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Telefono</label><input name="phone" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Email</label><input name="email" type="email" class="form-control"></div>
                    <div class="col-12"><label class="form-label fw-semibold">Especialidad</label><input name="specialty" class="form-control"></div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" checked>
                            <label class="form-check-label fw-semibold" for="is_active">Activo</label>
                        </div>
                    </div>
                    <div class="col-12 d-grid"><button class="btn btn-primary">Guardar tecnico</button></div>
                </form>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="table-card">
                <div class="border-bottom p-3 p-md-4">
                    <form method="GET" action="{{ route('inventory.technicians.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-9"><label class="form-label fw-semibold">Buscar tecnico</label><input name="search" value="{{ $search }}" class="form-control" placeholder="Nombre, telefono, email o especialidad"></div>
                        <div class="col-md-3 d-grid"><button class="btn btn-outline-primary">Buscar</button></div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Tecnico</th><th>Contacto</th><th>Especialidad</th><th class="text-center">Estado</th></tr></thead>
                        <tbody>
                            @forelse ($technicians as $technician)
                                <tr>
                                    <td class="fw-bold">{{ $technician->name }}</td>
                                    <td>{{ $technician->phone ?: '-' }}<div class="small text-muted">{{ $technician->email ?: 'Sin correo' }}</div></td>
                                    <td>{{ $technician->specialty ?: '-' }}</td>
                                    <td class="text-center"><span class="badge {{ $technician->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $technician->is_active ? 'Activo' : 'Inactivo' }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-4 text-center text-muted">No hay tecnicos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-top px-3 px-md-4 py-3">{{ $technicians->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
@endsection

