@extends('layout')

@section('title', 'Comprobantes | SmartZone')
@section('page-title', 'Comprobantes')
@section('page-subtitle', 'Tipos de comprobante y tiraje/correlativo por tipo')

@section('content')
    <div class="row g-4">
        <div class="col-xl-4">
            <div class="table-card p-3 p-md-4">
                <h2 class="h5 mb-3">Nuevo tipo</h2>
                <form method="POST" action="{{ route('inventory.receipts.store') }}" class="row g-3">
                    @csrf
                    <div class="col-12"><label class="form-label fw-semibold">Nombre</label><input name="name" class="form-control" placeholder="FACTURA" required></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Codigo</label><input name="code" class="form-control" placeholder="factura" required></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Prefijo</label><input name="prefix" class="form-control" placeholder="F001"></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Correlativo actual</label><input name="current_number" type="number" min="0" class="form-control" value="0"></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Longitud</label><input name="padding" type="number" min="1" max="12" class="form-control" value="8"></div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" checked>
                            <label class="form-check-label fw-semibold" for="is_active">Activo</label>
                        </div>
                    </div>
                    <div class="col-12 d-grid"><button class="btn btn-primary">Guardar tipo</button></div>
                </form>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="table-card">
                <div class="border-bottom p-3 p-md-4"><h2 class="h5 mb-0">Tiraje configurado</h2></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Tipo</th><th>Codigo</th><th>Prefijo</th><th class="text-end">Correlativo</th><th class="text-center">Estado</th><th style="width: 320px;">Actualizar</th></tr></thead>
                        <tbody>
                            @forelse ($types as $type)
                                <tr>
                                    <td class="fw-bold">{{ $type->name }}</td>
                                    <td>{{ $type->code }}</td>
                                    <td>{{ $type->prefix ?: '-' }}</td>
                                    <td class="text-end">{{ $type->current_number }}</td>
                                    <td class="text-center"><span class="badge {{ $type->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $type->is_active ? 'Activo' : 'Inactivo' }}</span></td>
                                    <td>
                                        <form method="POST" action="{{ route('inventory.receipts.update', $type) }}" class="row g-2">
                                            @csrf
                                            @method('PUT')
                                            <div class="col-4"><input name="name" value="{{ $type->name }}" class="form-control form-control-sm" required></div>
                                            <div class="col-3"><input name="code" value="{{ $type->code }}" class="form-control form-control-sm" required></div>
                                            <div class="col-2"><input name="prefix" value="{{ $type->prefix }}" class="form-control form-control-sm"></div>
                                            <div class="col-3 d-flex gap-1">
                                                <input name="current_number" value="{{ $type->current_number }}" type="number" min="0" class="form-control form-control-sm" required>
                                                <input type="hidden" name="padding" value="{{ $type->padding }}">
                                                <input type="hidden" name="is_active" value="{{ $type->is_active ? 1 : 0 }}">
                                                <button class="btn btn-outline-primary btn-sm">Guardar</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-4 text-center text-muted">No hay tipos de comprobante configurados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-top px-3 px-md-4 py-3">{{ $types->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
@endsection

