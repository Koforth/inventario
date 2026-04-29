@extends('layout')

@section('title', 'Kardex | Sistema de Inventario')
@section('page-title', 'Kardex')
@section('page-subtitle', 'Resumen de saldos y movimientos con entradas y salidas de almacen')

@section('content')
    <div class="row g-4">
        <div class="col-xl-4">
            <div class="table-card p-3 p-md-4">
                <h2 class="h5 mb-3">Periodo de inventario</h2>
                <div class="mb-3">
                    <div class="text-muted small">Periodo actual</div>
                    <div class="fw-bold">{{ $period->period_key }}</div>
                    <div class="small">Desde {{ $period->starts_at->format('d/m/Y') }} hasta {{ $period->ends_at->format('d/m/Y') }}</div>
                </div>
                <hr>
                <h3 class="h6 mb-3">Registrar movimiento</h3>
                <form method="POST" action="{{ route('inventory.kardex.move') }}" class="row g-3">
                    @csrf
                    <div class="col-12">
                        <label class="form-label fw-semibold">Producto</label>
                        <select name="product_id" class="form-select" required>
                            <option value="">Seleccione</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->nombre }} (Stock: {{ $product->stock }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Operacion</label>
                        <select name="operation" id="operationType" class="form-select" required>
                            <option value="entrada">Entrada</option>
                            <option value="salida">Salida</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Cantidad</label>
                        <input name="quantity" type="number" min="1" class="form-control" required>
                    </div>
                    <div class="col-12 d-none" id="exitTypeGroup">
                        <label class="form-label fw-semibold">Tipo de salida</label>
                        <select name="exit_type" class="form-select">
                            <option value="traslado">Salida de almacen</option>
                            <option value="merma">Merma</option>
                        </select>
                    </div>
                    <div class="col-12 d-grid">
                        <button class="btn btn-primary">Guardar movimiento</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="table-card">
                <div class="border-bottom p-3 p-md-4">
                    <form method="GET" action="{{ route('inventory.kardex.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Producto</label>
                            <select name="product_id" class="form-select">
                                <option value="0">Todos</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" @selected($productId === $product->id)>{{ $product->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3"><label class="form-label fw-semibold">Desde</label><input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control"></div>
                        <div class="col-md-3"><label class="form-label fw-semibold">Hasta</label><input type="date" name="date_to" value="{{ $dateTo }}" class="form-control"></div>
                        <div class="col-md-2 d-grid"><button class="btn btn-outline-primary">Filtrar</button></div>
                    </form>
                    <div class="small text-muted mt-3">Productos: {{ $summary->products }} | Stock total: {{ $summary->stock }}</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Fecha</th><th>Producto</th><th>Tipo</th><th class="text-end">Cantidad</th><th>Usuario</th></tr></thead>
                        <tbody>
                            @forelse ($movements as $movement)
                                <tr>
                                    <td>{{ $movement->created_at->format('d/m/Y h:i A') }}</td>
                                    <td>{{ $movement->product?->nombre }}</td>
                                    <td class="text-capitalize">{{ $movement->tipo }}</td>
                                    <td class="text-end fw-bold">{{ $movement->cantidad }}</td>
                                    <td>{{ $movement->user?->name }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-4 text-center text-muted">Sin movimientos para el filtro.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-top px-3 px-md-4 py-3">{{ $movements->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const operation = document.getElementById('operationType');
            const exitTypeGroup = document.getElementById('exitTypeGroup');
            const toggle = () => exitTypeGroup.classList.toggle('d-none', operation.value !== 'salida');
            operation.addEventListener('change', toggle);
            toggle();
        });
    </script>
@endsection

