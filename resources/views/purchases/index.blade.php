@extends('layout')

@section('title', 'Compras | Sistema de Inventario')
@section('page-title', 'Compras')
@section('page-subtitle', 'Consulta por fechas o por mes')

@section('content')
    <div class="d-flex flex-wrap justify-content-end gap-2 mb-3">
        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-primary">Proveedores</a>
        <a href="{{ route('purchases.price-history') }}" class="btn btn-outline-primary">Historial de precios</a>
        <a href="{{ route('purchases.credits') }}" class="btn btn-outline-primary">Compras al credito</a>
        <a href="{{ route('purchases.create') }}" class="btn btn-primary">Realizar compra</a>
    </div>

    <div class="table-card">
        <div class="border-bottom p-3 p-md-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-2"><label class="form-label fw-semibold">Desde</label><input name="date_from" type="date" value="{{ $dateFrom }}" class="form-control"></div>
                <div class="col-md-2"><label class="form-label fw-semibold">Hasta</label><input name="date_to" type="date" value="{{ $dateTo }}" class="form-control"></div>
                <div class="col-md-2"><label class="form-label fw-semibold">Mes</label><input name="month" type="month" value="{{ $month }}" class="form-control"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Buscar</label><input name="search" value="{{ $search }}" class="form-control" placeholder="Numero o proveedor"></div>
                <div class="col-md-2 d-grid"><button class="btn btn-primary">Consultar</button></div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Numero</th><th>Proveedor</th><th>Fecha</th><th>Tipo</th><th class="text-end">Total</th><th class="text-end">Saldo</th><th></th></tr></thead>
                <tbody>
                    @forelse ($purchases as $purchase)
                        <tr>
                            <td class="fw-bold">{{ $purchase->number }}</td>
                            <td>{{ $purchase->supplier?->name }}</td>
                            <td>{{ $purchase->created_at->format('d/m/Y h:i A') }}</td>
                            <td><span class="badge {{ $purchase->payment_type === 'credito' ? 'text-bg-warning' : 'text-bg-success' }}">{{ $purchase->payment_type }}</span></td>
                            <td class="text-end">C$ {{ number_format((float) $purchase->total, 2) }}</td>
                            <td class="text-end">C$ {{ number_format((float) $purchase->balance, 2) }}</td>
                            <td class="text-end"><a href="{{ route('purchases.show', $purchase) }}" class="btn btn-outline-primary btn-sm">Ver</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-5 text-center text-muted">No hay compras para mostrar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-top px-3 px-md-4 py-3">{{ $purchases->links('pagination::bootstrap-5') }}</div>
    </div>
@endsection
