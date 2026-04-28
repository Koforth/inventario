@extends('layout')

@section('title', 'Historial de precios | Compras')
@section('page-title', 'Historial de precios')
@section('page-subtitle', 'Costos registrados por producto en compras')

@section('content')
    <div class="table-card">
        <div class="border-bottom p-3 p-md-4"><form method="GET" class="row g-3 align-items-end"><div class="col-md-10"><label class="form-label fw-semibold">Buscar producto</label><input name="search" value="{{ $search }}" class="form-control" placeholder="Nombre, SKU o codigo"></div><div class="col-md-2 d-grid"><button class="btn btn-primary">Buscar</button></div></form></div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Producto</th><th>Proveedor</th><th>Compra</th><th class="text-end">Costo anterior</th><th class="text-end">Costo compra</th><th>Fecha</th></tr></thead><tbody>
            @forelse($items as $item)
                <tr><td><div class="fw-bold">{{ $item->product_name }}</div><div class="text-muted small">{{ $item->barcode ?: $item->sku }}</div></td><td>{{ $item->purchase?->supplier?->name }}</td><td>{{ $item->purchase?->number }}</td><td class="text-end">C$ {{ number_format((float)$item->previous_cost,2) }}</td><td class="text-end fw-bold">C$ {{ number_format((float)$item->unit_cost,2) }}</td><td>{{ $item->created_at->format('d/m/Y') }}</td></tr>
            @empty
                <tr><td colspan="6" class="py-5 text-center text-muted">No hay historial de precios para mostrar.</td></tr>
            @endforelse
        </tbody></table></div>
        <div class="border-top px-3 px-md-4 py-3">{{ $items->links('pagination::bootstrap-5') }}</div>
    </div>
@endsection
