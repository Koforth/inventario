@extends('layout')

@section('title', 'Ventas | Sistema de Inventario')
@section('page-title', 'Ventas')
@section('page-subtitle', 'Procesar ventas y descontar existencias')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Ventas de hoy</div>
                <div class="display-6 fw-bold text-success">{{ $stats['today_sales'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Productos disponibles</div>
                <div class="display-6 fw-bold">{{ $stats['available_products'] }}</div>
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
                    <form method="GET" action="{{ route('inventory.sales.index') }}" class="row g-3 align-items-end">
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
                                <th class="text-end">Precio</th>
                                <th class="text-center">Stock</th>
                                <th class="text-center">Estado</th>
                                <th style="width: 260px;">Procesar venta</th>
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
                                            @if ($product->brand)
                                                <span class="ms-2">{{ $product->brand->nombre }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end">C$ {{ number_format((float) $product->precio, 2) }}</td>
                                    <td class="text-center fw-bold">{{ $product->stock }}</td>
                                    <td class="text-center">
                                        @if ($product->isLowStock())
                                            <span class="badge text-bg-warning">Bajo stock</span>
                                        @else
                                            <span class="badge text-bg-success">Disponible</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('inventory.sales.process') }}" class="d-flex gap-2">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <input
                                                type="number"
                                                name="cantidad"
                                                min="1"
                                                max="{{ $product->stock }}"
                                                class="form-control"
                                                placeholder="Cantidad"
                                                required
                                            >
                                            <button class="btn btn-success">Vender</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-5 text-center text-muted">
                                        No hay productos disponibles para vender.
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
                    <h2 class="h5 mb-1">Ventas recientes</h2>
                    <div class="text-muted small">Ultimas salidas por venta.</div>
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
                                    <span class="badge text-bg-success">-{{ $movement->cantidad }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted">
                            Todavia no hay ventas registradas.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
