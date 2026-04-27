@extends('layout')

@section('title', 'Entradas | Sistema de Inventario')
@section('page-title', 'Entradas de inventario')
@section('page-subtitle', 'Registrar productos que ingresan al stock')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Entradas de hoy</div>
                <div class="display-6 fw-bold text-primary">{{ $stats['today_entries'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Productos registrados</div>
                <div class="display-6 fw-bold">{{ $stats['total_products'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Bajo stock</div>
                <div class="display-6 fw-bold text-warning">{{ $stats['low_stock'] }}</div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="table-card">
                <div class="border-bottom p-3 p-md-4">
                    <form method="GET" action="{{ route('inventory.entries.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-9">
                            <label for="search" class="form-label fw-semibold">Buscar producto</label>
                            <input
                                id="search"
                                name="search"
                                value="{{ $search }}"
                                class="form-control"
                                placeholder="Nombre, SKU, codigo, marca o categoria"
                            >
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
                                <th>Producto</th>
                                <th>Marca</th>
                                <th class="text-center">Stock</th>
                                <th class="text-center">Minimo</th>
                                <th style="width: 260px;">Registrar entrada</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                <tr>
                                    <td>
                                        <a href="{{ route('products.show', $product) }}" class="fw-bold text-decoration-none">
                                            {{ $product->nombre }}
                                        </a>
                                        <div class="text-muted small">
                                            SKU: {{ $product->sku }}
                                            @if ($product->barcode)
                                                <span class="ms-2">Codigo: {{ $product->barcode }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $product->brand?->nombre ?? 'Sin marca' }}</td>
                                    <td class="text-center fw-bold">{{ $product->stock }}</td>
                                    <td class="text-center">{{ $product->stock_minimo }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('inventory.entries.store') }}" class="d-flex gap-2">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <input
                                                type="number"
                                                name="cantidad"
                                                min="1"
                                                class="form-control"
                                                placeholder="Cantidad"
                                                required
                                            >
                                            <button class="btn btn-primary">Agregar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-5 text-center text-muted">
                                        No hay productos para mostrar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-top px-3 px-md-4 py-3">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="table-card h-100">
                <div class="border-bottom p-3 p-md-4">
                    <h2 class="h5 mb-1">Entradas recientes</h2>
                    <div class="text-muted small">Ultimos ingresos registrados.</div>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($recentMovements as $movement)
                        <div class="list-group-item px-3 px-md-4 py-3">
                            <div class="d-flex justify-content-between gap-3">
                                <div>
                                    <div class="fw-bold">{{ $movement->product?->nombre ?? 'Producto eliminado' }}</div>
                                    <div class="text-muted small">
                                        {{ $movement->user?->name ?? 'Sin usuario' }} - {{ $movement->created_at->format('d/m/Y h:i A') }}
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge text-bg-primary">+{{ $movement->cantidad }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted">
                            Todavia no hay entradas registradas.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
