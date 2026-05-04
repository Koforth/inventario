@extends('layout')

@section('title', 'Dashboard | Sistema de Inventario')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Resumen operativo del negocio en tiempo real')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3"><div class="metric-card p-4 h-100"><div class="text-muted small text-uppercase fw-semibold">Ventas hoy</div><div class="display-6 fw-bold">{{ $stats['today_sales_count'] }}</div><div class="small text-success">C$ {{ number_format($stats['today_sales_amount'], 2) }}</div></div></div>
        <div class="col-md-6 col-xl-3"><div class="metric-card p-4 h-100"><div class="text-muted small text-uppercase fw-semibold">Caja</div><div class="display-6 fw-bold {{ $stats['cash_open'] ? 'text-success' : 'text-secondary' }}">{{ $stats['cash_open'] ? 'Abierta' : 'Cerrada' }}</div><div class="small">Ingresos hoy: C$ {{ number_format($stats['cash_in_today'], 2) }}</div></div></div>
        <div class="col-md-6 col-xl-3"><div class="metric-card p-4 h-100"><div class="text-muted small text-uppercase fw-semibold">Bajo stock</div><div class="display-6 fw-bold text-warning">{{ $stats['low_stock'] }}</div><div class="small">Valor inventario: C$ {{ number_format($stats['inventory_value'], 2) }}</div></div></div>
        <div class="col-md-6 col-xl-3"><div class="metric-card p-4 h-100"><div class="text-muted small text-uppercase fw-semibold">Apartados pendientes</div><div class="display-6 fw-bold">{{ $stats['layaways_pending'] }}</div><div class="small text-danger">Credito pendiente: C$ {{ number_format($stats['credit_sales_pending'], 2) }}</div></div></div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="table-card h-100">
                <div class="border-bottom p-3 p-md-4 d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Ventas recientes</h2>
                    <a href="{{ route('inventory.sales.index') }}" class="btn btn-outline-primary btn-sm">Ir a ventas</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Nro</th><th>Cliente</th><th>Tipo</th><th>Comprobante</th><th class="text-end">Total</th><th>Fecha</th></tr></thead>
                        <tbody>
                            @forelse ($recentSales as $sale)
                                <tr>
                                    <td>{{ $sale->number }}</td>
                                    <td>{{ $sale->customer?->name ?: 'Consumidor final' }}</td>
                                    <td class="text-capitalize">{{ $sale->sale_type }}</td>
                                    <td>{{ $sale->receipt_number ?: ucfirst($sale->receipt_type) }}</td>
                                    <td class="text-end fw-bold">C$ {{ number_format((float) $sale->total, 2) }}</td>
                                    <td>{{ $sale->created_at->format('d/m/Y h:i A') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-4 text-center text-muted">Sin ventas recientes.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="table-card h-100">
                <div class="border-bottom p-3 p-md-4">
                    <h2 class="h5 mb-0">Accesos rapidos</h2>
                </div>
                <div class="p-3 p-md-4 d-grid gap-2">
                    <a href="{{ route('products.index') }}" class="btn btn-outline-primary text-start">Catalogo de productos</a>
                    <a href="{{ route('inventory.kardex.index') }}" class="btn btn-outline-primary text-start">Kardex</a>
                    <a href="{{ route('inventory.layaways.index') }}" class="btn btn-outline-primary text-start">Apartados y creditos</a>
                    <a href="{{ route('inventory.workshop.index') }}" class="btn btn-outline-primary text-start">Ordenes de taller</a>
                    <a href="{{ route('cash.index') }}" class="btn btn-outline-primary text-start">Caja diaria</a>
                    @can('reports.manage')
                        <a href="{{ route('reports.index') }}" class="btn btn-outline-primary text-start">Reportes</a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="table-card h-100">
                <div class="border-bottom p-3 p-md-4"><h2 class="h5 mb-0">Proximos retiros (apartados)</h2></div>
                <div class="list-group list-group-flush">
                    @forelse ($nextLayaways as $layaway)
                        <div class="list-group-item px-3 px-md-4 py-3">
                            <div class="fw-bold">{{ $layaway->number }}</div>
                            <div class="small">{{ $layaway->customer?->name }}</div>
                            <div class="small text-muted">{{ $layaway->pickup_at->format('d/m/Y h:i A') }} - Saldo C$ {{ number_format((float) $layaway->balance, 2) }}</div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted">No hay retiros pendientes.</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="table-card h-100">
                <div class="border-bottom p-3 p-md-4"><h2 class="h5 mb-0">Movimientos recientes</h2></div>
                <div class="list-group list-group-flush">
                    @forelse ($recentMovements as $movement)
                        <div class="list-group-item px-3 px-md-4 py-3 d-flex justify-content-between align-items-start gap-3">
                            <div><div class="fw-bold">{{ $movement->product?->nombre ?: 'Producto eliminado' }}</div><div class="small text-muted">{{ $movement->created_at->format('d/m/Y h:i A') }}</div></div>
                            <span class="badge text-bg-light text-uppercase border">{{ $movement->tipo }} x{{ $movement->cantidad }}</span>
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted">Sin movimientos recientes.</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="table-card h-100">
                <div class="border-bottom p-3 p-md-4"><h2 class="h5 mb-0">Taller</h2></div>
                <div class="p-3 p-md-4">
                    <div class="mb-3">
                        <div class="text-muted small text-uppercase fw-semibold">Ordenes activas</div>
                        <div class="display-6 fw-bold">{{ $stats['workshop_open'] }}</div>
                    </div>
                    <div class="small text-muted mb-3">Compras del mes: C$ {{ number_format($stats['purchases_month'], 2) }}</div>
                    <ul class="list-group list-group-flush">
                        @forelse ($recentWorkshopOrders as $order)
                            <li class="list-group-item px-0">
                                <div class="fw-bold">{{ $order->number }} - {{ $order->device_name }}</div>
                                <div class="small">{{ $order->customer?->name }} | {{ $order->technician?->name ?: 'Sin tecnico' }}</div>
                            </li>
                        @empty
                            <li class="list-group-item px-0 text-muted">Sin ordenes registradas.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
