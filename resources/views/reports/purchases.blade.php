@extends('layout')

@section('title', 'Reporte de Compras | SmartZone')
@section('page-title', 'Reporte de Compras')
@section('page-subtitle', 'Compras realizadas por periodo')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Compras en el periodo</div>
                <div class="display-6 fw-bold">{{ $totals->count }}</div>
                <div class="text-muted small">{{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Total comprado</div>
                <div class="display-6 fw-bold text-success">C$ {{ number_format($totals->total, 2) }}</div>
                <div class="text-muted small">Valor bruto de compras</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Proveedores</div>
                <div class="display-6 fw-bold">{{ $suppliers->count() }}</div>
                <div class="text-muted small">Registrados en el sistema</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Promedio por compra</div>
                <div class="display-6 fw-bold text-primary">C$ {{ $totals->count > 0 ? number_format($totals->total / $totals->count, 2) : '0.00' }}</div>
                <div class="text-muted small">Valor medio de cada compra</div>
            </div>
        </div>
    </div>

    <div class="table-card mb-4">
        <div class="border-bottom p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center gap-3 mb-4">
                <div>
                    <h2 class="h5 mb-1">Compras (Maestro - Detalle)</h2>
                    <div class="text-muted small">Cada compra con su detalle de productos recibidos.</div>
                </div>
                <a href="{{ route('reports.purchases.export', request()->only(['from', 'to', 'supplier_id'])) }}" class="btn btn-success w-100 w-sm-auto">
                    Descargar Excel
                </a>
            </div>
            <form method="GET" action="{{ route('reports.purchases') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="from" class="form-label fw-semibold">Desde</label>
                    <input id="from" name="from" type="date" value="{{ $from }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="to" class="form-label fw-semibold">Hasta</label>
                    <input id="to" name="to" type="date" value="{{ $to }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="supplier_id" class="form-label fw-semibold">Proveedor</label>
                    <select id="supplier_id" name="supplier_id" class="form-select">
                        <option value="all" @selected($supplierId === 'all')>Todos</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected($supplierId === (string) $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-grid">
                    <button class="btn btn-primary">Filtrar</button>
                </div>
            </form>
        </div>

        @forelse ($purchases as $purchase)
            <div class="border-bottom p-3 p-md-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <div>
                        <span class="fw-bold">{{ $purchase->number }}</span>
                        <span class="badge {{ $purchase->purchase_type === 'contado' ? 'text-bg-success' : 'text-bg-warning' }} ms-2">{{ ucfirst($purchase->purchase_type) }}</span>
                        <span class="badge text-bg-info ms-1">{{ $purchase->supplier?->name ?? 'Sin proveedor' }}</span>
                    </div>
                    <div class="text-end">
                        <span class="fw-bold fs-5">C$ {{ number_format($purchase->total, 2) }}</span>
                    </div>
                </div>
                <div class="text-muted small mb-2">
                    {{ $purchase->created_at->format('d/m/Y h:i A') }}
                    | {{ $purchase->user?->name ?: 'Sin usuario' }}
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Cant</th>
                                <th class="text-end">Costo unitario</th>
                                <th class="text-end">Impuesto</th>
                                <th class="text-end">Importe</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchase->items as $item)
                                <tr>
                                    <td>{{ $item->product?->nombre ?? 'Producto eliminado' }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">C$ {{ number_format($item->unit_cost, 2) }}</td>
                                    <td class="text-end">C$ {{ number_format($item->tax_amount, 2) }}</td>
                                    <td class="text-end fw-semibold">C$ {{ number_format($item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="p-5 text-center text-muted">No hay compras en el periodo seleccionado.</div>
        @endforelse

        <div class="border-top px-3 px-md-4 py-3">
            {{ $purchases->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection