@extends('layout')

@section('title', 'Productos | Sistema de Inventario')
@section('page-title', 'Catalogo de productos')
@section('page-subtitle', 'Consulta de existencias, stock minimo, marca y categoria')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="metric-card p-4">
                <div class="text-muted fw-semibold small text-uppercase">Productos registrados</div>
                <div class="display-6 fw-bold">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card p-4">
                <div class="text-muted fw-semibold small text-uppercase">Bajo stock</div>
                <div class="display-6 fw-bold text-warning">{{ $stats['low_stock'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card p-4">
                <div class="text-muted fw-semibold small text-uppercase">Valor del inventario</div>
                <div class="display-6 fw-bold">C$ {{ number_format((float) $stats['inventory_value'], 2) }}</div>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="border-bottom p-3 p-md-4">
            <form method="GET" action="{{ route('products.index') }}" class="row g-3 align-items-end">
                <div class="col-md-7">
                    <label for="search" class="form-label fw-semibold">Buscar producto</label>
                    <input
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="SKU, codigo de barras, nombre, marca, categoria o proveedor"
                    >
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label fw-semibold">Estado de stock</label>
                    <select id="status" name="status" class="form-select">
                        <option value="all" @selected($status === 'all')>Todos</option>
                        <option value="low" @selected($status === 'low')>Bajo stock</option>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-primary">Buscar</button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Imagen</th>
                        <th>Producto</th>
                        <th>Marca</th>
                        <th>Categoria</th>
                        <th class="text-end">Precio</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Minimo</th>
                        <th>Proveedor</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                @if ($product->image_path)
                                    <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->nombre }}" class="rounded border" style="width: 64px; height: 48px; object-fit: cover;">
                                @else
                                    <div class="rounded border bg-light d-flex align-items-center justify-content-center text-muted small" style="width: 64px; height: 48px;">
                                        JPG
                                    </div>
                                @endif
                            </td>
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
                            <td>{{ $product->category?->nombre ?? 'Sin categoria' }}</td>
                            <td class="text-end">C$ {{ number_format((float) $product->precio, 2) }}</td>
                            <td class="text-center fw-bold">{{ $product->stock }}</td>
                            <td class="text-center">{{ $product->stock_minimo }}</td>
                            <td>{{ $product->proveedor ?: 'Sin proveedor' }}</td>
                            <td class="text-center">
                                @if ($product->stock <= $product->stock_minimo)
                                    <span class="badge text-bg-warning">Bajo stock</span>
                                @else
                                    <span class="badge text-bg-success">Disponible</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-5 text-center text-muted">
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
@endsection
