@extends('layout')

@section('title', 'Reporte de Ventas | SmartZone')
@section('page-title', 'Reporte de Ventas')
@section('page-subtitle', 'Ventas registradas por periodo')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Ventas en el periodo</div>
                <div class="display-6 fw-bold">{{ $totals->count }}</div>
                <div class="text-muted small">{{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Total vendido</div>
                <div class="display-6 fw-bold text-success">C$ {{ number_format($totals->total, 2) }}</div>
                <div class="text-muted small">Valor bruto de ventas</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Descuentos</div>
                <div class="display-6 fw-bold text-danger">C$ {{ number_format($totals->discounts, 2) }}</div>
                <div class="text-muted small">Total en descuentos aplicados</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Credito pendiente</div>
                <div class="display-6 fw-bold text-warning">C$ {{ number_format($totals->credit_pending, 2) }}</div>
                <div class="text-muted small">Saldo de ventas al credito</div>
            </div>
        </div>
    </div>

    <div class="table-card mb-4">
        <div class="border-bottom p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center gap-3 mb-4">
                <div>
                    <h2 class="h5 mb-1">Ventas (Maestro - Detalle)</h2>
                    <div class="text-muted small">Cada venta con su detalle de productos.</div>
                </div>
                <a href="{{ route('reports.sales.export', request()->only(['from', 'to', 'sale_type'])) }}" class="btn btn-success w-100 w-sm-auto">
                    Descargar Excel
                </a>
            </div>
            <form method="GET" action="{{ route('reports.sales') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="from" class="form-label fw-semibold">Desde</label>
                    <input id="from" name="from" type="date" value="{{ $from }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="to" class="form-label fw-semibold">Hasta</label>
                    <input id="to" name="to" type="date" value="{{ $to }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="sale_type" class="form-label fw-semibold">Tipo</label>
                    <select id="sale_type" name="sale_type" class="form-select">
                        <option value="all" @selected($saleType === 'all')>Todas</option>
                        <option value="contado" @selected($saleType === 'contado')>Contado</option>
                        <option value="credito" @selected($saleType === 'credito')>Credito</option>
                    </select>
                </div>
                <div class="col-md-3 d-grid">
                    <button class="btn btn-primary">Filtrar</button>
                </div>
            </form>
        </div>

        @forelse ($sales as $sale)
            <div class="border-bottom p-3 p-md-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <div>
                        <span class="fw-bold">{{ $sale->number }}</span>
                        <span class="badge text-bg-secondary ms-2">{{ strtoupper($sale->receipt_type) }} {{ $sale->receipt_number }}</span>
                        <span class="badge {{ $sale->sale_type === 'contado' ? 'text-bg-success' : 'text-bg-warning' }} ms-1">{{ ucfirst($sale->sale_type) }}</span>
                    </div>
                    <div class="text-end">
                        <span class="fw-bold fs-5">C$ {{ number_format($sale->total, 2) }}</span>
                    </div>
                </div>
                <div class="text-muted small mb-2">
                    {{ $sale->created_at->format('d/m/Y h:i A') }}
                    | {{ $sale->customer?->name ?: 'Consumidor final' }}
                    | {{ $sale->user?->name ?: 'Sin usuario' }}
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Cant</th>
                                <th class="text-end">P. Unitario</th>
                                <th class="text-center">Desc.</th>
                                <th class="text-end">Importe</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sale->items as $item)
                                <tr>
                                    <td>{{ $item->product?->nombre ?? 'Producto eliminado' }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">C$ {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-center">{{ $item->discount > 0 ? 'C$ ' . number_format($item->discount, 2) : '-' }}</td>
                                    <td class="text-end fw-semibold">C$ {{ number_format($item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="p-5 text-center text-muted">No hay ventas en el periodo seleccionado.</div>
        @endforelse

        <div class="border-top px-3 px-md-4 py-3">
            {{ $sales->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection